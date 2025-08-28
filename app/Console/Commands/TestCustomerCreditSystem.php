<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CustomerCreditService;
use App\Models\Customer;
use App\Models\Wilayah;
use App\Models\Transaksi;
use App\Models\Kas;

class TestCustomerCreditSystem extends Command
{
    protected $signature = 'app:test-customer-credit-system';
    protected $description = 'Test sistem kredit dan limit customer';

    public function handle()
    {
        $this->info('🧪 Testing Sistem Kredit Customer...');
        $creditService = new CustomerCreditService();

        // 1. Setup test data
        $this->info('1. Setup test data...');
        
        // Ambil wilayah
        $wilayah = Wilayah::where('nama_wilayah', 'Jakarta Pusat')->first();
        if (!$wilayah) {
            $this->error('Wilayah Jakarta Pusat tidak ditemukan! Jalankan WilayahSeeder terlebih dahulu.');
            return;
        }

        // Update customer test dengan data kredit
        $customer = Customer::where('kode_customer', 'CUST001')->first();
        if ($customer) {
            $customer->update([
                'limit_kredit' => 1000000, // 1 juta
                'limit_hari_tempo' => 30, // 30 hari
                'wilayah_id' => $wilayah->id
            ]);
        }

        // Buat customer kredit baru
        $customerKredit = Customer::firstOrCreate(
            ['kode_customer' => 'CUST002'],
            [
                'nama' => 'Customer Kredit Test',
                'alamat' => 'Alamat Kredit Test',
                'hp' => '08123456788',
                'limit_kredit' => 500000, // 500 ribu
                'limit_hari_tempo' => 14, // 14 hari
                'wilayah_id' => $wilayah->id
            ]
        );

        // Update customer kredit jika sudah ada
        if ($customerKredit->limit_hari_tempo == 0) {
            $customerKredit->update([
                'limit_kredit' => 500000,
                'limit_hari_tempo' => 14,
                'wilayah_id' => $wilayah->id
            ]);
        }

        // Buat customer tunai
        $customerTunai = Customer::firstOrCreate(
            ['kode_customer' => 'CUST003'],
            [
                'nama' => 'Customer Tunai Test',
                'alamat' => 'Alamat Tunai Test',
                'hp' => '08123456787',
                'limit_kredit' => 0,
                'limit_hari_tempo' => 0,
                'wilayah_id' => $wilayah->id
            ]
        );

        $this->info('   ✅ Test data berhasil dibuat');

        // 2. Test helper methods customer
        $this->info('2. Testing helper methods customer...');
        
        $this->line("   - Customer {$customerKredit->nama}:");
        $this->line("     * Status: {$customerKredit->getStatusKredit()}");
        $this->line("     * Limit: {$customerKredit->getLimitKreditFormatted()}");
        $this->line("     * Is Kredit: " . ($customerKredit->isKredit() ? 'Ya' : 'Tidak'));
        $this->line("     * Is Tunai: " . ($customerKredit->isTunai() ? 'Ya' : 'Tidak'));

        $this->line("   - Customer {$customerTunai->nama}:");
        $this->line("     * Status: {$customerTunai->getStatusKredit()}");
        $this->line("     * Limit: {$customerTunai->getLimitKreditFormatted()}");
        $this->line("     * Is Kredit: " . ($customerTunai->isKredit() ? 'Ya' : 'Tidak'));
        $this->line("     * Is Tunai: " . ($customerTunai->isTunai() ? 'Ya' : 'Tidak'));

        // 3. Test cek kelayakan kredit
        $this->info('3. Testing cek kelayakan kredit...');
        
        // Test customer kredit dengan nilai transaksi kecil
        $kelayakan1 = $creditService->cekKelayakanKredit($customerKredit->id, 100000);
        $this->line("   - Customer {$customerKredit->nama} - Transaksi Rp 100.000:");
        $this->line("     * Layak: " . ($kelayakan1['layak'] ? 'Ya' : 'Tidak'));
        $this->line("     * Alasan: {$kelayakan1['alasan']}");
        $this->line("     * Sisa Limit: Rp " . number_format($kelayakan1['sisa_limit'], 0, ',', '.'));

        // Test customer kredit dengan nilai transaksi besar
        $kelayakan2 = $creditService->cekKelayakanKredit($customerKredit->id, 600000);
        $this->line("   - Customer {$customerKredit->nama} - Transaksi Rp 600.000:");
        $this->line("     * Layak: " . ($kelayakan2['layak'] ? 'Ya' : 'Tidak'));
        $this->line("     * Alasan: {$kelayakan2['alasan']}");
        if (!$kelayakan2['layak']) {
            $this->line("     * Kekurangan: Rp " . number_format($kelayakan2['kekurangan'], 0, ',', '.'));
        }

        // Test customer tunai
        $kelayakan3 = $creditService->cekKelayakanKredit($customerTunai->id, 100000);
        $this->line("   - Customer {$customerTunai->nama} - Transaksi Rp 100.000:");
        $this->line("     * Layak: " . ($kelayakan3['layak'] ? 'Ya' : 'Tidak'));
        $this->line("     * Alasan: {$kelayakan3['alasan']}");

        // 4. Test simulasi transaksi kredit
        $this->info('4. Testing simulasi transaksi kredit...');
        
        // Buat transaksi kredit
        $transaksi = Transaksi::create([
            'no_transaksi' => 'TRX-KREDIT-' . time(),
            'tanggal' => now(),
            'kode_customer' => $customerKredit->kode_customer,
            'sales' => 'SALES001',
            'pembayaran' => 'Kredit',
            'cara_bayar' => 'Kredit 14 Hari',
            'subtotal' => 200000,
            'grand_total' => 200000,
            'status' => 'baru'
        ]);

        // Buat transaksi item (simulasi)
        $this->line("   - Transaksi kredit dibuat: {$transaksi->no_transaksi}");
        $this->line("     * Customer: {$customerKredit->nama}");
        $this->line("     * Total: Rp " . number_format($transaksi->grand_total, 0, ',', '.'));
        $this->line("     * Cara Bayar: {$transaksi->cara_bayar}");

        // 5. Test hitung piutang
        $this->info('5. Testing hitung piutang...');
        
        $piutangInfo = $creditService->hitungPiutang($customerKredit->id);
        $this->line("   - Customer {$customerKredit->nama}:");
        $this->line("     * Total Piutang: Rp " . number_format($piutangInfo['total_piutang'], 0, ',', '.'));
        $this->line("     * Piutang Jatuh Tempo: Rp " . number_format($piutangInfo['piutang_jatuh_tempo'], 0, ',', '.'));
        $this->line("     * Sisa Limit: Rp " . number_format($piutangInfo['sisa_limit_kredit'], 0, ',', '.'));
        $this->line("     * Status: {$piutangInfo['status_kredit']}");

        // 6. Test status kredit customer
        $this->info('6. Testing status kredit customer...');
        
        $customersAman = $creditService->getCustomerByStatusKredit('aman');
        $customersSedang = $creditService->getCustomerByStatusKredit('sedang');
        $customersKritis = $creditService->getCustomerByStatusKredit('kritis');
        $customersHabis = $creditService->getCustomerByStatusKredit('habis');

        $this->line("   - Customer dengan status kredit:");
        $this->line("     * Aman: {$customersAman->count()} customer");
        $this->line("     * Sedang: {$customersSedang->count()} customer");
        $this->line("     * Kritis: {$customersKritis->count()} customer");
        $this->line("     * Habis: {$customersHabis->count()} customer");

        // 7. Test laporan per wilayah
        $this->info('7. Testing laporan per wilayah...');
        
        $laporanWilayah = $creditService->getLaporanPiutangPerWilayah();
        foreach ($laporanWilayah as $laporan) {
            $this->line("   - {$laporan['wilayah']->nama_wilayah}:");
            $this->line("     * Total Customer: {$laporan['total_customer']}");
            $this->line("     * Customer Kredit: {$laporan['customer_kredit']}");
            $this->line("     * Total Piutang: Rp " . number_format($laporan['total_piutang'], 0, ',', '.'));
        }

        // Cleanup
        $transaksi->delete();

        $this->info('✅ Testing sistem kredit customer selesai!');
    }
}
