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
use App\Models\StokOwner;
use App\Models\Supplier;
use App\Models\Bookings;
use App\Models\Logistics;
use App\Models\Transactions;
use App\Models\Customer;
use App\Models\KodeBarang;

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




    Route::post('/addTransaction', [KasController::class, 'addTransaction']);



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

    // Route::get('/master/barang', function () {
    //     return view('master.barang');
    // })->name('master.barang');

    Route::get('/master/barang', [PanelController::class, 'viewBarang'])->name('master.barang');

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
    Route::get('/transaksi/nota/{id}', [TransaksiController::class, 'nota'])->name('transaksi.nota');
    // Penjualan Per Customer
    Route::get('/transaksi/datapenjualanpercustomer', [TransaksiController::class, 'datapenjualanpercustomer'])->name('transaksi.datapenjualanpercustomer');


    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
    Route::get('/api/sales/search', [StokOwnerController::class, 'search'])->name('api.sales.search');
    Route::get('/api/panels/search', [PanelController::class, 'search'])->name('api.panels.search');

    // Display Transaksi Penjualan
    Route::get('transaksi.penjualan', function () {
        return view('transaksi.displaypenjualan');
        })->name('transaksi.displaypenjualan');

    // Lihat Nota
    Route::get('/transaksi/lihatnota/{id}', [TransaksiController::class, 'showNota'])->name('transaksi.lihatnota');
    Route::get('/lihat_nota', [TransaksiController::class, 'listNota'])->name('transaksi.listnota');

    // Surat Jalan
    Route::get('/suratjalan', function () {
        return view('suratjalan.suratjalan');
        })->name('suratjalan.form');

    // History Surat Jalan
    Route::get('/suratjalan/historysuratjalan', function () {
        return view('suratjalan.historysuratjalan');
        })->name('suratjalan.historysuratjalan');

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

    // Master Cara Bayar (web.php)
    Route::get('/master/cara_bayar', function () {
        return view('master.cara_bayar'); // karena file-nya langsung di views/
    })->name('cara_bayar.form');

    Route::post('/mastercarabayar', [TransaksiController::class, 'store'])
    ->name('panels.mastercarabayar');

    // API for ajax calls
    Route::prefix('api')->group(function () {
        Route::get('/products/search', [TransaksiController::class, 'searchProducts'])->name('api.products.search');
        Route::get('/customers/search', [TransaksiController::class, 'searchCustomers'])->name('api.customers.search');
        Route::post('/customers/create', [TransaksiController::class, 'createCustomer'])->name('api.customers.create');
    });

});


