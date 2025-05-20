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
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\KategoriBarangController;

use App\Models\StokOwner;
use App\Models\Supplier;
use App\Models\KodeBarang;
use App\Models\Bookings;
use App\Models\Logistics;
use App\Models\Transactions;
use App\Models\Customer;
use App\Models\Pembelian;
use App\Models\Transaksi;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Public routes (authentication)
Route::middleware(['web'])->group(function () {
    Route::post('/signin', [AccountsController::class, 'login']);
    Route::post('/logout', [AccountsController::class, 'logout']);
    Route::get('/signin', function () {
        return view('signin');
    })->name('signin');

    Route::get('/login', function () {
        return redirect()->route('signin');
    })->name('login');
});

// Protected routes (need authentication)
Route::middleware(['web', 'auth'])->group(function () {
    // Dashboard route
    Route::get('', [PanelController::class, 'viewBarang'])
        ->middleware('permission:view dashboard')
        ->name('master.barang');
    
    // Profile route - accessible by all authenticated users
    Route::get('/profile', [AccountsController::class, 'profile']);
    Route::post('/updateProfile', [AccountsController::class, 'updateProfile'])->name('updateProfile');
    Route::post('/switchDatabase', [AccountsController::class, 'switchDatabase']);
    
    // ==============================
    // USER MANAGEMENT SECTION
    // ==============================
    
    // User Management routes
    Route::group(['middleware' => ['permission:edit users']], function () {
        Route::get('/account-maintenance', [AccountsController::class, 'accountMaintenance'])->name('accounts.maintenance');
        Route::post('/editAccount', [AccountsController::class, 'editAccount']);
        Route::get('/createAccount', [AccountsController::class, 'createAccount'])->name('createAccount');
        Route::post('/storeAccount', [AccountsController::class, 'storeAccount'])->name('storeAccount');
    });
    
    // Role Management routes
    Route::group(['middleware' => ['permission:manage roles'], 'prefix' => 'roles'], function () {
        Route::get('/createRole', [AccountsController::class, 'createRole'])->name('createRole');
        Route::post('/storeRole', [AccountsController::class, 'storeRole'])->name('storeRole');
    });
    
    // ==============================
    // MASTER DATA SECTION
    // ==============================
    
    // Master Barang routes
    Route::group(['middleware' => ['permission:view master data'], 'prefix' => 'master'], function () {
        Route::get('/barang', [PanelController::class, 'viewBarang'])->name('master.barang');
        
        // Mutasi Stok Barang
        Route::get('/mutasistokbarang', function () {
            return view('master.mutasistokbarang');
        })->name('master.mutasistokbarang');
    });
    
    // Customer Management routes
    Route::group(['middleware' => ['permission:manage customers'], 'prefix' => 'master'], function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });
    
    // Supplier Management routes
    Route::group(['middleware' => ['permission:manage suppliers'], 'prefix' => 'master'], function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
    
    // Stok Owner Management routes
    Route::group(['middleware' => ['permission:manage stok owner'], 'prefix' => 'master'], function () {
        Route::get('/stok_owner', [StokOwnerController::class, 'index'])->name('stok_owner.index');
        Route::post('/stok_owner', [StokOwnerController::class, 'store'])->name('stok_owner.store');
        Route::delete('/stok_owner/{stokOwner}', [StokOwnerController::class, 'destroy'])->name('stok_owner.destroy');
    });
    
    // Cara Bayar Management routes
    Route::group(['middleware' => ['permission:manage cara bayar'], 'prefix' => 'master'], function () {
        Route::get('/cara_bayar', [CaraBayarController::class, 'index'])->name('master.cara_bayar');
        Route::post('/cara_bayar', [CaraBayarController::class, 'store'])->name('master.cara_bayar.store');
        Route::delete('/cara_bayar/{id}', [CaraBayarController::class, 'destroy'])->name('master.cara_bayar.destroy');
    });
    
    // Perusahaan Management routes
    Route::group(['middleware' => ['permission:manage perusahaan']], function () {
        Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
        Route::get('/perusahaan/create', [PerusahaanController::class, 'create'])->name('perusahaan.create');
        Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
        Route::get('/perusahaan/{id}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
        Route::put('/perusahaan/{id}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
        Route::delete('/perusahaan/{id}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy');
        Route::post('/perusahaan/{id}/set-default', [PerusahaanController::class, 'setDefault'])->name('perusahaan.set-default');
    });
    
    // Kategori routes
    Route::group(['middleware' => ['permission:manage categories'], 'prefix' => 'kategori'], function () {
        Route::get('/', [KategoriBarangController::class, 'index'])->name('kategori.index');
        Route::get('/create', [KategoriBarangController::class, 'create'])->name('kategori.create');
        Route::post('/', [KategoriBarangController::class, 'store'])->name('kategori.store');
        Route::get('/{id}/edit', [KategoriBarangController::class, 'edit'])->name('kategori.edit');
        Route::put('/{id}', [KategoriBarangController::class, 'update'])->name('kategori.update');
        Route::delete('/{id}', [KategoriBarangController::class, 'destroy'])->name('kategori.destroy');
    });
    
    // Kode Barang Management routes
    Route::group(['middleware' => ['permission:manage kode barang'], 'prefix' => 'kode_barang'], function () {
        Route::get('/add', [KodeBarangController::class, 'createCode'])->name('code.create-code');
        Route::get('/view', [KodeBarangController::class, 'viewCode'])->name('code.view-code');
        Route::post('/add', [KodeBarangController::class, 'storeCode'])->name('code.store-code');
        
        // Edit and delete kode barang
        Route::get('/edit/{id}', [KodeBarangController::class, 'edit'])->name('code.edit');
        Route::put('/update/{id}', [KodeBarangController::class, 'update'])->name('code.update');
        Route::delete('/delete/{id}', [KodeBarangController::class, 'destroy'])->name('code.delete');
    });
    
    // ==============================
    // PANEL MANAGEMENT SECTION
    // ==============================
    
    // Panel Inventory routes
    Route::group(['middleware' => ['permission:manage panels'], 'prefix' => 'panels'], function () {
        // Panel Inventory
        Route::get('/inventory', [PanelController::class, 'inventory'])->name('panels.inventory');
        
        // Create Order Form
        Route::get('/order', [PanelController::class, 'createOrder'])->name('panels.create-order');
        Route::post('/order', [PanelController::class, 'repackOrder'])->name('panels.store-order');
        
        // Add to Inventory Form
        Route::get('/add', [PanelController::class, 'createInventory'])->name('panels.create-inventory');
        Route::post('/add', [PanelController::class, 'storeInventory'])->name('panels.store-inventory');
        
        // Repack
        Route::get('/repack', [PanelController::class, 'repack'])->name('panels.repack');
        Route::get('/print-receipt/{id}', [PanelController::class, 'printReceipt'])->name('panels.print-receipt');
        Route::get('/view-order/{id}', [PanelController::class, 'viewOrder'])->name('panels.view-order');
        
        // Edit and Delete
        Route::get('/edit/{id}', [PanelController::class, 'editInventory'])->name('panels.edit-inventory');
        Route::post('/edit', [PanelController::class, 'updateInventory'])->name('panels.update-inventory');
        Route::post('/delete/{id}', [PanelController::class, 'deleteInventory'])->name('panels.delete-inventory');
    });
    
    // ==============================
    // TRANSACTION SECTION
    // ==============================
    
    // Kas Management routes
    Route::group(['middleware' => ['permission:view kas|manage kas']], function () {
        Route::get('/viewKas', [KasController::class, 'viewKas'])->name('kas.view');
        Route::get('/viewSlide', [KasController::class, 'viewSlide']);
        Route::get('/listutang', [KasController::class, 'viewDebt']);
        Route::get('/viewDebt', [KasController::class, 'viewDebt']);
        Route::get('/hutanglunas', [KasController::class, 'hutangLunas']);
        Route::get('/hutangbelumlunas', [KasController::class, 'hutangBelumLunas']);
    });
    
    Route::group(['middleware' => ['permission:manage kas'], 'prefix' => 'kas'], function () {
        Route::get('/add', [KasController::class, 'create'])->name('kas.create');
        Route::post('/add', [KasController::class, 'store'])->name('kas.store');
        Route::post('/delete', [KasController::class, 'delete_kas'])->name('kas.delete');
        Route::post('/cancel', [KasController::class, 'cancel_kas'])->name('kas.cancel');
        Route::post('/edit', [KasController::class, 'edit_kas']);
        Route::post('/update', [KasController::class, 'update_kas']);
        Route::get('/addtransaction', [KasController::class, 'index']);
    });
    
    // Transaksi Penjualan routes
    // Transaksi Routes - Reordered to avoid route conflicts
    Route::group(['middleware' => ['permission:manage penjualan'], 'prefix' => 'transaksi'], function () {
        // Static routes first (no parameters)
        Route::get('/', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/penjualan', [TransaksiController::class, 'penjualan'])->name('transaksi.penjualan');
        Route::post('/store', [TransaksiController::class, 'store'])->name('transaksi.store');
        
        // IMPORTANT: Fixed route name to match what's used in the controller
        Route::get('/lihat_nota', [TransaksiController::class, 'listNota'])->name('transaksi.listnota');
        
        // Display view for penjualan
        Route::get('/displaypenjualan', function () {
            return view('transaksi.displaypenjualan');
        })->name('transaksi.displaypenjualan');
        
        // Dynamic routes with specific prefixes
        Route::get('/lihatnota/{id}', [TransaksiController::class, 'showNota'])->name('transaksi.lihatnota');
        Route::get('/shownota/{id}', [TransaksiController::class, 'showNota'])->name('transaksi.shownota');
        Route::get('/nota/{id}', [TransaksiController::class, 'showNota'])->name('transaksi.nota');
        Route::get('/edit/{id}', [TransaksiController::class, 'edit'])->name('transaksi.edit');
        Route::post('/update/{id}', [TransaksiController::class, 'update'])->name('transaksi.update');
        Route::post('/cancel/{id}', [TransaksiController::class, 'cancelTransaction'])->name('transaksi.cancel');
        
        // This catch-all route should be LAST
        Route::get('/{id}', [TransaksiController::class, 'getTransaction'])->name('transaksi.get');
    });
    
    // Additional Penjualan routes
    Route::get('/penjualanpercustomer', [TransaksiController::class, 'penjualanPercustomer'])
        ->middleware('permission:manage penjualan')
        ->name('transaksi.penjualancustomer');
    
    Route::get('/panel/{group_id}', [TransaksiController::class, 'getByGroupId'])
        ->middleware('permission:view master data')
        ->name('api.panel.get');
    
    // Pembelian (Purchase) routes
    Route::group(['middleware' => ['permission:manage pembelian'], 'prefix' => 'pembelian'], function () {
        // Main routes
        Route::get('/', [PembelianController::class, 'index'])->name('pembelian.index');
        Route::post('/store', [PembelianController::class, 'store'])->name('pembelian.store'); 
        Route::get('/{id}', [PembelianController::class, 'getPurchase'])->name('pembelian.get');
        
        // Show invoice
        Route::get('/lihatnota/{id}', [PembelianController::class, 'showNota'])->name('pembelian.nota.show');
        Route::get('/nota/{nota}', [PembelianController::class, 'nota'])->name('pembelian.nota');
        Route::get('/lihat/nota', [PembelianController::class, 'listNota'])->name('pembelian.nota.list');
        
        // Edit, update and delete
        Route::get('/edit/{id}', [PembelianController::class, 'edit'])->name('pembelian.edit');
        Route::post('/update/{id}', [PembelianController::class, 'update'])->name('pembelian.update');
        Route::delete('/delete/{id}', [PembelianController::class, 'destroy'])->name('pembelian.delete');
        Route::post('/cancel/{id}', [PembelianController::class, 'cancel'])->name('pembelian.cancel');
        
        // Views
        Route::get('/historypembelian', function () {
            return view('pembelian.historypembelian');
        })->name('pembelian.historypembelian');
    });
    
    // Surat Jalan routes
    Route::group(['middleware' => ['permission:manage surat jalan'], 'prefix' => 'suratjalan'], function () {
        Route::get('/create', [SuratJalanController::class, 'create'])->name('suratjalan.create');
        Route::post('/store', [SuratJalanController::class, 'store'])->name('suratjalan.store');
        Route::get('/history', [SuratJalanController::class, 'history'])->name('suratjalan.history');
        Route::get('/detail/{id}', [SuratJalanController::class, 'detail'])->name('suratjalan.detail');
    });
    
    // Purchase Order routes
    Route::group(['middleware' => ['permission:manage purchase orders'], 'prefix' => 'purchase-order'], function () {
        Route::get('/', [PurchaseOrderController::class, 'index'])->name('transaksi.purchaseorder');
        Route::get('/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-order.show');
        Route::post('/store', [PurchaseOrderController::class, 'store'])->name('purchase-order.store');
        Route::post('/{id}/complete', [PurchaseOrderController::class, 'completeTransaction'])->name('purchase-order.complete');
        Route::patch('/{id}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-order.cancel');
        Route::put('/{id}', [PurchaseOrderController::class, 'update'])->name('purchase-order.update');
    });
    
    // ==============================
    // STOCK MANAGEMENT SECTION
    // ==============================
    
    // Stock Management routes
    Route::group(['middleware' => ['permission:view stock'], 'prefix' => 'stock'], function () {
        Route::get('/mutasi', [StockController::class, 'mutasiStock'])->name('stock.mutasi');
        Route::get('/print-good', [StockController::class, 'printGoodStock'])->name('stock.print.good');
        Route::get('/get', [StockController::class, 'getStock'])->name('stock.get');
        Route::get('/mutations', [StockController::class, 'getStockMutations'])->name('stock.mutations');
    });
    
    // Stock Adjustment routes
    Route::group(['middleware' => ['permission:manage stock adjustment'], 'prefix' => 'stock-adjustment'], function () {
        Route::get('/', [StockAdjustmentController::class, 'index'])->name('stock.adjustment.index');
        Route::get('/history', [StockAdjustmentController::class, 'history'])->name('stock.adjustment.history');
        Route::get('/create', [StockAdjustmentController::class, 'create'])->name('stock.adjustment.create');
        Route::post('/store', [StockAdjustmentController::class, 'store'])->name('stock.adjustment.store');
        Route::get('/adjust/{kodeBarang}', [StockAdjustmentController::class, 'adjust'])->name('stock.adjustment.adjust');
        Route::get('/{id}', [StockAdjustmentController::class, 'show'])->name('stock.adjustment.show');
    });
    
    // ==============================
    // API SECTION
    // ==============================
    
    // API routes for AJAX calls
    Route::prefix('api')->group(function () {
        // General API routes
        Route::get('/cara-bayar/by-metode', function (Illuminate\Http\Request $request) {
            $metode = $request->query('metode');
            return \App\Models\CaraBayar::where('metode', $metode)->get();
        });

        // Customer API routes
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
        Route::get('/customers', [CustomerController::class, 'searchsuratjalan'])->name('api.customers');
        Route::post('/customers/create', [TransaksiController::class, 'createCustomer'])->name('api.customers.create');
        
        // Sales API routes
        Route::get('/sales/search', [StokOwnerController::class, 'search'])->name('api.sales.search');
        Route::get('/stok-owner/search', [StokOwnerController::class, 'search'])->name('api.stok-owner.search');
        
        // Panels API routes
        Route::get('/panels/search', [PanelController::class, 'search'])->name('api.panels.search');
        Route::get('/kode-barang/search', [KodeBarangController::class, 'searchKodeBarang'])->name('kodeBarang.search');
        Route::get('/panels/search-available', [PanelController::class, 'searchAvailablePanels'])->name('panels.searchAvailable');
        Route::get('/panel-by-kode-barang', [PanelController::class, 'getPanelByKodeBarang'])->name('panel.by.kodeBarang');
        
        // Transaksi API routes
        Route::get('/products/search', [TransaksiController::class, 'searchProducts'])->name('api.products.search');
        Route::get('/transaksi', [TransaksiController::class, 'getTransaksi'])->name('api.transaksi');
        Route::get('/searchfaktur', [TransaksiController::class,'getTransaksiByCustomer'])->name('api.faktur.search');
        Route::get('/suratjalan/transaksiitem/{transaksiId}', [TransaksiController::class, 'getRincianTransaksi'])->name('api.rinciantransaksi');
        Route::get('/transaksi/items/{transaksiId}', [TransaksiController::class, 'getTransaksiItems'])->name('api.transaksi.items');
        
        // Supplier API routes
        Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('api.suppliers.search');
        
        // Penjualan API routes
        Route::get('/getpenjualancustomer', [TransaksiController::class, 'getPenjualan']);
    });
});