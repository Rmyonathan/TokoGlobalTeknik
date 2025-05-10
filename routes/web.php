<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\BookingsController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StokOwnerController;
use App\Http\Controllers\KodeBarangController;
use App\Http\Controllers\SuratJalanController;
use App\Http\Controllers\SuratJalanItemController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CaraBayarController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PerusahaanController;

use App\Models\StokOwner;
use App\Models\Supplier;
use App\Models\KodeBarang;
use App\Models\Bookings;
use App\Models\Logistics;
use App\Models\Transactions;
use App\Models\Customer;
use App\Models\Pembelian;
use App\Models\Transaksi;

Route::middleware(['web'])->group(function () {


    Route::post('/signin', [AccountsController::class, 'login']);
    Route::post('/logout', [AccountsController::class, 'logout']);

    Route::get('/profile', [AccountsController::class, 'profile']);

    Route::get('/signin', function () {
        return view('signin');
    })->name('signin');

    Route::get('', function () {
        return view('master.barang');
    })->name('master.barang');

});

Route::middleware(['web', 'role'])->group(function () {
    Route::get('/account-maintenance', [AccountsController::class, 'accountMaintenance']);

    Route::get('/viewKas', [KasController::class, 'viewKas']);
    Route::get('/viewSlide', [KasController::class, 'viewSlide']);
    Route::post('/delete_kas',
    [KasController::class, 'delete_kas']);
    Route::post('/cancel_kas',
    [KasController::class, 'cancel_kas']);
    Route::post('/edit_kas',
    [KasController::class, 'edit_kas']);
    Route::post('/update_kas',
    [KasController::class, 'update_kas']);



    Route::get('/addtransaction', function () {
        return view('addtransaction');
    });


    Route::get('/listutang', [KasController::class, 'viewDebt']);
    Route::get('/viewDebt', [KasController::class, 'viewDebt']);

    Route::get('/addtransaction', [KasController::class, 'index']);

    Route::get('/hutanglunas', [KasController::class, 'hutangLunas']);

    Route::get('/hutangbelumlunas', [KasController::class, 'hutangBelumLunas']);




    Route::get('/kas/add', [KasController::class, 'create'])->name('kas.create');
    Route::post('/kas/add', [KasController::class, 'store'])->name('kas.store');




    Route::post('/editAccount', [AccountsController::class, 'editAccount']);
    Route::post('/updateProfile', [AccountsController::class, 'updateProfile']);
    Route::post('/switchDatabase', [AccountsController::class, 'switchDatabase']);

    // Panel Inventory
    Route::get('/panels/inventory', [PanelController::class, 'inventory'])
    ->name('panels.inventory');

    // Create Order Form
    Route::get('/panels/order', [PanelController::class, 'createOrder'])
    ->name('panels.create-order');

    // Process Order
    Route::post('/panels/order', [PanelController::class, 'storeOrder'])
    ->name('panels.store-order');

    // Add to Inventory Form
    Route::get('/panels/add', [PanelController::class, 'createInventory'])
    ->name('panels.create-inventory');
    Route::get('/kode_barang/add', [KodeBarangController::class, 'createCode'])
    ->name('code.create-code');

    Route::get('/kode_barang/view', [KodeBarangController::class, 'viewCode'])
    ->name('code.view-code');

    Route::get('/panels/edit/{id}', [PanelController::class, 'editInventory'])
    ->name('panels.edit-inventory');

    // Store New Inventory
    Route::post('/panels/add', [PanelController::class, 'storeInventory'])
    ->name('panels.store-inventory');

    Route::post('/kode_barang/add', [KodeBarangController::class, 'storeCode'])
    ->name('code.store-code');

    Route::post('/panels/edit', [PanelController::class, 'updateInventory'])
    ->name('panels.update-inventory');
    Route::post('/panels/delete/{id}', [PanelController::class, 'deleteInventory'])
    ->name('panels.delete-inventory');

    //ROute edit and delete kode barang -yoyo
    Route::get('/code/edit/{id}', [KodeBarangController::class, 'edit'])->name('code.edit');
    Route::put('/code/update/{id}', [KodeBarangController::class, 'update'])->name('code.update');
    Route::delete('/code/delete/{id}', [KodeBarangController::class, 'destroy'])->name('code.delete');

    // Route::get('/master/barang', function () {
    //     return view('master.barang');
    // })->name('master.barang');

    Route::get('/master/barang', [PanelController::class, 'viewBarang'])->name('master.barang');

    // Mutasi Stok Barang
    Route::get('master.mutasistokbarang', function () {
        return view('master.mutasistokbarang');
        })->name('master.mutasistokbarang');


    Route::get('master/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('master/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('master/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('master/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('master/customers/search', [CustomerController::class, 'getCustomers'])->name('customers.search');

    Route::get('master/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('master/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::put('master/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('master/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('/master/stok_owner', [StokOwnerController::class, 'index'])->name('stok_owner.index');
    Route::post('/master/stok_owner', [StokOwnerController::class, 'store'])->name('stok_owner.store');
    Route::delete('/master/stok_owner/{stokOwner}', [StokOwnerController::class, 'destroy'])->name('stok_owner.destroy');

    // Transaksi Penjualan Routes
    Route::get('/transaksi/penjualan', [TransaksiController::class, 'penjualan'])->name('transaksi.penjualan');
    Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::get('/transaksi/{id}', [TransaksiController::class, 'getTransaction'])->name('transaksi.get');
    Route::get('/transaksi/shownota/{id}', [TransaksiController::class, 'showNota'])->name('transaksi.shownota');
    Route::get('/transaksi/nota/{id}', [TransaksiController::class, 'nota'])->name('transaksi.nota');

    // Penjualan Per Customer
    Route::get('/penjualanpercustomer', [TransaksiController::class, 'penjualanPercustomer'])->name('transaksi.penjualancustomer');
    Route::get('/api/getpenjualancustomer',[TransaksiController::class, 'getPenjualan']);

    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
    Route::get('/api/sales/search', [StokOwnerController::class, 'search'])->name('api.sales.search');
    Route::get('/api/panels/search', [PanelController::class, 'search'])->name('api.panels.search');
    Route::get('/api/suppliers/search', [SupplierController::class, 'search'])->name('api.suppliers.search');
    
    // Display Transaksi Penjualan
    Route::get('transaksi.penjualan', function () {
        return view('transaksi.displaypenjualan');
        })->name('transaksi.displaypenjualan');

    // Lihat Nota
    Route::get('/transaksi/lihatnota/{id}', [TransaksiController::class, 'showNota'])->name('transaksi.lihatnota');
    Route::get('/lihat_nota', [TransaksiController::class, 'listNota'])->name('transaksi.listnota');

    // Pembelian
    // Pembelian Barang (dummy atau real)
    Route::get('/pembelian', function () {
        return view('pembelian.addpembelian'); // karena file-nya langsung di views/
    })->name('pembelian.form');

    Route::get('/panel/{group_id}', [TransaksiController::class, 'getByGroupId'])->name('api.panel.get');

    // History Pembelian
    Route::get('/pembelian/historypembelian', function () {
        return view('pembelian.historypembelian'); // karena file-nya langsung di views/
    })->name('pembelian.historypembelian');
    
    // Cara Bayar
    Route::prefix('master')->group(function () {
        Route::get('/cara_bayar', [CaraBayarController::class, 'index'])->name('master.cara_bayar');
        Route::post('/cara_bayar', [CaraBayarController::class, 'store'])->name('master.cara_bayar.store');
        Route::delete('/cara_bayar/{id}', [CaraBayarController::class, 'destroy'])->name('master.cara_bayar.destroy');
    });

    Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
    Route::get('/perusahaan/create', [PerusahaanController::class, 'create'])->name('perusahaan.create');
    Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
    Route::get('/perusahaan/{id}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
    Route::put('/perusahaan/{id}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
    Route::delete('/perusahaan/{id}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy');

    Route::get('/api/cara-bayar/by-metode', function (Illuminate\Http\Request $request) {
        $metode = $request->query('metode');
        return \App\Models\CaraBayar::where('metode', $metode)->get();
    });     

    // API for ajax calls
    Route::prefix('api')->group(function () {
        Route::get('/products/search', [TransaksiController::class, 'searchProducts'])->name('api.products.search');
        Route::get('/customers/search', [TransaksiController::class, 'searchCustomers'])->name('api.customers.search');
        Route::post('/customers/create', [TransaksiController::class, 'createCustomer'])->name('api.customers.create');
    });
    
    Route::get('/api/customers', [CustomerController::class, 'searchsuratjalan'])->name('api.customers');
    Route::get('/api/sales/search', [StokOwnerController::class, 'search'])->name('api.sales.search');
    Route::get('/api/panels/search', [PanelController::class, 'search'])->name('api.panels.search');
    Route::get('/api/transaksi', [TransaksiController::class, 'getTransaksi'])->name('api.transaksi');
    Route::get('/api/searchfaktur', [TransaksiController::class,'getTransaksiByCustomer'])->name('api.faktur.search');
    Route::get('/api/suratjalan/transaksiitem/{transaksiId}', [TransaksiController::class, 'getRincianTransaksi'])->name('api.rinciantransaksi');
    Route::get('/api/transaksi/items/{transaksiId}', [TransaksiController::class, 'getTransaksiItems'])->name('api.transaksi.items');
    Route::get('/kode-barang/search', [KodeBarangController::class, 'searchKodeBarang'])->name('kodeBarang.search');

    // Surat Jalan
    Route::prefix('suratjalan')->group(function () {
        Route::get('/create', [SuratJalanController::class, 'create'])->name('suratjalan.create');
        Route::post('/store', [SuratJalanController::class, 'store'])->name('suratjalan.store');
        Route::get('/history', [SuratJalanController::class, 'history'])->name('suratjalan.history');
        Route::get('/detail/{id}', [SuratJalanController::class, 'detail'])->name('suratjalan.detail');
    });

    
    // Main transaction page pembelian
    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::post('/pembelian/store', [PembelianController::class, 'store'])->name('pembelian.store'); // Store transaction
    Route::get('/pembelian/{id}', [PembelianController::class, 'getPurchase'])->name('pembelian.get');// Get transaction data
    // Show invoice pembelian
    Route::get('/pembelian/lihatnota/{id}', [PembelianController::class, 'showNota'])->name('pembelian.nota.show');
    Route::get('/pembelian/nota/{nota}', [PembelianController::class, 'nota'])->name('pembelian.nota');// View nota by nota number
    Route::get('/pembelian/lihat/nota', [PembelianController::class, 'listNota'])->name('pembelian.nota.list');  // List all nota

    // New routes for edit and delete
    Route::get('/edit/{id}', [PembelianController::class, 'edit'])->name('pembelian.edit');
    Route::post('/update/{id}', [PembelianController::class, 'update'])->name('pembelian.update');
    Route::delete('/delete/{id}', [PembelianController::class, 'destroy'])->name('pembelian.delete');

    // Stock Management Routes
    Route::get('/stock/mutasi', [StockController::class, 'mutasiStock'])->name('stock.mutasi');
    Route::get('/stock/print-good', [StockController::class, 'printGoodStock'])->name('stock.print.good');
    Route::get('/stock/get', [StockController::class, 'getStock'])->name('stock.get');
    Route::get('/stock/mutations', [StockController::class, 'getStockMutations'])->name('stock.mutations');

    // Route ke halaman list PO
    Route::get('/transaksi.purchaseorder', [PurchaseOrderController::class, 'index'])->name('transaksi.purchaseorder');
    // Route ke halaman detail PO
    Route::get('/transaksi/purchaseorder/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-order.show');
    // Route buat nyimpen PO dari form penjualan
    Route::post('/transaksi/purchaseorder/store', [PurchaseOrderController::class, 'store'])->name('purchase-order.store');
    // Route buat nyelesain PO (ubah jadi completed dan isi tanggal_jadi)
    Route::post('/transaksi/purchaseorder/{id}/complete', [PurchaseOrderController::class, 'completeTransaction'])->name('purchase-order.complete');
    // Route buat cancel PO
    Route::patch('/transaksi/purchaseorder/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-order.cancel');

});