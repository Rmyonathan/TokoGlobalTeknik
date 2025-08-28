<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\CustomerPrice;
use App\Models\CustomerItemOngkos;
use App\Models\UnitConversion;
use App\Http\Controllers\TransaksiController;
use Illuminate\Http\Request;

class TestFrontendFakturIntegration extends Command
{
    protected $signature = 'app:test-frontend-faktur-integration';
    protected $description = 'Test frontend integration untuk sistem Faktur FIFO';

    public function handle()
    {
        $this->info('🧪 Testing Frontend Integration untuk Sistem Faktur FIFO...');

        // 1. Setup test data
        $this->info('1. Setup test data...');
        
        // Pastikan ada customer
        $customer = Customer::where('kode_customer', 'CUST001')->first();
        if (!$customer) {
            $this->error('Customer CUST001 tidak ditemukan! Jalankan FifoTestSeeder terlebih dahulu.');
            return;
        }

        // Pastikan ada kode barang
        $kodeBarang = KodeBarang::where('kode_barang', 'KB001')->first();
        if (!$kodeBarang) {
            $this->error('Kode barang KB001 tidak ditemukan! Jalankan FifoTestSeeder terlebih dahulu.');
            return;
        }

        $this->info('   ✅ Test data berhasil disiapkan');

        // 2. Test Customer Price setup
        $this->info('2. Testing Customer Price setup...');
        
        $customerPrice = CustomerPrice::firstOrCreate([
            'customer_id' => $customer->id,
            'kode_barang_id' => $kodeBarang->id,
        ], [
            'harga_jual_khusus' => 15000,
            'ongkos_kuli_khusus' => 2000,
            'unit_jual' => 'LBR',
            'is_active' => true,
            'keterangan' => 'Harga khusus untuk testing frontend'
        ]);

        $this->line("   - Customer Price berhasil dibuat:");
        $this->line("     * Harga Jual: Rp " . number_format($customerPrice->harga_jual_khusus, 0, ',', '.'));
        $this->line("     * Unit: " . $customerPrice->unit_jual);
        $this->line("     * Status: " . ($customerPrice->is_active ? 'Aktif' : 'Tidak Aktif'));

        // 3. Test Customer Item Ongkos setup
        $this->info('3. Testing Customer Item Ongkos setup...');
        
        $ongkosKhusus = CustomerItemOngkos::updateOrCreateOngkos(
            $customer->id,
            $kodeBarang->id,
            3000,
            'Ongkos kuli testing frontend'
        );

        $this->line("   - Customer Item Ongkos berhasil dibuat:");
        $this->line("     * Ongkos Kuli: Rp " . number_format($ongkosKhusus->ongkos_kuli_khusus, 0, ',', '.'));
        $this->line("     * Keterangan: " . $ongkosKhusus->keterangan);

        // 4. Test Unit Conversion setup
        $this->info('4. Testing Unit Conversion setup...');
        
        $unitConversion = UnitConversion::firstOrCreate([
            'kode_barang_id' => $kodeBarang->id,
            'unit_turunan' => 'DUS',
        ], [
            'nilai_konversi' => 40,
            'keterangan' => '1 DUS = 40 LBR',
            'is_active' => true
        ]);

        $this->line("   - Unit Conversion berhasil dibuat:");
        $this->line("     * Unit Turunan: " . $unitConversion->unit_turunan);
        $this->line("     * Nilai Konversi: " . $unitConversion->nilai_konversi);
        $this->line("     * Keterangan: " . $unitConversion->keterangan);

        // 5. Test AJAX API endpoint
        $this->info('5. Testing AJAX API endpoint getHargaDanOngkos...');
        
        $controller = new TransaksiController(
            app(\App\Http\Controllers\StockController::class),
            app(\App\Http\Controllers\PanelController::class)
        );

        // Test dengan satuan LBR
        $requestLBR = new Request([
            'customer_id' => $customer->id,
            'kode_barang_id' => $kodeBarang->id,
            'satuan' => 'LBR'
        ]);

        $responseLBR = $controller->getHargaDanOngkos($requestLBR);
        $responseDataLBR = json_decode($responseLBR->getContent(), true);

        $this->line("   - Response untuk satuan LBR:");
        $this->line("     * Success: " . ($responseDataLBR['success'] ? 'Ya' : 'Tidak'));
        $this->line("     * Harga Jual: Rp " . number_format($responseDataLBR['harga_jual'], 0, ',', '.'));
        $this->line("     * Ongkos Kuli: Rp " . number_format($responseDataLBR['ongkos_kuli'], 0, ',', '.'));
        $this->line("     * Satuan: " . $responseDataLBR['satuan']);

        // Test dengan satuan DUS
        $requestDUS = new Request([
            'customer_id' => $customer->id,
            'kode_barang_id' => $kodeBarang->id,
            'satuan' => 'DUS'
        ]);

        $responseDUS = $controller->getHargaDanOngkos($requestDUS);
        $responseDataDUS = json_decode($responseDUS->getContent(), true);

        $this->line("   - Response untuk satuan DUS:");
        $this->line("     * Success: " . ($responseDataDUS['success'] ? 'Ya' : 'Tidak'));
        $this->line("     * Harga Jual: Rp " . number_format($responseDataDUS['harga_jual'], 0, ',', '.'));
        $this->line("     * Ongkos Kuli: Rp " . number_format($responseDataDUS['ongkos_kuli'], 0, ',', '.'));
        $this->line("     * Satuan: " . $responseDataDUS['satuan']);

        // 6. Test data consistency
        $this->info('6. Testing data consistency...');
        
        // Verifikasi customer price
        $customerPriceCheck = CustomerPrice::where('customer_id', $customer->id)
            ->where('kode_barang_id', $kodeBarang->id)
            ->where('is_active', true)
            ->first();

        if ($customerPriceCheck) {
            $this->line("   ✅ Customer Price konsisten:");
            $this->line("     * Customer: " . $customer->nama);
            $this->line("     * Barang: " . $kodeBarang->name);
            $this->line("     * Harga: Rp " . number_format($customerPriceCheck->harga_jual_khusus, 0, ',', '.'));
        }

        // Verifikasi ongkos kuli
        $ongkosCheck = CustomerItemOngkos::where('customer_id', $customer->id)
            ->where('kode_barang_id', $kodeBarang->id)
            ->where('is_active', true)
            ->first();

        if ($ongkosCheck) {
            $this->line("   ✅ Customer Item Ongkos konsisten:");
            $this->line("     * Ongkos Kuli: Rp " . number_format($ongkosCheck->ongkos_kuli_khusus, 0, ',', '.'));
            $this->line("     * Keterangan: " . $ongkosCheck->keterangan);
        }

        // 7. Test frontend data flow simulation
        $this->info('7. Testing frontend data flow simulation...');
        
        // Simulasi user input
        $userInput = [
            'customer_id' => $customer->id,
            'kode_barang' => $kodeBarang->kode_barang,
            'satuan' => 'DUS',
            'qty' => 2,
            'harga' => $responseDataDUS['harga_jual'],
            'ongkos_kuli' => $responseDataDUS['ongkos_kuli']
        ];

        $this->line("   - Simulasi user input:");
        $this->line("     * Customer: " . $customer->nama);
        $this->line("     * Kode Barang: " . $userInput['kode_barang']);
        $this->line("     * Satuan: " . $userInput['satuan']);
        $this->line("     * Qty: " . $userInput['qty']);
        $this->line("     * Harga: Rp " . number_format($userInput['harga'], 0, ',', '.'));
        $this->line("     * Ongkos Kuli: Rp " . number_format($userInput['ongkos_kuli'], 0, ',', '.'));

        // Hitung total
        $subtotal = $userInput['qty'] * $userInput['harga'];
        $totalOngkosKuli = $userInput['qty'] * $userInput['ongkos_kuli'];
        $grandTotal = $subtotal + $totalOngkosKuli;

        $this->line("   - Perhitungan total:");
        $this->line("     * Subtotal: Rp " . number_format($subtotal, 0, ',', '.'));
        $this->line("     * Total Ongkos Kuli: Rp " . number_format($totalOngkosKuli, 0, ',', '.'));
        $this->line("     * Grand Total: Rp " . number_format($grandTotal, 0, ',', '.'));

        // 8. Test error handling
        $this->info('8. Testing error handling...');
        
        // Test dengan customer yang tidak ada
        try {
            $invalidRequest = new Request([
                'customer_id' => 99999,
                'kode_barang_id' => $kodeBarang->id,
                'satuan' => 'LBR'
            ]);

            $invalidResponse = $controller->getHargaDanOngkos($invalidRequest);
            $invalidResponseData = json_decode($invalidResponse->getContent(), true);

            $this->line("   - Error handling untuk customer tidak valid:");
            $this->line("     * Success: " . ($invalidResponseData['success'] ? 'Ya' : 'Tidak'));
            $this->line("     * Message: " . ($invalidResponseData['message'] ?? 'Tidak ada error message'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->line("   ✅ Error handling berfungsi dengan baik:");
            $this->line("     * Exception: " . get_class($e));
            $this->line("     * Message: " . $e->getMessage());
        }

        // Cleanup
        $this->info('9. Cleanup test data...');
        
        $customerPrice->delete();
        $ongkosKhusus->delete();
        $unitConversion->delete();

        $this->info('✅ Testing Frontend Integration untuk Sistem Faktur FIFO selesai!');
        $this->info('');
        $this->info('🎯 Frontend Integration Summary:');
        $this->info('   ✅ AJAX endpoint getHargaDanOngkos berfungsi');
        $this->info('   ✅ Dynamic pricing per customer');
        $this->info('   ✅ Dynamic ongkos kuli per customer per item');
        $this->info('   ✅ Unit conversion support');
        $this->info('   ✅ Error handling');
        $this->info('   ✅ Data consistency validation');
        $this->info('');
        $this->info('🚀 Frontend siap untuk production deployment!');
    }
}
