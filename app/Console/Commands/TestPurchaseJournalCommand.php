<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Pembelian;
use App\Models\PembelianItem;
use App\Models\KodeBarang;
use App\Services\AccountingService;
use App\Models\Journal;
use App\Models\JournalDetail;

class TestPurchaseJournalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:test-purchase-journal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a sample purchase and verify journal entries are created.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test purchase...');
        DB::beginTransaction();
        try {
            $supplier = Supplier::first() ?: Supplier::create([
                'kode_supplier' => 'SUP-TEST',
                'nama' => 'Supplier Test',
            ]);

            $barang = KodeBarang::first() ?: KodeBarang::create([
                'kode_barang' => 'BRG-TEST',
                'name' => 'Barang Test',
                'unit_dasar' => 'PCS',
                'unit_jual' => 'PCS',
                'status' => 'Active',
            ]);

            $nota = 'BL/TEST/' . now()->format('ymdHis');
            $pembelian = Pembelian::create([
                'nota' => $nota,
                'tanggal' => now(),
                'kode_supplier' => $supplier->kode_supplier,
                'pembayaran' => 'Tunai',
                'cara_bayar' => 'Tunai',
                'subtotal' => 100000,
                'diskon' => 0,
                'ppn' => 10000,
                'grand_total' => 110000,
            ]);

            PembelianItem::create([
                'nota' => $nota,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->name,
                'harga' => 10000,
                'qty' => 10,
                'total' => 100000,
            ]);

            DB::commit();

            // Invoke accounting journal explicitly (in case controller hook not used here)
            app(AccountingService::class)->createJournalFromPurchase($pembelian);

            $journal = Journal::where('reference', $nota)->orWhere('description', 'like', "%{$nota}%")->latest('id')->first();
            if (!$journal) {
                $this->error('No journal found for this purchase.');
                return Command::FAILURE;
            }

            $details = JournalDetail::where('journal_id', $journal->id)->get();
            $this->info('Journal created: '.$journal->journal_no.' on '.$journal->journal_date);
            foreach ($details as $d) {
                $this->line(sprintf('- [%s] D %.2f / C %.2f : %s',
                    optional($d->account)->name,
                    $d->debit,
                    $d->credit,
                    $d->memo
                ));
            }
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: '.$e->getMessage());
            return Command::FAILURE;
        }
    }
}
