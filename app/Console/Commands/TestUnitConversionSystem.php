<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UnitConversionService;
use App\Models\KodeBarang;
use App\Models\UnitConversion;
use App\Models\CustomerPrice;
use App\Models\Customer;
use App\Models\KategoriBarang;

class TestUnitConversionSystem extends Command
{
    protected $signature = 'app:test-unit-conversion-system';
    protected $description = 'Test sistem konversi unit dan harga per customer';

    public function handle()
    {
        $this->info('🧪 Testing Sistem Konversi Unit & Harga...');
        $unitService = new UnitConversionService();

        // 1. Setup test data
        $this->info('1. Setup test data...');
        
        // Buat kategori barang
        $kategori = KategoriBarang::firstOrCreate(
            ['name' => 'Plastik Test'],
            ['description' => 'Kategori untuk testing unit conversion']
        );

        // Update kode barang test dengan kolom baru
        $kodeBarang = KodeBarang::where('kode_barang', 'KB001')->first();
        if ($kodeBarang) {
            $kodeBarang->update([
                'kategori_barang_id' => $kategori->id,
                'unit_dasar' => 'LBR',
                'harga_jual' => 12000,
                'ongkos_kuli_default' => 500
            ]);
        }

        // Buat unit conversion
        UnitConversion::firstOrCreate(
            ['kode_barang_id' => $kodeBarang->id, 'unit_turunan' => 'DUS'],
            [
                'nilai_konversi' => 40, // 1 DUS = 40 LBR
                'keterangan' => 'Konversi test',
                'is_active' => true
            ]
        );

        UnitConversion::firstOrCreate(
            ['kode_barang_id' => $kodeBarang->id, 'unit_turunan' => 'BOX'],
            [
                'nilai_konversi' => 20, // 1 BOX = 20 LBR
                'keterangan' => 'Konversi test',
                'is_active' => true
            ]
        );

        // Buat customer test
        $customer = Customer::firstOrCreate(
            ['kode_customer' => 'CUST001'],
            [
                'nama' => 'Customer Test Unit',
                'alamat' => 'Alamat Test',
                'hp' => '08123456789'
            ]
        );

        // Buat customer price khusus
        CustomerPrice::firstOrCreate(
            ['customer_id' => $customer->id, 'kode_barang_id' => $kodeBarang->id],
            [
                'harga_jual_khusus' => 13000, // Harga khusus lebih tinggi
                'ongkos_kuli_khusus' => 600,
                'unit_jual' => 'DUS', // Customer ini beli per DUS
                'is_active' => true,
                'keterangan' => 'Harga khusus untuk customer VIP'
            ]
        );

        $this->info('   ✅ Test data berhasil dibuat');

        // 2. Test konversi unit
        $this->info('2. Testing konversi unit...');
        
        // Test konversi ke unit dasar
        $qtyDus = 5;
        $qtyLbr = $unitService->convertToBaseUnit($kodeBarang->id, $qtyDus, 'DUS');
        $this->line("   - {$qtyDus} DUS = {$qtyLbr} LBR");

        $qtyBox = 3;
        $qtyLbr2 = $unitService->convertToBaseUnit($kodeBarang->id, $qtyBox, 'BOX');
        $this->line("   - {$qtyBox} BOX = {$qtyLbr2} LBR");

        // Test konversi dari unit dasar
        $qtyLbr3 = 100;
        $qtyDus2 = $unitService->convertFromBaseUnit($kodeBarang->id, $qtyLbr3, 'DUS');
        $this->line("   - {$qtyLbr3} LBR = {$qtyDus2} DUS");

        // 3. Test harga per customer
        $this->info('3. Testing harga per customer...');
        
        // Test customer dengan harga khusus
        $priceInfo = $unitService->getCustomerPrice($customer->id, $kodeBarang->id, 'LBR');
        $this->line("   - Customer {$customer->nama}:");
        $this->line("     * Harga: Rp " . number_format($priceInfo['harga_jual'], 2));
        $this->line("     * Ongkos Kuli: Rp " . number_format($priceInfo['ongkos_kuli'], 2));
        $this->line("     * Unit: {$priceInfo['unit']}");
        $this->line("     * Custom Price: " . ($priceInfo['is_custom_price'] ? 'Ya' : 'Tidak'));

        // Test customer tanpa harga khusus (gunakan default)
        $customer2 = Customer::firstOrCreate(
            ['kode_customer' => 'CUST002'],
            [
                'nama' => 'Customer Regular',
                'alamat' => 'Alamat Regular',
                'hp' => '08123456788'
            ]
        );

        $priceInfo2 = $unitService->getCustomerPrice($customer2->id, $kodeBarang->id, 'LBR');
        $this->line("   - Customer {$customer2->nama}:");
        $this->line("     * Harga: Rp " . number_format($priceInfo2['harga_jual'], 2));
        $this->line("     * Ongkos Kuli: Rp " . number_format($priceInfo2['ongkos_kuli'], 2));
        $this->line("     * Unit: {$priceInfo2['unit']}");
        $this->line("     * Custom Price: " . ($priceInfo2['is_custom_price'] ? 'Ya' : 'Tidak'));

        // 4. Test konversi harga
        $this->info('4. Testing konversi harga...');
        
        $hargaPerDus = $unitService->convertPrice($kodeBarang->id, 13000, 'DUS', 'LBR');
        $this->line("   - Harga 1 DUS (Rp 13.000) = Rp " . number_format($hargaPerDus, 2) . " per LBR");

        $hargaPerBox = $unitService->convertPrice($kodeBarang->id, 12000, 'LBR', 'BOX');
        $this->line("   - Harga 1 LBR (Rp 12.000) = Rp " . number_format($hargaPerBox, 2) . " per BOX");

        // 5. Test available units
        $this->info('5. Testing available units...');
        
        $availableUnits = $unitService->getAvailableUnits($kodeBarang->id);
        $this->line("   - Unit tersedia: " . implode(', ', $availableUnits));

        // 6. Test validasi unit
        $this->info('6. Testing validasi unit...');
        
        $isValidLbr = $unitService->isValidUnit($kodeBarang->id, 'LBR');
        $isValidDus = $unitService->isValidUnit($kodeBarang->id, 'DUS');
        $isValidKg = $unitService->isValidUnit($kodeBarang->id, 'KG');

        $this->line("   - LBR valid: " . ($isValidLbr ? 'Ya' : 'Tidak'));
        $this->line("   - DUS valid: " . ($isValidDus ? 'Ya' : 'Tidak'));
        $this->line("   - KG valid: " . ($isValidKg ? 'Ya' : 'Tidak'));

        $this->info('✅ Testing sistem unit conversion selesai!');
    }
}
