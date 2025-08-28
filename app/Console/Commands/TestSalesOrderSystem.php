<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\KodeBarang;
use App\Models\StokOwner;
use App\Services\UnitConversionService;
use App\Services\CustomerCreditService;

class TestSalesOrderSystem extends Command
{
    protected $signature = 'app:test-sales-order-system';
    protected $description = 'Test sistem Sales Order (SO)';

    public function handle()
    {
        $this->info('🧪 Testing Sistem Sales Order...');
        $unitService = new UnitConversionService();
        $creditService = new CustomerCreditService();

        // 1. Setup test data
        $this->info('1. Setup test data...');
        
        // Ambil customer kredit
        $customer = Customer::where('kode_customer', 'CUST001')->first();
        if (!$customer) {
            $this->error('Customer CUST001 tidak ditemukan! Jalankan TestCustomerCreditSystem terlebih dahulu.');
            return;
        }

        // Ambil salesman
        $salesman = StokOwner::where('kode_stok_owner', 'SALES001')->first();
        if (!$salesman) {
            $this->error('Salesman SALES001 tidak ditemukan! Jalankan TestFifoSale terlebih dahulu.');
            return;
        }

        // Ambil kode barang
        $kodeBarang = KodeBarang::where('kode_barang', 'KB001')->first();
        if (!$kodeBarang) {
            $this->error('Kode barang KB001 tidak ditemukan! Jalankan FifoTestSeeder terlebih dahulu.');
            return;
        }

        $this->info('   ✅ Test data berhasil ditemukan');

        // 2. Test generate nomor SO
        $this->info('2. Testing generate nomor SO...');
        
        $noSo1 = SalesOrder::generateNoSo();
        $noSo2 = SalesOrder::generateNoSo();
        
        $this->line("   - Nomor SO 1: {$noSo1}");
        $this->line("   - Nomor SO 2: {$noSo2}");

        // 3. Test create Sales Order
        $this->info('3. Testing create Sales Order...');
        
        $salesOrder = SalesOrder::create([
            'no_so' => $noSo1,
            'tanggal' => now(),
            'customer_id' => $customer->id,
            'salesman_id' => $salesman->id,
            'status' => 'pending',
            'cara_bayar' => 'Kredit',
            'hari_tempo' => 14,
            'tanggal_estimasi' => now()->addDays(7),
            'keterangan' => 'Test Sales Order'
        ]);

        $this->line("   - Sales Order berhasil dibuat: {$salesOrder->no_so}");
        $this->line("   - Customer: {$salesOrder->customer->nama}");
        $this->line("   - Salesman: {$salesOrder->salesman->nama}");
        $this->line("   - Status: {$salesOrder->getStatusText()}");

        // 4. Test create Sales Order Items
        $this->info('4. Testing create Sales Order Items...');
        
        // Item 1 - LBR
        $item1 = SalesOrderItem::create([
            'sales_order_id' => $salesOrder->id,
            'kode_barang_id' => $kodeBarang->id,
            'qty' => 100,
            'satuan' => 'LBR',
            'harga' => 12000,
            'total' => 1200000,
            'qty_terkirim' => 0,
            'qty_sisa' => 100,
            'keterangan' => 'Test item LBR'
        ]);

        // Item 2 - DUS (jika ada konversi unit)
        $unitConversions = $unitService->getAvailableUnits($kodeBarang->id);
        if (in_array('DUS', $unitConversions)) {
            $item2 = SalesOrderItem::create([
                'sales_order_id' => $salesOrder->id,
                'kode_barang_id' => $kodeBarang->id,
                'qty' => 5,
                'satuan' => 'DUS',
                'harga' => 480000, // 40 LBR * 12000
                'total' => 2400000,
                'qty_terkirim' => 0,
                'qty_sisa' => 5,
                'keterangan' => 'Test item DUS'
            ]);
        }

        $this->line("   - Item 1: {$item1->qty} {$item1->satuan} @ Rp " . number_format($item1->harga, 0, ',', '.'));
        if (isset($item2)) {
            $this->line("   - Item 2: {$item2->qty} {$item2->satuan} @ Rp " . number_format($item2->harga, 0, ',', '.'));
        }

        // 5. Test update total Sales Order
        $this->info('5. Testing update total Sales Order...');
        
        $subtotal = $salesOrder->items->sum('total');
        $salesOrder->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal
        ]);

        $this->line("   - Subtotal: Rp " . number_format($salesOrder->subtotal, 0, ',', '.'));
        $this->line("   - Grand Total: Rp " . number_format($salesOrder->grand_total, 0, ',', '.'));

        // 6. Test helper methods
        $this->info('6. Testing helper methods...');
        
        $this->line("   - Is Pending: " . ($salesOrder->isPending() ? 'Ya' : 'Tidak'));
        $this->line("   - Can Be Approved: " . ($salesOrder->canBeApproved() ? 'Ya' : 'Tidak'));
        $this->line("   - Can Be Canceled: " . ($salesOrder->canBeCanceled() ? 'Ya' : 'Tidak'));
        $this->line("   - Total Qty Sisa: " . $salesOrder->getTotalQtySisa());
        $this->line("   - Total Qty Terkirim: " . $salesOrder->getTotalQtyTerkirim());
        $this->line("   - Is Fully Shipped: " . ($salesOrder->isFullyShipped() ? 'Ya' : 'Tidak'));

        // 7. Test status transitions
        $this->info('7. Testing status transitions...');
        
        // Approve
        $salesOrder->update(['status' => 'approved']);
        $this->line("   - Status setelah approve: {$salesOrder->getStatusText()}");
        $this->line("   - Can Be Processed: " . ($salesOrder->canBeProcessed() ? 'Ya' : 'Tidak'));

        // Process
        $salesOrder->update(['status' => 'processed']);
        $this->line("   - Status setelah process: {$salesOrder->getStatusText()}");
        $this->line("   - Can Be Canceled: " . ($salesOrder->canBeCanceled() ? 'Ya' : 'Tidak'));

        // 8. Test item shipping simulation
        $this->info('8. Testing item shipping simulation...');
        
        // Simulasi pengiriman sebagian
        $item1->addQtyTerkirim(50);
        $this->line("   - Item 1 setelah kirim 50 LBR:");
        $this->line("     * Qty Terkirim: {$item1->qty_terkirim}");
        $this->line("     * Qty Sisa: {$item1->qty_sisa}");
        $this->line("     * Shipped Status: {$item1->getShippedStatus()}");
        $this->line("     * Shipped Percentage: {$item1->getShippedPercentage()}%");

        // Simulasi pengiriman lengkap
        $item1->addQtyTerkirim(50);
        $this->line("   - Item 1 setelah kirim lengkap:");
        $this->line("     * Qty Terkirim: {$item1->qty_terkirim}");
        $this->line("     * Qty Sisa: {$item1->qty_sisa}");
        $this->line("     * Shipped Status: {$item1->getShippedStatus()}");
        $this->line("     * Is Fully Shipped: " . ($item1->isFullyShipped() ? 'Ya' : 'Tidak'));

        // 9. Test credit validation
        $this->info('9. Testing credit validation...');
        
        $kelayakan = $creditService->cekKelayakanKredit($customer->id, $salesOrder->grand_total);
        $this->line("   - Kelayakan kredit untuk SO ini:");
        $this->line("     * Layak: " . ($kelayakan['layak'] ? 'Ya' : 'Tidak'));
        $this->line("     * Alasan: {$kelayakan['alasan']}");
        $this->line("     * Sisa Limit: Rp " . number_format($kelayakan['sisa_limit'], 0, ',', '.'));

        // 10. Test unit conversion in SO context
        $this->info('10. Testing unit conversion in SO context...');
        
        $qtyInBaseUnit = $unitService->convertToBaseUnit($kodeBarang->id, 5, 'DUS');
        $this->line("   - 5 DUS = {$qtyInBaseUnit} LBR");

        $customerPrice = $unitService->getCustomerPrice($customer->id, $kodeBarang->id, 'DUS');
        $this->line("   - Harga customer untuk DUS: Rp " . number_format($customerPrice['harga_jual'], 0, ',', '.'));

        // Cleanup
        $salesOrder->delete();

        $this->info('✅ Testing sistem Sales Order selesai!');
    }
}
