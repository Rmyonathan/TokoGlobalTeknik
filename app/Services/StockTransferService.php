<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\StockBatch;
use App\Models\StockTransferLog;
use App\Models\KodeBarang;

class StockTransferService
{
    /**
     * Transfer stock FIFO from source DB to target DB with average cost pricing
     *
     * @param int $kodeBarangId
     * @param float $qty
     * @param string $sourceConnection ex: 'mysql'
     * @param string $targetConnection ex: 'mysql_second'
     * @param array $options ['unit' => 'LBR','note' => '','created_by' => '']
     * @return array
     * @throws \Throwable
     */
    public function transferBetweenDatabases(
        int $kodeBarangId,
        float $qty,
        string $sourceConnection,
        string $targetConnection,
        array $options = []
    ): array {
        $unit = $options['unit'] ?? 'LBR';
        $note = $options['note'] ?? '';
        $createdBy = $options['created_by'] ?? (auth()->user()->name ?? 'SYSTEM');

        if ($qty <= 0) {
            throw new \InvalidArgumentException('Qty must be > 0');
        }

        $transferNo = $this->generateTransferNo();

        // Step 1: Allocate from source using FIFO
        $allocation = [];
        $totalTaken = 0.0;
        $weightedCostSum = 0.0;

        DB::connection($sourceConnection)->transaction(function () use (
            $kodeBarangId,
            $qty,
            &$allocation,
            &$totalTaken,
            &$weightedCostSum,
            $unit,
            $sourceConnection
        ) {
            // Lock FIFO batches on source
            $batches = StockBatch::on($sourceConnection)
                ->where('kode_barang_id', $kodeBarangId)
                ->where('qty_sisa', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            $needed = $qty;
            foreach ($batches as $batch) {
                if ($needed <= 0) break;
                $take = min($needed, (float) $batch->qty_sisa);
                if ($take <= 0) continue;

                $batch->qty_sisa = (float) $batch->qty_sisa - $take;
                $batch->save();

                $allocation[] = [
                    'batch_id' => $batch->id,
                    'qty' => $take,
                    'harga_beli' => (float) $batch->harga_beli,
                ];

                $totalTaken += $take;
                $weightedCostSum += $take * (float) $batch->harga_beli;
                $needed -= $take;
            }

            if ($needed > 0) {
                throw new \RuntimeException('Stok tidak mencukupi di database asal');
            }
        });

        // Step 2: Compute average cost
        $avgCost = $totalTaken > 0 ? round($weightedCostSum / $totalTaken, 2) : 0.0;

        // Step 3: Create StockBatch on target
        $kodeBarang = KodeBarang::findOrFail($kodeBarangId);
        DB::connection($targetConnection)->transaction(function () use (
            $kodeBarang,
            $totalTaken,
            $avgCost,
            $unit,
            $targetConnection
        ) {
            StockBatch::on($targetConnection)->create([
                'kode_barang_id' => $kodeBarang->id,
                'kode_barang' => $kodeBarang->kode_barang,
                'qty_masuk' => $totalTaken,
                'qty_sisa' => $totalTaken,
                'harga_beli' => $avgCost,
                'tanggal_masuk' => now(),
                'supplier' => 'TRANSFER',
            ]);
        });

        // Step 4: Audit log on both sides
        $this->logTransfer($transferNo, $kodeBarang, $totalTaken, $avgCost, $unit, $sourceConnection, $targetConnection, 'source', $createdBy, $note);
        $this->logTransfer($transferNo, $kodeBarang, $totalTaken, $avgCost, $unit, $sourceConnection, $targetConnection, 'target', $createdBy, $note);

        // Step 5: Optional Accounting Journals (in-transit / intercompany)
        $this->maybeCreateJournals($transferNo, $kodeBarang->kode_barang, $totalTaken, $avgCost, $sourceConnection, $targetConnection, $createdBy, $note);

        return [
            'transfer_no' => $transferNo,
            'qty' => $totalTaken,
            'avg_cost' => $avgCost,
            'allocation' => $allocation,
        ];
    }

    private function logTransfer(
        string $transferNo,
        KodeBarang $kodeBarang,
        float $qty,
        float $avgCost,
        string $unit,
        string $sourceConnection,
        string $targetConnection,
        string $role,
        string $createdBy,
        string $note
    ): void {
        StockTransferLog::create([
            'transfer_no' => $transferNo,
            'kode_barang' => $kodeBarang->kode_barang,
            'kode_barang_id' => $kodeBarang->id,
            'qty' => $qty,
            'avg_cost' => $avgCost,
            'unit' => $unit,
            'source_db' => $sourceConnection,
            'target_db' => $targetConnection,
            'role' => $role,
            'created_by' => $createdBy,
            'note' => $note,
        ]);
    }

    private function generateTransferNo(): string
    {
        $prefix = 'TF-'.now()->format('Ymd').'-';
        $last = StockTransferLog::where('transfer_no', 'like', $prefix.'%')
            ->orderBy('transfer_no', 'desc')
            ->first();
        $n = 1;
        if ($last) {
            $num = (int) substr($last->transfer_no, strlen($prefix));
            $n = $num + 1;
        }
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    private function maybeCreateJournals(
        string $transferNo,
        string $kodeBarang,
        float $qty,
        float $avgCost,
        string $sourceConnection,
        string $targetConnection,
        string $createdBy,
        string $note
    ): void {
        $amount = round($qty * $avgCost, 2);
        if ($amount <= 0) return;

        // Expect optional config: accounting.stock_transfer with COA codes
        $cfg = config('accounting.stock_transfer');
        if (!$cfg || !is_array($cfg)) return; // skip if not configured

        // Prepare journal payloads
        $memo = 'Stock Transfer '.$transferNo.' ('.$kodeBarang.')';

        $sourceJournal = [
            'no_bukti' => $transferNo.'-SRC',
            'tanggal' => now()->toDateString(),
            'memo' => $memo,
            'created_by' => $createdBy,
            'lines' => [
                // Debit In-Transit (or Intercompany Piutang)
                ['akun' => $cfg['in_transit_debit'] ?? $cfg['intercompany_piutang'] ?? null, 'debit' => $amount, 'kredit' => 0],
                // Credit Persediaan
                ['akun' => $cfg['inventory_source'] ?? null, 'debit' => 0, 'kredit' => $amount],
            ],
            'note' => $note,
        ];

        $targetJournal = [
            'no_bukti' => $transferNo.'-DST',
            'tanggal' => now()->toDateString(),
            'memo' => $memo,
            'created_by' => $createdBy,
            'lines' => [
                // Debit Persediaan
                ['akun' => $cfg['inventory_target'] ?? null, 'debit' => $amount, 'kredit' => 0],
                // Credit In-Transit (or Intercompany Hutang)
                ['akun' => $cfg['in_transit_credit'] ?? $cfg['intercompany_hutang'] ?? null, 'debit' => 0, 'kredit' => $amount],
            ],
            'note' => $note,
        ];

        $this->createJournalIfPossible($sourceJournal, $sourceConnection);
        $this->createJournalIfPossible($targetJournal, $targetConnection);
    }

    private function createJournalIfPossible(array $journal, string $connection): void
    {
        try {
            if (!class_exists(\App\Services\AccountingService::class)) return;
            $svc = app(\App\Services\AccountingService::class);
            if (!method_exists($svc, 'createGeneralJournal')) return;
            // Switch connection if service supports it, otherwise rely on default behavior
            $svc->createGeneralJournal($journal, $connection);
        } catch (\Throwable $e) {
            Log::warning('Accounting journal skipped: '.$e->getMessage());
        }
    }
}
