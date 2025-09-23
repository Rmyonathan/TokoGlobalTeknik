<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use App\Models\PembayaranDetail;
use App\Models\PembayaranPiutangNotaKredit;
use App\Models\PembayaranUtangSupplier;
use App\Models\PembayaranUtangSupplierDetail;
use App\Models\PembayaranUtangSupplierNotaDebit;
use App\Models\Transaksi;
use App\Models\Pembelian;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\NotaKredit;
use App\Models\NotaDebit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::table('pembayaran_utang_supplier_nota_debits')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('pembayaran_utang_supplier_details')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('pembayaran_utang_suppliers')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('pembayaran_piutang_nota_kredits')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('pembayaran_details')->truncate(); } catch (\Throwable $e) {}
        try { DB::table('pembayarans')->truncate(); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $customers = Customer::all();
        $suppliers = Supplier::all();
        $transaksis = Transaksi::where('cara_bayar', 'Kredit')->get();
        $pembelians = Pembelian::where('cara_bayar', 'Kredit')->get();

        if ($customers->isEmpty() || $suppliers->isEmpty()) {
            $this->command->warn('⚠️  Customer atau supplier belum ada. Jalankan CustomerSupplierSeeder terlebih dahulu.');
            return;
        }

        // Create AR Payments (Pembayaran Piutang)
        $pembayaranArCount = 8;
        $pembayaranArData = [];

        for ($i = 1; $i <= $pembayaranArCount; $i++) {
            $customer = $customers->random();
            $tanggal = now()->subDays(rand(1, 20));
            
            // Create AR payment
            $pembayaran = Pembayaran::create([
                'customer_id' => $customer->id,
                'no_pembayaran' => 'PAY-AR-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tanggal_bayar' => $tanggal,
                'total_bayar' => 0, // Will be calculated
                'total_piutang' => 0,
                'sisa_piutang' => 0,
                'metode_pembayaran' => 'cash',
                'cara_bayar' => ['Tunai', 'Transfer'][rand(0, 1)],
                'status' => 'confirmed',
                'created_by' => 1
            ]);

            // Add 1-3 transactions to payment
            $customerTransaksis = $transaksis->where('customer_id', $customer->id)->take(rand(1, 3));
            $totalBayar = 0;
            $totalPiutang = 0;

            foreach ($customerTransaksis as $transaksi) {
                // Pay 50-100% of remaining balance
                $sisaPiutang = $transaksi->sisa_piutang;
                $bayar = $sisaPiutang * (rand(50, 100) / 100);
                $totalBayar += $bayar;
                $totalPiutang += $transaksi->grand_total;

                // Create payment detail
                PembayaranDetail::create([
                    'pembayaran_id' => $pembayaran->id,
                    'no_pembayaran' => $pembayaran->no_pembayaran,
                    'no_transaksi' => $transaksi->no_transaksi,
                    'total_tagihan' => $transaksi->grand_total,
                    'total_bayar' => $bayar,
                    'sisa_piutang' => $sisaPiutang - $bayar,
                ]);

                // Update transaction
                $transaksi->increment('total_dibayar', $bayar);
                $transaksi->decrement('sisa_piutang', $bayar);
                $transaksi->update([
                    'status_piutang' => $transaksi->sisa_piutang <= 0 ? 'lunas' : 'sebagian'
                ]);
            }

            // Update payment total
            $pembayaran->update([
                'total_bayar' => $totalBayar,
                'total_piutang' => $totalPiutang,
                'sisa_piutang' => $totalPiutang - $totalBayar
            ]);

            // Update customer credit
            $customer->increment('sisa_kredit', $totalBayar);

            $pembayaranArData[] = [
                'no_pembayaran' => $pembayaran->no_pembayaran,
                'customer' => $customer->nama,
                'total_bayar' => $totalBayar,
                'transactions' => $customerTransaksis->count(),
            ];
        }

        // Create AP Payments (Pembayaran Utang Supplier)
        $pembayaranApCount = 6;
        $pembayaranApData = [];

        for ($i = 1; $i <= $pembayaranApCount; $i++) {
            $supplier = $suppliers->random();
            $tanggal = now()->subDays(rand(1, 15));
            
            // Create AP payment
            $pembayaranUtang = PembayaranUtangSupplier::create([
                'supplier_id' => $supplier->id,
                'no_pembayaran' => 'PAY-AP-' . now()->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'tanggal_bayar' => $tanggal,
                'total_bayar' => 0, // Will be calculated
                'total_utang' => 0,
                'sisa_utang' => 0,
                'metode_pembayaran' => 'transfer',
                'cara_bayar' => ['Tunai', 'Transfer'][rand(0, 1)],
                'status' => 'confirmed',
                'created_by' => 1
            ]);

            // Add 1-2 purchases to payment
            $supplierPembelians = $pembelians->where('supplier_id', $supplier->id)->take(rand(1, 2));
            $totalBayar = 0;
            $totalUtang = 0;

            foreach ($supplierPembelians as $pembelian) {
                // Pay 60-100% of purchase amount
                $bayar = $pembelian->grand_total * (rand(60, 100) / 100);
                $totalBayar += $bayar;
                $totalUtang += $pembelian->grand_total;

                // Create payment detail
                PembayaranUtangSupplierDetail::create([
                    'pembayaran_utang_supplier_id' => $pembayaranUtang->id,
                    'no_pembayaran' => $pembayaranUtang->no_pembayaran,
                    'nota' => $pembelian->nota,
                    'total_tagihan' => $pembelian->grand_total,
                    'total_bayar' => $bayar,
                ]);
            }

            // Update payment total
            $pembayaranUtang->update([
                'total_bayar' => $totalBayar,
                'total_utang' => $totalUtang,
                'sisa_utang' => $totalUtang - $totalBayar
            ]);

            $pembayaranApData[] = [
                'no_pembayaran' => $pembayaranUtang->no_pembayaran,
                'supplier' => $supplier->nama,
                'total_bayar' => $totalBayar,
                'purchases' => $supplierPembelians->count(),
            ];
        }

        $this->command->info('✅ Data pembayaran berhasil dibuat!');
        $this->command->info('Total pembayaran piutang: ' . $pembayaranArCount);
        $this->command->info('Total pembayaran utang: ' . $pembayaranApCount);
        $this->command->info('Total AR payments: Rp ' . number_format(collect($pembayaranArData)->sum('total_bayar'), 0, ',', '.'));
        $this->command->info('Total AP payments: Rp ' . number_format(collect($pembayaranApData)->sum('total_bayar'), 0, ',', '.'));
        
        // Show sample data
        $this->command->info("\n📋 Sample AR payments:");
        foreach (array_slice($pembayaranArData, 0, 3) as $data) {
            $this->command->info("- {$data['no_pembayaran']} | {$data['customer']} | Rp " . number_format($data['total_bayar'], 0, ',', '.'));
        }
        
        $this->command->info("\n📋 Sample AP payments:");
        foreach (array_slice($pembayaranApData, 0, 3) as $data) {
            $this->command->info("- {$data['no_pembayaran']} | {$data['supplier']} | Rp " . number_format($data['total_bayar'], 0, ',', '.'));
        }
    }
}
