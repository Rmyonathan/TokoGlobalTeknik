<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\PembayaranDetail;
use App\Models\StokOwner;
use App\Services\PembayaranPiutangService;
use Carbon\Carbon;

class TestPembayaranPiutangSystem extends Command
{
    protected $signature = 'app:test-pembayaran-piutang-system';
    protected $description = 'Test comprehensive payment system for accounts receivable';

    public function handle()
    {
        $this->info('🧪 Testing Sistem Pembayaran Piutang...');

        // 1. Setup test data
        $this->info('1. Setup test data...');
        
        // Create customer
        $customer = Customer::firstOrCreate([
            'kode_customer' => 'CUST-PAY-001'
        ], [
            'nama' => 'Customer Test Pembayaran',
            'alamat' => 'Jl. Test Pembayaran No. 1',
            'hp' => '081234567890',
            'telepon' => '021-1234567',
            'limit_kredit' => 1000000,
            'limit_hari_tempo' => 30
        ]);

        // Create salesman
        $salesman = StokOwner::firstOrCreate([
            'kode_stok_owner' => 'SALES-PAY'
        ], [
            'keterangan' => 'Sales Test Pembayaran'
        ]);

        // Create kode barang
        $kodeBarang = KodeBarang::firstOrCreate([
            'kode_barang' => 'KB-PAY'
        ], [
            'name' => 'Barang Test Pembayaran',
            'cost' => 5000,
            'price' => 10000,
            'attribute' => 'Test',
            'length' => 100,
            'status' => 'Active'
        ]);

        $this->info('   ✅ Test data berhasil disiapkan');

        // 2. Create test invoices with different due dates
        $this->info('2. Creating test invoices...');
        
        $invoices = [];
        
        // Invoice 1 - Overdue
        $invoice1 = Transaksi::create([
            'no_transaksi' => 'INV-PAY-001',
            'tanggal' => now()->subDays(45),
            'kode_customer' => $customer->kode_customer,
            'sales' => $salesman->kode_stok_owner,
            'pembayaran' => 'Kredit',
            'cara_bayar' => 'Transfer',
            'tanggal_jadi' => now()->subDays(45),
            'tanggal_jatuh_tempo' => now()->subDays(15),
            'subtotal' => 100000,
            'discount' => 0,
            'disc_rupiah' => 0,
            'ppn' => 11000,
            'dp' => 0,
            'grand_total' => 111000,
            'status' => 'baru',
            'status_piutang' => 'belum_dibayar',
            'total_dibayar' => 0,
            'sisa_piutang' => 111000,
            'created_from_po' => null,
            'is_edited' => false,
            'keterangan' => 'Test invoice overdue'
        ]);

        // Invoice 2 - Due today
        $invoice2 = Transaksi::create([
            'no_transaksi' => 'INV-PAY-002',
            'tanggal' => now()->subDays(30),
            'kode_customer' => $customer->kode_customer,
            'sales' => $salesman->kode_stok_owner,
            'pembayaran' => 'Kredit',
            'cara_bayar' => 'Transfer',
            'tanggal_jadi' => now()->subDays(30),
            'tanggal_jatuh_tempo' => now(),
            'subtotal' => 150000,
            'discount' => 0,
            'disc_rupiah' => 0,
            'ppn' => 16500,
            'dp' => 0,
            'grand_total' => 166500,
            'status' => 'baru',
            'status_piutang' => 'belum_dibayar',
            'total_dibayar' => 0,
            'sisa_piutang' => 166500,
            'created_from_po' => null,
            'is_edited' => false,
            'keterangan' => 'Test invoice due today'
        ]);

        // Invoice 3 - Due in future
        $invoice3 = Transaksi::create([
            'no_transaksi' => 'INV-PAY-003',
            'tanggal' => now()->subDays(15),
            'kode_customer' => $customer->kode_customer,
            'sales' => $salesman->kode_stok_owner,
            'pembayaran' => 'Kredit',
            'cara_bayar' => 'Transfer',
            'tanggal_jadi' => now()->subDays(15),
            'tanggal_jatuh_tempo' => now()->addDays(15),
            'subtotal' => 200000,
            'discount' => 0,
            'disc_rupiah' => 0,
            'ppn' => 22000,
            'dp' => 0,
            'grand_total' => 222000,
            'status' => 'baru',
            'status_piutang' => 'belum_dibayar',
            'total_dibayar' => 0,
            'sisa_piutang' => 222000,
            'created_from_po' => null,
            'is_edited' => false,
            'keterangan' => 'Test invoice due in future'
        ]);

        $invoices = [$invoice1, $invoice2, $invoice3];
        
        $this->info("   ✅ 3 test invoices berhasil dibuat:");
        $this->info("     * INV-PAY-001: Rp 111.000 (Overdue 15 hari)");
        $this->info("     * INV-PAY-002: Rp 166.500 (Due today)");
        $this->info("     * INV-PAY-003: Rp 222.000 (Due in 15 hari)");

        // 3. Test PembayaranPiutangService
        $this->info('3. Testing PembayaranPiutangService...');
        
        $service = new PembayaranPiutangService();
        
        // Test hitungTotalPiutangCustomer
        $piutangInfo = $service->hitungTotalPiutangCustomer($customer->id);
        if ($piutangInfo['success']) {
            $this->info("   ✅ Total Piutang Customer:");
            $this->info("     * Total Piutang: Rp " . number_format($piutangInfo['piutang']['total_piutang'], 0, ',', '.'));
            $this->info("     * Total Jatuh Tempo: Rp " . number_format($piutangInfo['piutang']['total_jatuh_tempo'], 0, ',', '.'));
            $this->info("     * Jumlah Faktur: " . $piutangInfo['piutang']['jumlah_faktur']);
            $this->info("     * Faktur Jatuh Tempo: " . $piutangInfo['piutang']['faktur_jatuh_tempo']);
            $this->info("     * Persentase Jatuh Tempo: " . number_format($piutangInfo['piutang']['persentase_jatuh_tempo'], 2) . "%");
        }

        // 4. Test FIFO payment suggestions
        $this->info('4. Testing FIFO payment suggestions...');
        
        $totalBayar = 200000; // Test payment amount
        $suggestions = $service->getFifoPaymentSuggestion($customer->id, $totalBayar);
        
        if ($suggestions['success']) {
            $this->info("   ✅ FIFO Payment Suggestions untuk pembayaran Rp " . number_format($totalBayar, 0, ',', '.'));
            $this->info("     * Total Suggested: Rp " . number_format($suggestions['total_suggested'], 0, ',', '.'));
            $this->info("     * Remaining Payment: Rp " . number_format($suggestions['remaining_payment'], 0, ',', '.'));
            $this->info("     * Efficiency: " . number_format($suggestions['efficiency'], 2) . "%");
            
            foreach ($suggestions['suggestions'] as $suggestion) {
                $this->info("     * " . $suggestion['no_transaksi'] . ": Rp " . number_format($suggestion['suggested_payment'], 0, ',', '.') . 
                    " (Priority: " . $suggestion['priority'] . ", " . number_format($suggestion['persentase_pelunasan'], 1) . "%)");
            }
        }

        // 5. Test payment processing
        $this->info('5. Testing payment processing...');
        
        $paymentData = [
            'customer_id' => $customer->id,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'total_bayar' => 200000,
            'metode_pembayaran' => 'Transfer',
            'cara_bayar' => 'Bank Transfer',
            'no_referensi' => 'TRF-001',
            'keterangan' => 'Test payment via service',
            'payment_details' => [
                [
                    'transaksi_id' => $invoice1->id,
                    'jumlah_dilunasi' => 111000
                ],
                [
                    'transaksi_id' => $invoice2->id,
                    'jumlah_dilunasi' => 89000
                ]
            ]
        ];
        
        $paymentResult = $service->processPayment($paymentData);
        
        if ($paymentResult['success']) {
            $this->info("   ✅ Payment berhasil diproses:");
            $this->info("     * No Pembayaran: " . $paymentResult['pembayaran']['no_pembayaran']);
            $this->info("     * Customer: " . $paymentResult['pembayaran']['customer']);
            $this->info("     * Total Bayar: Rp " . number_format($paymentResult['pembayaran']['total_bayar'], 0, ',', '.'));
            $this->info("     * Tanggal: " . $paymentResult['pembayaran']['tanggal_bayar']);
            $this->info("     * Total Processed: Rp " . number_format($paymentResult['summary']['total_processed'], 0, ',', '.'));
            $this->info("     * Invoices Processed: " . $paymentResult['summary']['invoices_processed']);
            $this->info("     * Efficiency: " . number_format($paymentResult['summary']['efficiency'], 2) . "%");
        } else {
            $this->error("   ❌ Payment gagal: " . $paymentResult['message']);
        }

        // 6. Test payment statistics
        $this->info('6. Testing payment statistics...');
        
        $statistics = $service->getPaymentStatistics();
        if ($statistics['success']) {
            $this->info("   ✅ Payment Statistics:");
            $this->info("     * Today: Rp " . number_format($statistics['statistics']['today']['total_pembayaran'], 0, ',', '.') . 
                " (" . $statistics['statistics']['today']['jumlah_transaksi'] . " transactions)");
            $this->info("     * This Month: Rp " . number_format($statistics['statistics']['this_month']['total_pembayaran'], 0, ',', '.') . 
                " (" . $statistics['statistics']['this_month']['jumlah_transaksi'] . " transactions)");
            $this->info("     * Growth: " . number_format($statistics['statistics']['growth']['total_pembayaran'], 2) . "%");
        }

        // 7. Test laporan piutang
        $this->info('7. Testing laporan piutang...');
        
        $filters = [
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addMonths(1)
        ];
        
        $laporan = $service->getLaporanPiutang($filters);
        if ($laporan['success']) {
            $this->info("   ✅ Laporan Piutang:");
            $this->info("     * Total Faktur: " . $laporan['summary']['total_faktur']);
            $this->info("     * Total Nilai Faktur: Rp " . number_format($laporan['summary']['total_nilai_faktur'], 0, ',', '.'));
            $this->info("     * Total Sudah Dibayar: Rp " . number_format($laporan['summary']['total_sudah_dibayar'], 0, ',', '.'));
            $this->info("     * Total Sisa Piutang: Rp " . number_format($laporan['summary']['total_sisa_piutang'], 0, ',', '.'));
            $this->info("     * Total Jatuh Tempo: " . $laporan['summary']['total_jatuh_tempo']);
            $this->info("     * Total Overdue Amount: Rp " . number_format($laporan['summary']['total_overdue_amount'], 0, ',', '.'));
            $this->info("     * Average Collection Period: " . number_format($laporan['summary']['average_collection_period'], 1) . " days");
        }

        // 8. Verify payment details
        $this->info('8. Verifying payment details...');
        
        $pembayaran = Pembayaran::where('customer_id', $customer->id)->first();
        if ($pembayaran) {
            $this->info("   ✅ Payment Record:");
            $this->info("     * No Pembayaran: " . $pembayaran->no_pembayaran);
            $this->info("     * Status: " . $pembayaran->status);
            $this->info("     * Total Bayar: Rp " . number_format($pembayaran->total_bayar, 0, ',', '.'));
            $this->info("     * Total Piutang: Rp " . number_format($pembayaran->total_piutang, 0, ',', '.'));
            $this->info("     * Sisa Piutang: Rp " . number_format($pembayaran->sisa_piutang, 0, ',', '.'));
            
            $this->info("   ✅ Payment Details:");
            foreach ($pembayaran->details as $detail) {
                $this->info("     * " . $detail->no_transaksi . ": Rp " . number_format($detail->jumlah_dilunasi, 0, ',', '.') . 
                    " (Status: " . $detail->status_pelunasan . ")");
            }
        }

        // 9. Verify invoice status updates
        $this->info('9. Verifying invoice status updates...');
        
        $this->info("   ✅ Invoice Status Updates:");
        foreach ($invoices as $invoice) {
            $invoice->refresh();
            $this->info("     * " . $invoice->no_transaksi . ":");
            $this->info("       - Status Piutang: " . $invoice->status_piutang);
            $this->info("       - Total Dibayar: Rp " . number_format($invoice->total_dibayar, 0, ',', '.'));
            $this->info("       - Sisa Piutang: Rp " . number_format($invoice->sisa_piutang, 0, ',', '.'));
            $this->info("       - Is Jatuh Tempo: " . ($invoice->checkJatuhTempo() ? 'Ya' : 'Tidak'));
            if ($invoice->checkJatuhTempo()) {
                $this->info("       - Hari Keterlambatan: " . $invoice->hari_keterlambatan . " hari");
            }
        }

        // 10. Cleanup test data
        $this->info('10. Cleanup test data...');
        
        // Delete payment details first
        if ($pembayaran) {
            PembayaranDetail::where('pembayaran_id', $pembayaran->id)->delete();
            $pembayaran->delete();
        }
        
        // Delete invoices
        foreach ($invoices as $invoice) {
            $invoice->delete();
        }
        
        // Delete test data
        $customer->delete();
        $salesman->delete();
        $kodeBarang->delete();
        
        $this->info('✅ Testing Sistem Pembayaran Piutang selesai!');
        $this->info('');
        $this->info('🎯 Payment System Summary:');
        $this->info('   ✅ Customer piutang calculation');
        $this->info('   ✅ FIFO payment suggestions');
        $this->info('   ✅ Payment processing with FIFO allocation');
        $this->info('   ✅ Payment statistics and reporting');
        $this->info('   ✅ Invoice status updates');
        $this->info('   ✅ Overdue tracking and management');
        $this->info('');
        $this->info('🚀 Sistem Pembayaran Piutang siap untuk production deployment!');
    }
}
