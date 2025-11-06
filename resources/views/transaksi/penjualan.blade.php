@extends('layout.Nav')

@section('content')
<style>
    /* Custom Select2 styling for better integration */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(1.4em + 0.45rem + 2px) !important;
        padding: 0.3rem 0.45rem !important;
        font-size: 0.85rem !important;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0 !important;
        line-height: 1.4em !important;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: calc(1.4em + 0.45rem) !important;
    }
    
    .select2-dropdown {
        font-size: 0.85rem !important;
    }
    
    .select2-results__option {
        padding: 6px 12px !important;
    }
    
    /* Improved form styling */
    .form-control-sm {
        height: calc(1.3em + 0.5rem + 2px);
        padding: 0.25rem 0.5rem;
        font-size: 0.825rem;
    }
    
    label.small {
        color: #495057;
        margin-bottom: 0.25rem;
    }
    
    .bg-light.border {
        border-color: #dee2e6 !important;
    }
    
    .table-responsive {
        border-radius: 0.25rem;
        box-shadow: 0 0 0.5rem rgba(0,0,0,0.05);
    }
    
    #itemsTable thead th {
        font-size: 0.75rem;
        padding: 0.5rem 0.3rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    #itemsTable tbody td {
        padding: 0.4rem 0.3rem;
        font-size: 0.75rem;
        vertical-align: middle;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .col-6 {
            margin-bottom: 0.5rem;
        }
    }
</style>
<div id="loadingOverlay" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(255,255,255,0.7);backdrop-filter:blur(2px);justify-content:center;align-items:center;">
    <div style="font-size:1.5rem;color:#333;">
        <span class="spinner-border text-primary" role="status"></span>
        <span class="ml-2">Memproses...</span>
    </div>
</div>
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Transaksi Penjualan</h2>
    </div>
    @if(session('warning'))
<script>
    alert("{{ session('warning') }}");
</script>
@endif
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Data Transaksi</h5>
        </div>
        <div class="card-body">
            <form id="transactionForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_transaksi">No. Transaksi</label>
                            <input type="text" class="form-control" id="no_transaksi" value="{{ $noTransaksi ?? '' }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
                        </div>

                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="no_po">Nomor PO</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="no_po" 
                                       name="no_po" 
                                       placeholder="Masukkan Nomor PO">
                            </div>
                            <small class="form-text text-muted">Isi manual nomor PO customer (opsional).</small>
                        </div>

                        <div class="form-group">
                            <label for="customer">Customer</label>
                            <div class="input-group">
                                <input type="text" id="customer" name="customer_display" class="form-control" placeholder="Masukkan nama customer">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addCustomerModal" title="Tambah Customer Baru">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="kode_customer" name="kode_customer">
                            <div id="customerDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                        </div>
                        <div class="form-group">
                            <label for="customer">Alamat Customer</label>
                            <input type="text" id="alamatCustomer" name="customer-alamat" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="customer">No HP / Telp Customer</label>
                            <input type="text" id="hpCustomer" name="customer-hp" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="sales_order">Sales Order (Opsional)</label>
                            <div class="input-group">
                                <input type="text" id="sales_order_search" name="sales_order_display" class="form-control" placeholder="Cari Sales Order...">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="load_sales_order_btn">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="sales_order_id" name="sales_order_id">
                            <div id="salesOrderDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                            <small class="form-text text-muted">Pilih Sales Order untuk mengisi otomatis data transaksi</small>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-group">
                            <label for="sales">Sales (Opsional)</label>
                            <input type="text" id="sales" name="sales_display" class="form-control" placeholder="Masukkan kode atau nama sales (opsional)">
                            <input type="hidden" id="kode_sales" name="sales"> <!-- Hanya kode_sales yang dikirim -->
                            <div id="salesDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                            <small class="form-text text-muted">Boleh dikosongkan</small>
                        </div>

                        <div class="form-group">
                            <label for="mode_input_barang">Mode Input Barang</label>
                            <select class="form-control" id="mode_input_barang">
                                <option value="kecil" selected>Satuan Kecil</option>
                                <option value="besar">Satuan Besar</option>
                            </select>
                            <small class="form-text text-muted">Pilih cara input: satuan kecil atau langsung satuan besar</small>
                        </div>

                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran</label>
                            <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
                                <option value="Tunai" selected>TUNAI</option>
                                <option value="Non Tunai">NON TUNAI</option>
                            </select>
                            <small class="form-text text-muted">Pilih metode pembayaran</small>
                        </div>

                        <div class="form-group">
                            <label for="cara_bayar">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar" name="cara_bayar">
                                <option value="Tunai" selected>Tunai</option>
                            </select>
                        </div>

                        <div class="form-group" id="hariTempoGroup" style="display:none;">
                            <label for="hari_tempo">Hari Tempo</label>
                            <input type="number" class="form-control" id="hari_tempo" name="hari_tempo" min="0" value="0">
                            <small class="form-text text-muted">Isi 0 jika tanpa tempo</small>
                        </div>
                        <div class="form-group" id="jatuhTempoGroup" style="display:none;">
                            <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo</label>
                            <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo">
                        </div>

                        <div class="form-group">
                            <label for="tanggal_jadi">Tanggal Jadi</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal_jadi" name="tanggal_jadi" value="{{ date('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Section (Satuan Kecil) -->
    <div class="card mb-4" id="cardSmallItems">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-box mr-2"></i>Rincian Barang (Satuan Kecil)</h5>
        </div>
        <div class="card-body">
            <!-- Form Tambah Barang -->
            <div class="bg-light p-3 rounded mb-3 border">
                <div id="items-container">
                    <div class="item-row" data-index="0">
                        <!-- Baris 1: Data Utama Barang -->
                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Barang <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm item-barang" id="kode_barang_select">
                                    <option value="">Pilih Barang</option>
                                    @if(isset($kodeBarangs) && $kodeBarangs)
                                        @foreach($kodeBarangs as $barang)
                                            <option value="{{ $barang->id }}" 
                                                data-harga="{{ $barang->harga_jual }}"
                                                data-unit-dasar="{{ $barang->unit_dasar }}"
                                                data-kode="{{ $barang->kode_barang }}"
                                                data-nama="{{ $barang->name }}"
                                                data-merek="{{ $barang->merek }}"
                                                data-ukuran="{{ $barang->ukuran }}">
                                                {{ $barang->kode_barang }} - {{ $barang->name }}@if($barang->merek || $barang->ukuran) ({{ $barang->merek ?? '-' }}@if($barang->merek && $barang->ukuran), @endif{{ $barang->ukuran ?? '-' }})@endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">Tidak ada data barang</option>
                                    @endif
                                </select>
                                <!-- Stock info badge -->
                                <div id="stockInfoSmall" class="mt-1" style="display:none;">
                                    <small class="badge badge-info">
                                        <i class="fas fa-box"></i> Sisa Stok: <span id="stockQtySmall">0</span> <span id="stockUnitSmall"></span>
                                    </small>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm item-qty" id="quantity" step="0.01" min="0.01" placeholder="0">
                            </div>
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Satuan Kecil</label>
                                <select class="form-control form-control-sm item-satuan-kecil" id="satuanKecil">
                                    <option value=""></option>
                                </select>
                                <input type="hidden" class="item-satuan" id="satuan" value="">
                            </div>
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Satuan Besar</label>
                                <select class="form-control form-control-sm item-satuan-besar" id="satuanBesar"></select>
                            </div>
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Harga <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm item-harga" id="harga" step="0.01" min="0" placeholder="0">
                            </div>
                        </div>
                        
                        <!-- Baris 2: Detail Tambahan -->
                        <div class="row align-items-end">
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Total</label>
                                <input type="number" class="form-control form-control-sm item-total" id="item_total" readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Diskon (%)</label>
                                <input type="number" class="form-control form-control-sm" id="diskon" placeholder="0" min="0" max="100">
                            </div>
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Ongkos Kuli</label>
                                <input type="number" class="form-control form-control-sm" id="ongkos_kuli" placeholder="0">
                            </div>
                            <div class="col-lg-5 col-md-9 mb-2">
                                <label class="font-weight-bold small mb-1">Keterangan</label>
                                <input type="text" class="form-control form-control-sm" id="keterangan" placeholder="Keterangan tambahan (opsional)">
                            </div>
                            <div class="col-lg-1 col-md-3 col-12 mb-2">
                                <button type="button" class="btn btn-success btn-block btn-sm" id="addItemBtn">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Section (Satuan Besar) -->
    <div class="card mb-4" id="cardLargeItems" style="display:none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-box mr-2"></i>Rincian Barang (Satuan Besar)</h5>
        </div>
        <div class="card-body">
            <!-- Form Tambah Barang Satuan Besar -->
            <div class="bg-light p-3 rounded mb-3 border">
                <div id="items-container-large">
                    <div class="item-row-large" data-index="0">
                        <!-- Baris 1: Data Utama Barang -->
                        <div class="row mb-2">
                            <div class="col-lg-5 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Barang <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm item-barang-large" id="kode_barang_select_large">
                                    <option value="">Pilih Barang</option>
                                    @if(isset($kodeBarangs) && $kodeBarangs)
                                        @foreach($kodeBarangs as $barang)
                                            <option value="{{ $barang->id }}" 
                                                data-harga="{{ $barang->harga_jual }}"
                                                data-unit-dasar="{{ $barang->unit_dasar }}"
                                                data-kode="{{ $barang->kode_barang }}"
                                                data-nama="{{ $barang->name }}"
                                                data-merek="{{ $barang->merek }}"
                                                data-ukuran="{{ $barang->ukuran }}">
                                                {{ $barang->kode_barang }} - {{ $barang->name }}@if($barang->merek || $barang->ukuran) ({{ $barang->merek ?? '-' }}@if($barang->merek && $barang->ukuran), @endif{{ $barang->ukuran ?? '-' }})@endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">Tidak ada data barang</option>
                                    @endif
                                </select>
                                <!-- Stock info badge -->
                                <div id="stockInfoLarge" class="mt-1" style="display:none;">
                                    <small class="badge badge-info">
                                        <i class="fas fa-box"></i> Sisa Stok: <span id="stockQtyLarge">0</span> <span id="stockUnitLarge"></span>
                                    </small>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm item-qty-large" id="quantity_large" step="0.01" min="0.01" placeholder="0">
                            </div>
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Satuan Besar <span class="text-danger">*</span></label>
                                <select class="form-control form-control-sm item-satuan-besar-large" id="satuanBesarLarge">
                                    <option value="">Pilih satuan besar</option>
                                </select>
                                <input type="hidden" class="item-satuan-large" id="satuan_large" value="">
                            </div>
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Harga <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm item-harga-large" id="harga_large" step="0.01" min="0" placeholder="0">
                            </div>
                        </div>
                        
                        <!-- Baris 2: Detail Tambahan -->
                        <div class="row align-items-end">
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Total</label>
                                <input type="number" class="form-control form-control-sm item-total-large" id="item_total_large" readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Diskon (%)</label>
                                <input type="number" class="form-control form-control-sm" id="diskon_large" placeholder="0" min="0" max="100">
                            </div>
                            <div class="col-lg-2 col-md-3 col-6 mb-2">
                                <label class="font-weight-bold small mb-1">Ongkos Kuli</label>
                                <input type="number" class="form-control form-control-sm" id="ongkos_kuli_large" placeholder="0">
                            </div>
                            <div class="col-lg-5 col-md-9 mb-2">
                                <label class="font-weight-bold small mb-1">Keterangan</label>
                                <input type="text" class="form-control form-control-sm" id="keterangan_large" placeholder="Keterangan tambahan (opsional)">
                            </div>
                            <div class="col-lg-1 col-md-3 col-12 mb-2">
                                <button type="button" class="btn btn-success btn-block btn-sm" id="addItemBtnLarge">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Barang (Shared Table for Both Modes) -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Daftar Barang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Merek</th>
                            <th>Ukuran/Type</th>
                            <th>Keterangan</th>
                            <th>Harga Jual</th>
                            <th>Qty & Satuan</th>
                            <th>Satuan Besar</th>
                            <th>Total</th>
                            <th>Ongkos Kuli</th>
                            <th>Diskon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsList">
                        <!-- Dynamic items will be added here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i>Ringkasan Pembayaran</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Kolom Kiri: Perhitungan -->
                <div class="col-lg-6 mb-3">
                    <div class="bg-light p-3 rounded border">
                        <h6 class="font-weight-bold mb-3 text-primary"><i class="fas fa-coins mr-2"></i>Perhitungan</h6>
                        
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold mb-1">Subtotal</label>
                            <input type="text" class="form-control text-right font-weight-bold" id="total" name="total" readonly value="0" style="background-color: #fff; font-size: 1.1rem;">
                        </div>
                        
                        <div class="form-group mb-2">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <input type="checkbox" id="discount_checkbox">
                                    </div>
                                </div>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Disc (%)</span>
                                </div>
                                <input type="number" class="form-control" id="discount_percent" value="0" disabled placeholder="0">
                                <input type="text" class="form-control text-right" id="discount_amount" value="0" readonly style="background-color: #e9ecef;">
                            </div>
                        </div>
                        
                        <div class="form-group mb-2">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <input type="checkbox" id="disc_rp_checkbox">
                                    </div>
                                </div>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Disc (Rp)</span>
                                </div>
                                <input type="number" class="form-control" id="disc_rp" value="0" disabled placeholder="0">
                            </div>
                        </div>
                        
                        <div class="form-group mb-2">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <input type="checkbox" id="ppn_checkbox">
                                    </div>
                                </div>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">PPN</span>
                                </div>
                                <input type="text" class="form-control text-right" id="ppn_amount" value="0" readonly style="background-color: #e9ecef;">
                            </div>
                        </div>
                        
                        <div class="form-group mb-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <input type="checkbox" id="dp_checkbox">
                                    </div>
                                </div>
                                <div class="input-group-prepend">
                                    <span class="input-group-text">DP</span>
                                </div>
                                <input type="number" class="form-control" id="dp_amount" value="0" disabled placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Kolom Kanan: Total & Aksi -->
                <div class="col-lg-6 mb-3">
                    <div class="bg-light p-3 rounded border h-100">
                        <h6 class="font-weight-bold mb-3 text-success"><i class="fas fa-money-bill-wave mr-2"></i>Total Pembayaran</h6>
                        
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar_akhir" disabled>
                                <option value="">-- Belum Dipilih --</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold mb-2">Grand Total</label>
                            <div class="alert alert-success p-2 mb-0" style="background-color: #d4edda; border: 2px solid #28a745;">
                                <input type="text" class="form-control text-center font-weight-bold text-success border-0" id="grand_total" readonly value="0" style="font-size: 1.8rem; background-color: transparent;">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="small font-weight-bold mb-1">Catatan</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Masukkan catatan tambahan (opsional)"></textarea>
                        </div>
                        
                        <div class="mt-4">
                            <button type="button" class="btn btn-success btn-block btn-lg mb-2" id="saveTransaction">
                                <i class="fas fa-save mr-2"></i>Simpan Transaksi
                            </button>
                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="btn btn-warning btn-block" id="buatPOBtn">
                                        <i class="fas fa-file-alt mr-1"></i> Buat PO
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-secondary btn-block" id="cancelTransaction">
                                        <i class="fas fa-times mr-1"></i> Batal
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simplified Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border: 3px solid black;">
            <form id="addCustomerForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Customer Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Kode customer akan dibuat otomatis
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="hp">HP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="hp" name="hp" required>
                    </div>
                    <div class="form-group">
                        <label for="telepon">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>





<!-- Modal Invoice -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="invoiceContent">
                    <h4>No Transaksi: <span id="invoiceNoTransaksi"></span></h4>
                    <p>Tanggal: <span id="invoiceTanggal"></span></p>
                    <p>Customer: <span id="invoiceCustomer"></span></p>
                    <p>Grand Total: <span id="invoiceGrandTotal"></span></p>
                    </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" id="printInvoiceBtn">
                        <i class="fas fa-print"></i> Print Standard
                    </button>
                    <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" id="printNotaKecilBtn">
                            <i class="fas fa-file-alt"></i> Nota Kecil
                        </a>
                        <a class="dropdown-item" href="#" id="printNotaBesarBtn">
                            <i class="fas fa-file-alt"></i> Nota Besar
                        </a>
                        <a class="dropdown-item" href="#" id="printNotaSementaraBtn">
                            <i class="fas fa-exclamation-triangle"></i> Nota Sementara
                        </a>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="backToFormBtn">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
    function showLoading() {
        $('#loadingOverlay').fadeIn(100);
    }
    function hideLoading() {
        $('#loadingOverlay').fadeOut(100);
    }
    $(document).ready(function() {
        // Toggle between small/large item forms by mode input barang
        $('#mode_input_barang').on('change', function() {
            const mode = $(this).val();
            if (mode === 'besar') {
                $('#cardSmallItems').hide();
                $('#cardLargeItems').show();
            } else {
                $('#cardLargeItems').hide();
                $('#cardSmallItems').show();
            }
        });
        
        // Initialize Select2 for item dropdown (small)
        $('#kode_barang_select').select2({
            theme: 'bootstrap-5',
            placeholder: 'Pilih atau cari barang...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Barang tidak ditemukan";
                },
                searching: function() {
                    return "Mencari...";
                }
            }
        });
        
        // Initialize Select2 for item dropdown (large)
        $('#kode_barang_select_large').select2({
            theme: 'bootstrap-5',
            placeholder: 'Pilih atau cari barang...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Barang tidak ditemukan";
                },
                searching: function() {
                    return "Mencari...";
                }
            }
        });

        // Initialize variables
        let items = [];
        let grandTotal = 0;
        // Auto-load from Surat Jalan if URL has ?no_suratjalan=...
        const urlParams = new URLSearchParams(window.location.search);
        const noSj = urlParams.get('no_suratjalan');
        const isFromSuratJalan = !!noSj;
        let suratJalanId = null;
        if (noSj) {
            $.get(`{{ route('suratjalan.api.by-no', '') }}/${encodeURIComponent(noSj)}`)
                .done(function(res){
                    suratJalanId = res.id || null;
                    if(res.customer){
                        $('#kode_customer').val(res.customer.kode_customer);
                        $('#customer').val(`${res.customer.kode_customer} - ${res.customer.nama || ''}`);
                        $('#alamatCustomer').val(res.customer.alamat || '');
                        $('#hpCustomer').val(`${res.customer.hp || ''} / ${res.customer.telepon || ''}`);
                    }
                    // Fill items
                    items = [];
                    (res.items||[]).forEach(function(it){
                        const total = (parseFloat(it.harga_jual_default||0) * parseFloat(it.qty||0));
                        items.push({
                            surat_jalan_item_id: it.surat_jalan_item_id || null,
                            kodeBarang: it.kode_barang,
                            namaBarang: it.nama_barang,
                            keterangan: '',
                            harga: parseFloat(it.harga_jual_default||0),
                            qty: parseFloat(it.qty||0),
                            satuan: it.satuan || it.unit_dasar || 'PCS',
                            satuanBesar: '',
                            diskon: 0,
                            ongkosKuli: 0,
                            total: total
                        });
                    });
                    renderItems();
                    calculateTotals();
                })
                .fail(function(){
                    console.warn('Gagal load Surat Jalan');
                });
        }

        // Auto-fill form jika ada data Sales Order
        @if(isset($salesOrder) && $salesOrder)
            // Set sales order data
            $('#sales_order_id').val('{{ $salesOrder->id }}');
            $('#sales_order_search').val('{{ $salesOrder->no_so }} - {{ $salesOrder->customer->nama }}');
            
            // Set customer data
            $('#kode_customer').val('{{ $salesOrder->customer->kode_customer }}');
            $('#customer').val('{{ $salesOrder->customer->kode_customer }} - {{ $salesOrder->customer->nama }}');
            $('#alamatCustomer').val('{{ $salesOrder->customer->alamat }}');
            $('#hpCustomer').val('{{ $salesOrder->customer->hp ?? "" }} / {{ $salesOrder->customer->telepon ?? "" }}');
            
            // Set salesman data
            $('#sales').val('{{ $salesOrder->salesman->kode_stok_owner }}');
            $('#kode_sales').val('{{ $salesOrder->salesman->kode_stok_owner }}');
            
            // Set payment method to kredit/tempo
            if (!isFromSuratJalan) {
                $('#metode_pembayaran').val('Non Tunai').trigger('change');
            }
            $('#hariTempoGroup').show();
            $('#jatuhTempoGroup').show();
            // Fill tempo fields from Sales Order if available
            @if(!is_null($salesOrder->hari_tempo))
                $('#hari_tempo').val('{{ $salesOrder->hari_tempo }}');
            @endif
            @if(!is_null($salesOrder->tanggal_jatuh_tempo))
                $('#tanggal_jatuh_tempo').val('{{ optional($salesOrder->tanggal_jatuh_tempo)->format('Y-m-d') }}');
            @endif
            
            // Set cara bayar after a delay to ensure dropdown is loaded
            setTimeout(() => {
                const caraBayar = '{{ $salesOrder->cara_bayar }}';
                if (caraBayar) {
                    $('#cara_bayar').val(caraBayar).trigger('change');
                    // Also update cara_bayar_akhir explicitly
                    $('#cara_bayar_akhir').html(`<option value="${caraBayar}">${caraBayar}</option>`).val(caraBayar);
                }
            }, 500);
            
            // Set tanggal jadi if available
            @if($salesOrder->tanggal_estimasi)
                $('#tanggal_jadi').val('{{ $salesOrder->tanggal_estimasi }}');
            @endif
            
            // Load sales order items
            loadSalesOrderItems({{ $salesOrder->id }});
        @endif

        // Auto-fill form jika ada data Surat Jalan
        @if(isset($suratJalan) && $suratJalan)
            // Set customer data
            $('#kode_customer').val('{{ $suratJalan->customer->kode_customer }}');
            $('#customer').val('{{ $suratJalan->customer->kode_customer }} - {{ $suratJalan->customer->nama }}');
            $('#alamatCustomer').val('{{ $suratJalan->customer->alamat }}');
            $('#hpCustomer').val('{{ $suratJalan->customer->hp ?? "" }} / {{ $suratJalan->customer->telepon ?? "" }}');
            
            // Set PO number from surat jalan
            @if($suratJalan->no_po)
                $('#no_po').val('{{ $suratJalan->no_po }}');
                console.log('Set no_po from surat jalan (PHP): {{ $suratJalan->no_po }}');
            @endif
            
            // Show tempo groups
            $('#hariTempoGroup').show();
            $('#jatuhTempoGroup').show();
            
            // Load surat jalan items (this will also set payment and tempo fields)
            loadSuratJalanItems('{{ $suratJalan->no_suratjalan }}');
        @endif

        // Metode Pembayaran
        // Set default payment method (only if not coming from surat jalan or sales order)
        @if(!isset($suratJalan) || !$suratJalan)
            @if(!isset($salesOrder) || !$salesOrder)
                if (!isFromSuratJalan) {
                    $('#metode_pembayaran').val('Tunai').trigger('change');
                }
            @endif
        @endif
        function recalcJatuhTempoPenjualan(){
            const base = $('#tanggal').val();
            const hari = parseInt($('#hari_tempo').val()||'0',10);
            if(!base || isNaN(hari)) return;
            const d = new Date(base);
            d.setDate(d.getDate()+hari);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth()+1).padStart(2,'0');
            const dd = String(d.getDate()).padStart(2,'0');
            $('#tanggal_jatuh_tempo').val(`${yyyy}-${mm}-${dd}`);
        }
        function toggleTempoVisibility(metodeLabel, caraLabel){
            const metode = (metodeLabel||'').toString().trim().toLowerCase();
            const cara = (caraLabel||'').toString().trim().toLowerCase();
            const isCash = (metode === 'tunai') || (cara === 'tunai') || (cara === 'cash') || (cara === 'kontan');
            if (isCash){
                $('#hariTempoGroup').hide();
                $('#jatuhTempoGroup').hide();
                $('#hari_tempo').val(0);
                $('#tanggal_jatuh_tempo').val('');
            } else {
                $('#hariTempoGroup').show();
                $('#jatuhTempoGroup').show();
            }
        }
        $('#tanggal').on('change', recalcJatuhTempoPenjualan);
        $('#hari_tempo').on('input', recalcJatuhTempoPenjualan);

        $('#cara_bayar').on('change', function () {
            const selected = $(this).val();
            $('#cara_bayar_akhir')
                .html(`<option value="${selected}">${selected}</option>`)
                .val(selected)
                .trigger('change'); // Trigger change event untuk memastikan update
            toggleTempoVisibility($('#metode_pembayaran').val(), selected);
        });
        $('#metode_pembayaran').on('change', function(){
            const metode = $(this).val();
            
            // Fetch cara bayar options based on selected metode
            $.ajax({
                url: '/api/cara-bayar/by-metode',
                method: 'GET',
                data: { metode: metode },
                success: function(data) {
                    const caraBayarSelect = $('#cara_bayar');
                    caraBayarSelect.empty();
                    
                    if (data && data.length > 0) {
                        // Populate dropdown with payment methods from database
                        data.forEach(function(item) {
                            caraBayarSelect.append(`<option value="${item.nama}">${item.nama}</option>`);
                        });
                        
                        // Select first option and trigger change
                        caraBayarSelect.val(data[0].nama).trigger('change');
                    } else {
                        // Fallback if no data found
                        if (metode === 'Tunai') {
                            caraBayarSelect.html('<option value="Tunai">Tunai</option>');
                        } else {
                            caraBayarSelect.html('<option value="Kredit">Kredit</option>');
                        }
                        caraBayarSelect.trigger('change');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching cara bayar:', xhr);
                    // Fallback on error
                    const caraBayarSelect = $('#cara_bayar');
                    if (metode === 'Tunai') {
                        caraBayarSelect.html('<option value="Tunai">Tunai</option>');
                    } else {
                        caraBayarSelect.html('<option value="Kredit">Kredit</option>');
                    }
                    caraBayarSelect.trigger('change');
                }
            });
            
            toggleTempoVisibility(metode, $('#cara_bayar').val());
        });

        // Search customers (show name only in dropdown) with debounce
        let customerSearchTimeout;
        $('#customer').on('input', function () {
            const keyword = $(this).val();
            
            // Clear previous timeout
            clearTimeout(customerSearchTimeout);
            
            if (keyword.length > 0) {
                // Add debounce to prevent too many requests
                customerSearchTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('api.customers.search') }}",
                        method: "GET",
                        data: { keyword },
                        success: function (data) {
                            let dropdown = '';
                            if (data.length > 0) {
                                data.forEach(customer => {
                                    dropdown += `<a class="dropdown-item customer-item" 
                                        data-kode="${customer.kode_customer}" 
                                        data-name="${customer.nama}"
                                        data-alamat="${customer.alamat}"
                                        data-hp="${customer.hp}"
                                        data-telp="${customer.telepon}"
                                        data-limit-kredit="${customer.limit_kredit || 0}"
                                        data-hari-tempo="${customer.limit_hari_tempo || 0}">
                                    ${customer.kode_customer} - ${customer.nama} - ${customer.alamat} - ${customer.hp}</a>`;
                                });
                            } else {
                                dropdown = '<a class="dropdown-item disabled">Tidak ada customer ditemukan</a>';
                            }
                            $('#customerDropdown').html(dropdown).show();
                        },
                        error: function () {
                            console.error('Error searching customer');
                        }
                    });
                }, 300); // 300ms debounce
            } else {
                $('#customerDropdown').hide();
            }
        });

        // Select Customer
        $(document).on('click', '.customer-item', function () {
            const kodeCustomer = $(this).data('kode');
            const namaCustomer = $(this).data('name');
            const alamatCustomer = $(this).data('alamat');
            const hpCustomer = $(this).data('hp');
            const telpCustomer = $(this).data('telp');
            const limitKredit = parseFloat($(this).data('limit-kredit')) || 0;
            const hariTempo = parseInt($(this).data('hari-tempo') || '0', 10);
            $('#kode_customer').val(kodeCustomer); // Isi input hidden dengan kode customer
            $('#customer').val(`${kodeCustomer} - ${namaCustomer}`); // Tampilkan kode dan nama customer di input utama
            $('#alamatCustomer').val(alamatCustomer);
            $('#hpCustomer').val(`${hpCustomer} / ${telpCustomer}`);
            $('#customerDropdown').hide();

            // Auto apply credit terms when adding manual invoice
            if (limitKredit > 0) {
                if (!isFromSuratJalan) {
                    $('#metode_pembayaran').val('Non Tunai').trigger('change');
                }
                $('#hariTempoGroup').show();
                $('#jatuhTempoGroup').show();
                $('#hari_tempo').val(hariTempo);
                // recalc jatuh tempo
                const base = $('#tanggal').val();
                if (base) {
                    const d = new Date(base);
                    d.setDate(d.getDate()+hariTempo);
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth()+1).padStart(2,'0');
                    const dd = String(d.getDate()).padStart(2,'0');
                    $('#tanggal_jatuh_tempo').val(`${yyyy}-${mm}-${dd}`);
                }
            } else {
                // If no credit limit, default to Tunai-style UI
                $('#metode_pembayaran').val('Tunai').trigger('change');
                $('#hariTempoGroup').hide();
                $('#jatuhTempoGroup').hide();
                $('#hari_tempo').val(0);
                $('#tanggal_jatuh_tempo').val('');
            }
        });

        // Search Sales Order
        $('#sales_order_search').on('input', function () {
            const keyword = $(this).val();
            if (keyword.length > 0) {
                $.ajax({
                    url: "{{ route('api.sales-order.search') }}",
                    method: "GET",
                    data: { keyword },
                    success: function (data) {
                        let dropdown = '';
                        if (data.length > 0) {
                            data.forEach(so => {
                                dropdown += `<a class="dropdown-item sales-order-item" 
                                    data-id="${so.id}" 
                                    data-no-so="${so.no_so}"
                                    data-customer="${so.customer?.nama || ''}"
                                    data-salesman="${so.salesman?.keterangan || ''}"
                                    data-tanggal="${so.tanggal}"
                                    data-cara-bayar="${so.cara_bayar}"
                                    data-hari-tempo="${so.hari_tempo || ''}"
                                    data-tanggal-jatuh-tempo="${so.tanggal_jatuh_tempo || ''}"
                                    data-tanggal-estimasi="${so.tanggal_estimasi || ''}"
                                    data-subtotal="${so.subtotal}"
                                    data-grand-total="${so.grand_total}">
                                ${so.no_so} - ${so.customer?.nama || ''} - ${so.tanggal} - ${so.grand_total}</a>`;
                            });
                        } else {
                            dropdown = '<a class="dropdown-item disabled">Tidak ada Sales Order ditemukan</a>';
                        }
                        $('#salesOrderDropdown').html(dropdown).show();
                    },
                    error: function () {
                        alert('Terjadi kesalahan saat mencari Sales Order.');
                    }
                });
            } else {
                $('#salesOrderDropdown').hide();
            }
        });

        // Select Sales Order
        $(document).on('click', '.sales-order-item', function () {
            const soId = $(this).data('id');
            const noSo = $(this).data('no-so');
            const customer = $(this).data('customer');
            const salesman = $(this).data('salesman');
            const tanggal = $(this).data('tanggal');
            const caraBayar = $(this).data('cara-bayar');
            const hariTempo = $(this).data('hari-tempo');
            const tanggalJatuhTempo = $(this).data('tanggal-jatuh-tempo');
            const tanggalEstimasi = $(this).data('tanggal-estimasi');
            const subtotal = $(this).data('subtotal');
            const grandTotal = $(this).data('grand-total');

            // Set sales order ID
            $('#sales_order_id').val(soId);
            $('#sales_order_search').val(`${noSo} - ${customer}`);

            // Auto-fill customer if not already set
            if (!$('#kode_customer').val()) {
                // Trigger customer search and selection
                $('#customer').val(customer).trigger('input');
                // Note: You might need to adjust this based on your customer data structure
            }

            // Auto-fill salesman
            if (salesman) {
                $('#sales').val(salesman).trigger('input');
            }

            // Force kredit/tempo and fill tempo values
            if (!isFromSuratJalan) {
                $('#metode_pembayaran').val('Non Tunai').trigger('change');
            }
            $('#hariTempoGroup').show();
            $('#jatuhTempoGroup').show();
            if (hariTempo !== undefined && hariTempo !== '') {
                $('#hari_tempo').val(hariTempo);
            }
            if (tanggalJatuhTempo) {
                $('#tanggal_jatuh_tempo').val(tanggalJatuhTempo);
            }

            // Auto-fill tanggal jadi if available
            if (tanggalEstimasi) {
                $('#tanggal_jadi').val(tanggalEstimasi);
            }

            // Load sales order items
            loadSalesOrderItems(soId);

            $('#salesOrderDropdown').hide();
        });

        // Load Sales Order Items
        function loadSalesOrderItems(soId) {
            $.ajax({
                url: `{{ url('api/sales-order') }}/${soId}/items`,
                method: "GET",
                success: function (data) {
                    // Debug logging
                    console.log('Sales Order Items Data:', data);
                    
                    // Clear existing items
                    items = [];
                    
                    // Add items from sales order
                    data.forEach(item => {
                        console.log('Processing item:', item);
                        console.log('KodeBarang relation:', item.kode_barang);
                        
                        // Determine satuan kecil and satuan besar based on Sales Order data
                        const unitDasar = item.kode_barang?.unit_dasar || 'LBR';
                        const satuanSO = item.satuan;
                        
                        // Satuan kecil selalu unit dasar (LBR)
                        // Satuan besar adalah unit turunan (JEGG) jika ada
                        const newItem = {
                            kodeBarang: item.kode_barang?.kode_barang || item.kode_barang_id || '',
                            namaBarang: item.kode_barang?.name || item.nama_barang || '',
                            keterangan: item.keterangan || '',
                            harga: parseFloat(item.harga),
                            qty: parseFloat(item.qty),
                            satuan: unitDasar, // Satuan kecil selalu unit dasar
                            satuanBesar: satuanSO !== unitDasar ? satuanSO : '', // Satuan besar jika berbeda dari unit dasar
                            total: parseFloat(item.total),
                            diskon: 0,
                            ongkosKuli: 0
                        };
                        
                        console.log('New item created:', newItem);
                        items.push(newItem);
                    });
                    
                    // Render items and calculate totals
                    renderItems();
                    calculateTotals();
                    
                    // Auto-select satuan in form if there's only one item
                    if (data.length === 1) {
                        const item = data[0];
                        const unitDasar = item.kode_barang?.unit_dasar || 'LBR';
                        const satuanSO = item.satuan;
                        
                        // Set satuan kecil (always unit dasar)
                        const satuanKecilSelect = $('.item-satuan-kecil');
                        satuanKecilSelect.val(unitDasar);
                        $('.item-satuan').val(unitDasar);
                        
                        // Set satuan besar (only if different from unit dasar)
                        const satuanBesarSelect = $('.item-satuan-besar');
                        if (satuanSO !== unitDasar) {
                            satuanBesarSelect.val(satuanSO);
                        } else {
                            satuanBesarSelect.val(''); // Clear if same as unit dasar
                        }
                        
                        console.log('Auto-selected units:', {
                            unitDasar: unitDasar,
                            satuanSO: satuanSO,
                            satuanKecil: unitDasar,
                            satuanBesar: satuanSO !== unitDasar ? satuanSO : ''
                        });
                    }
                    
                    // Show success message
                    alert('Sales Order berhasil dimuat! Data transaksi telah diisi otomatis.');
                },
                error: function (xhr) {
                    console.error('Error loading sales order items:', xhr.responseText);
                    alert('Gagal memuat item Sales Order.');
                }
            });
        }

        // Load Surat Jalan Items
        function loadSuratJalanItems(noSuratJalan) {
            $.ajax({
                url: `{{ url('suratjalan/api/by-no') }}/${noSuratJalan}`,
                method: "GET",
                success: function (data) {
                    console.log('Surat Jalan Items Data:', data);
                    
                    // Update payment and tempo fields from surat jalan data
                    // Set pembayaran & cara bayar from SJ, robust mapping (case-insensitive, synonyms)
                    const metodeFromSjRaw = (data.metode_pembayaran || '').toString().trim().toLowerCase();
                    const caraFromSjRaw = (data.cara_bayar || '').toString().trim();

                    // Normalize metode: map various forms to the exact option labels used in the select
                    let metodeNormalized = 'Tunai';
                    if (['non tunai', 'non-tunai', 'kredit', 'credit'].includes(metodeFromSjRaw)) {
                        metodeNormalized = 'Non Tunai';
                    } else if (['tunai', 'cash', 'kontan'].includes(metodeFromSjRaw)) {
                        metodeNormalized = 'Tunai';
                    } else if (metodeFromSjRaw) {
                        // Fallback: if unknown but present, prefer Tunai unless it looks like non tunai
                        metodeNormalized = metodeFromSjRaw.includes('non') ? 'Non Tunai' : 'Tunai';
                    }
                    $('#metode_pembayaran').val(metodeNormalized).trigger('change');

            // Cara bayar: preserve original label (e.g., CASH) when present; otherwise derive from metode
            const caraLabel = caraFromSjRaw || (metodeNormalized === 'Tunai' ? 'Tunai' : 'Kredit');
            
            // Wait for metode_pembayaran change to populate dropdown, then set specific cara_bayar
            setTimeout(() => {
                // Check if the caraLabel exists in dropdown, if not add it
                if ($('#cara_bayar option[value="' + caraLabel + '"]').length === 0) {
                    $('#cara_bayar').append(`<option value="${caraLabel}">${caraLabel}</option>`);
                }
                $('#cara_bayar').val(caraLabel).trigger('change');
                // Also update cara_bayar_akhir explicitly
                $('#cara_bayar_akhir').html(`<option value="${caraLabel}">${caraLabel}</option>`).val(caraLabel);
            }, 300);
                    if (data.no_po) {
                        $('#no_po').val(data.no_po);
                        console.log('Set no_po from surat jalan:', data.no_po);
                    }
                    if (data.hari_tempo !== null && data.hari_tempo !== undefined) {
                        $('#hari_tempo').val(data.hari_tempo);
                        console.log('Set hari tempo from API:', data.hari_tempo);
                    }
                    if (data.tanggal_jatuh_tempo) {
                        $('#tanggal_jatuh_tempo').val(data.tanggal_jatuh_tempo);
                        console.log('Set tanggal jatuh tempo from API:', data.tanggal_jatuh_tempo);
                    }
                    
                    // Recalculate jatuh tempo after setting values
                    setTimeout(() => {
                        recalcJatuhTempoPenjualan();
                    }, 100);
                    
                    // Clear existing items
                    items = [];
                    
                    // Add items from surat jalan
                    data.items.forEach(item => {
                        console.log('Processing surat jalan item:', item);
                        
                        // Determine satuan kecil and satuan besar
                        const unitDasar = item.unit_dasar || 'PCS';
                        const satuanSJ = item.satuan; // Satuan kecil dari surat jalan
                        const satuanBesarSJ = item.satuan_besar || ''; // Satuan besar dari surat jalan
                        
                        console.log('Unit mapping:', {
                            unitDasar: unitDasar,
                            satuanSJ: satuanSJ,
                            satuanBesarSJ: satuanBesarSJ,
                            isDifferent: satuanSJ !== unitDasar
                        });
                        
                        // Satuan kecil selalu unit dasar
                        // Satuan besar dari surat jalan
                const unitPrice = parseFloat(item.harga_jual_default||0);
                const lineTotal = (parseFloat(item.qty||0) * unitPrice);
                const newItem = {
                            kodeBarang: item.kode_barang,
                            namaBarang: item.nama_barang,
                            // keterangan: item.nama_barang,
                            merek: item.merek || '',
                            ukuran: item.ukuran || '',
                    harga: unitPrice,
                            qty: item.qty,  
                            satuan: satuanSJ || unitDasar, // Gunakan satuan SJ agar sesuai
                            satuanBesar: satuanBesarSJ, // Satuan besar dari surat jalan
                    total: lineTotal,
                            diskon: 0,
                            ongkosKuli: 0
                        };
                        
                        console.log('New item created:', newItem);
                        items.push(newItem);
                    });
                    
                    // Re-assert payment fields after item rendering in case other code modified them
                    setTimeout(() => {
                        if ($('#cara_bayar option[value="' + caraLabel + '"]').length === 0) {
                            $('#cara_bayar').append(`<option value="${caraLabel}">${caraLabel}</option>`);
                        }
                        $('#cara_bayar').val(caraLabel).trigger('change');
                        toggleTempoVisibility(metodeNormalized, caraLabel);
                    }, 400);

                    // Update items table
                    renderItems();
                    calculateTotals();
                    
                    // Show success message
                    alert('Surat Jalan berhasil dimuat! Data transaksi telah diisi otomatis.');
                },
                error: function (xhr) {
                    console.error('Error loading surat jalan items:', xhr.responseText);
                    alert('Gagal memuat item Surat Jalan.');
                }
            });
        }

        // Search Sales with debounce
        let salesSearchTimeout;
        $('#sales').on('input', function () {
            const keyword = $(this).val();
            
            // Clear previous timeout
            clearTimeout(salesSearchTimeout);
            
            if (keyword.length > 0) {
                // Add debounce to prevent too many requests
                salesSearchTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('api.sales.search') }}",
                        method: "GET",
                        data: { keyword },
                        success: function (data) {
                            let dropdown = '';
                            if (data.length > 0) {
                                data.forEach(sales => {
                                    dropdown += `<a class="dropdown-item sales-item" data-kode="${sales.kode_stok_owner}" data-name="${sales.keterangan}">${sales.kode_stok_owner} - ${sales.keterangan}</a>`;
                                });
                            } else {
                                dropdown = '<a class="dropdown-item disabled">Tidak ada sales ditemukan</a>';
                            }
                            $('#salesDropdown').html(dropdown).show();
                        },
                        error: function () {
                            console.error('Error searching sales');
                        }
                    });
                }, 300); // 300ms debounce
            } else {
                $('#salesDropdown').hide();
            }
        });

        // Select Sales
        $(document).on('click', '.sales-item', function () {
            const kodeSales = $(this).data('kode'); // Ambil kode sales
            const namaSales = $(this).data('name'); // Ambil nama sales
            $('#kode_sales').val(kodeSales); // Isi input hidden dengan kode sales
            $('#sales').val(`${kodeSales}`); // Tampilkan kode dan nama sales di input utama
            $('#salesDropdown').hide();
        });

        // ===== INTEGRASI SISTEM FAKTUR FIFO =====
        
        // Auto-populate harga dan ongkos kuli saat barang/satuan kecil dipilih
        $(document).on('change', '#kode_barang, #satuanKecil', function() {
            const kodeBarang = $('#kode_barang').val();
            const satuan = $('#satuanKecil').val();
            const customerId = $('#kode_customer').val();
            
            if (kodeBarang && satuan && customerId) {
                getHargaDanOngkos(kodeBarang, satuan, customerId);
            }
        });

        // Get harga dan ongkos kuli via AJAX
        function getHargaDanOngkos(kodeBarang, satuan, customerId) {
            // Cari kode_barang_id dari kode_barang
            $.ajax({
                url: "{{ route('kodeBarang.search') }}",
                method: "GET",
                data: { keyword: kodeBarang },
                success: function(data) {
                    if (data.length > 0) {
                        const kodeBarangData = data[0];
                        const kodeBarangId = kodeBarangData.id;
                        
                        // Sekarang panggil API getHargaDanOngkos
                        $.ajax({
                            url: "{{ route('api.transaksi.harga-ongkos') }}",
                            method: "GET",
                            data: {
                                customer_id: customerId,
                                kode_barang_id: kodeBarangId,
                                satuan: satuan
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Auto-populate harga dan ongkos kuli
                                    $('#harga').val(response.harga_jual);
                                    $('#ongkos_kuli').val(response.ongkos_kuli);
                                    
                                    // Update preview jika ada
                                    updateItemPreview();
                                }
                            },
                            error: function(xhr) {
                                console.log('Error getting harga dan ongkos kuli:', xhr.responseText);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    console.log('Error searching kode barang:', xhr.responseText);
                }
            });
        }

        // Manual get ongkos kuli button
        $(document).on('click', '#getOngkosKuliBtn', function() {
            const kodeBarang = $('#kode_barang').val();
            const satuan = $('#satuanKecil').val();
            const customerId = $('#kode_customer').val();
            
            if (!kodeBarang || !satuan || !customerId) {
                alert('Pilih customer, kode barang, dan satuan terlebih dahulu!');
                return;
            }
            
            getHargaDanOngkos(kodeBarang, satuan, customerId);
        });

        // Update item preview
        function updateItemPreview() {
            const harga = parseInt($('#harga').val()) || 0;
            const qty = parseInt($('#quantity').val()) || 0;
            const diskon = parseInt($('#diskon').val()) || 0;
            const ongkosKuli = parseInt($('#ongkos_kuli').val()) || 0;
            
            const subtotal = harga * qty;
            const diskonAmount = (subtotal * diskon) / 100;
            const total = subtotal - diskonAmount;
            
            // Update preview table
            $('#itemPreview').html(`
                <tr>
                    <td>${$('#kode_barang').val() || '-'}</td>
                    <td>${$('#nama_barang').val() || '-'}</td>
                    <td class="text-right">${formatCurrency(harga)}</td>
                    <td>${$('#panjang').val() || 0}</td>
                    <td>${qty} ${$('#satuanKecil').val() || 'LBR'}</td>
                    <td class="text-right">${formatCurrency(total)}</td>
                    <td>${$('#satuanKecil').val() || 'LBR'}</td>
                    <td>${diskon}%</td>
                    <td class="text-right">${formatCurrency(diskonAmount)}</td>
                    <td class="text-right">${formatCurrency(total)}</td>
                </tr>
            `);
        }

        // Update preview saat input berubah
        $('#harga, #quantity, #diskon, #ongkos_kuli').on('input', function() {
            updateItemPreview();
        });

        // Hide dropdown when clicking outside (with slight delay to allow clicks on dropdown items)
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#customer, #customerDropdown').length) {
                setTimeout(function() {
                    $('#customerDropdown').hide();
                }, 200);
            }
            if (!$(e.target).closest('#sales, #salesDropdown').length) {
                setTimeout(function() {
                    $('#salesDropdown').hide();
                }, 200);
            }
        });


        $('#addCustomerForm').on('submit', function (e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            
            $.ajax({
                url: "{{ route('customers.store') }}",
                method: "POST",
                data: formData,
                success: function (response) {
                    alert('Customer berhasil ditambahkan!');
                    
                    // Auto-populate customer field
                    const customer = response.customer;
                    $('#kode_customer').val(customer.kode_customer);
                    $('#customer').val(`${customer.kode_customer} - ${customer.nama}`);
                    $('#alamatCustomer').val(customer.alamat);
                    $('#hpCustomer').val(`${customer.hp}${customer.telepon ? ' / ' + customer.telepon : ''}`);
                    
                    $('#addCustomerModal').modal('hide');
                    $('#addCustomerForm')[0].reset();
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat menyimpan customer.');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });



        // Toggle discount and DP inputs
        $('#discount_checkbox').change(function() {
            $('#discount_percent').prop('disabled', !this.checked);
            calculateTotals();
        });

        $('#disc_rp_checkbox').change(function() {
            $('#disc_rp').prop('disabled', !this.checked);
            calculateTotals();
        });

        $('#ppn_checkbox').change(function() {
            calculateTotals();
        });

        $('#dp_checkbox').change(function() {
            $('#dp_amount').prop('disabled', !this.checked);
            calculateTotals();
        });

        // Calculate input changes
        $('#discount_percent, #disc_rp, #dp_amount').on('input', function() {
            calculateTotals();
        });

        // Handle barang change (samakan seperti Sales Order)
        $(document).on('change', '.item-barang', function() {
            const row = $(this).closest('.item-row');
            const selectedOption = $(this).find('option:selected');
            const harga = selectedOption.data('harga') || 0;
            const unitDasarFromOption = selectedOption.data('unit-dasar') || 'LBR';
            
            row.find('.item-harga').val(harga);
            
            // Set small unit from selected item's unit_dasar and populate big units via available-units
            const kecilSelect = row.find('.item-satuan-kecil');
            const besarSelect = row.find('.item-satuan-besar');
            kecilSelect.empty();
            besarSelect.empty();
            kecilSelect.append('<option value="'+unitDasarFromOption+'">'+unitDasarFromOption+'</option>');
            row.find('.item-satuan').val(unitDasarFromOption);

            const kodeBarangId = $(this).val();
            if (kodeBarangId) {
                $.ajax({
                    url: `{{ route('sales-order.available-units', '') }}/${kodeBarangId}`,
                    method: 'GET',
                    success: function(units) {
                        if (Array.isArray(units) && units.length > 0) {
                            units.forEach(function(unit) {
                                if (unit !== unitDasarFromOption) {
                                    besarSelect.append('<option value="'+unit+'">'+unit+'</option>');
                                }
                            });
                        }
                        calculateItemTotal(row);
                    },
                    error: function() {
                        calculateItemTotal(row);
                    }
                });
            } else {
                // reset to empty if no product
                row.find('.item-satuan-kecil').html('<option value=""></option>');
                row.find('.item-satuan-besar').html('');
                row.find('.item-satuan').val('');
                calculateItemTotal(row);
            }
        });

        // Handle qty change
        $(document).on('input', '.item-qty', function() {
            calculateItemTotal($(this).closest('.item-row'));
        });

        // Handle harga change
        $(document).on('input', '.item-harga', function() {
            calculateItemTotal($(this).closest('.item-row'));
        });

        // Handle satuan kecil change (samakan dengan Sales Order: clear big selection)
        $(document).on('change', '.item-satuan-kecil', function() {
            const row = $(this).closest('.item-row');
            const unit = $(this).val();
            row.find('.item-satuan').val(unit);
            row.find('.item-satuan-besar').prop('selectedIndex', -1);
            calculateItemTotal(row);
        });

        // Handle satuan besar change with conversion
        $(document).on('change', '.item-satuan-besar', function() {
            const row = $(this).closest('.item-row');
            const satuanBesar = $(this).val();
            const kodeBarangId = row.find('.item-barang').val();
            
            if (satuanBesar && kodeBarangId) {
                // Get conversion factor and store in data attribute
                $.ajax({
                    url: "{{ route('sales-order.conversion-factor') }}",
                    method: "GET",
                    data: {
                        kode_barang_id: kodeBarangId,
                        unit: satuanBesar
                    },
                    success: function(response) {
                        // Store conversion factor in row data
                        row.data('conversion-factor', response.factor);
                        row.data('unit-dasar', response.unit_dasar);
                        console.log('Conversion factor loaded:', response);
                        
                        // Update satuan hidden field to large unit
                        row.find('.item-satuan').val(satuanBesar);
                        
                        // Recalculate total
                        calculateItemTotal(row);
                    },
                    error: function(xhr) {
                        console.error('Error getting conversion factor:', xhr.responseText);
                        row.data('conversion-factor', 1);
                    }
                });
            } else {
                // Reset to base unit
                row.data('conversion-factor', 1);
                row.find('.item-satuan').val(row.find('.item-satuan-kecil').val());
                calculateItemTotal(row);
            }
        });

        // Calculate item total
        function calculateItemTotal(row) {
            const qty = parseFloat(row.find('.item-qty').val()) || 0;
            const harga = parseFloat(row.find('.item-harga').val()) || 0;
            const total = qty * harga;
            row.find('.item-total').val(total);
        }

        // Add Item Button
        $('#addItemBtn').click(function() {
            const row = $(this).closest('.item-row');
            const kodeBarangSelect = row.find('.item-barang');
            const selectedOption = kodeBarangSelect.find('option:selected');
            
            const kodeBarang = selectedOption.data('kode') || selectedOption.text().split(' - ')[0];
            const namaBarang = selectedOption.data('nama') || selectedOption.text().split(' - ')[1];
            const merek = (selectedOption.data('merek') || '').toString().trim();
            const ukuran = (selectedOption.data('ukuran') || '').toString().trim();
            const composedNama = (merek || ukuran)
                ? `${namaBarang} (${merek || '-'}${merek && ukuran ? ', ' : ''}${ukuran || '-'})`
                : namaBarang;
            const kodeBarangId = kodeBarangSelect.val();
            
            const keterangan = row.find('#keterangan').val();
            const harga = parseFloat(row.find('.item-harga').val()) || 0;
            let qty = parseFloat(row.find('.item-qty').val()) || 0;
            const satuan = row.find('.item-satuan-kecil').val();
            const satuanBesar = row.find('.item-satuan-besar').val();
            const diskon = parseFloat(row.find('#diskon').val()) || 0;
            const ongkosKuli = parseFloat(row.find('#ongkos_kuli').val()) || 0;
            const qtyInBaseUnit = qty; // Di mode satuan kecil, qty sudah dalam unit dasar
            const displayQty = qty; // Keep original for display
            const displaySatuan = satuan; // Display unit is satuan kecil

            if (!kodeBarangId || !kodeBarang || !namaBarang || harga === undefined || harga === null || !qty) {
                alert('Mohon lengkapi data barang!');
                return;
            }

            // Calculate total
            const subtotal = harga * qty;
            const diskonAmount = (subtotal * diskon) / 100;
            const total = subtotal - diskonAmount;

            const newItem = {
                kodeBarang,
                namaBarang: composedNama,
                merek: merek, // Add merek as separate field
                ukuran: ukuran, // Add ukuran as separate field
                keterangan,
                harga: harga,
                qty: qtyInBaseUnit, // Qty dalam unit dasar untuk backend
                qtyDisplay: displayQty, // Qty untuk display
                satuan: satuan, // Satuan dasar untuk backend
                satuanDisplay: displaySatuan, // Satuan untuk display
                satuanBesar: '', // Mode satuan kecil tidak pakai satuan besar
                conversionFactor: 1, // No conversion in small mode
                diskon,
                ongkosKuli,
                total
            };

            items.push(newItem);
            renderItems();
            calculateTotals();

            // Reset form
            row.find('select, input').val('');
            row.find('.item-satuan-kecil').html('<option value=""></option>');
            row.find('.item-satuan-besar').empty();
            row.find('.item-satuan').val('');
            $('#kode_barang_select').val(null).trigger('change');
        });

        // Handle add item button for LARGE mode
        $('#addItemBtnLarge').click(function() {
            const row = $(this).closest('.item-row-large');
            const kodeBarangSelect = row.find('.item-barang-large');
            const selectedOption = kodeBarangSelect.find('option:selected');
            const kodeBarangId = kodeBarangSelect.val();
            
            if (!kodeBarangId) {
                alert('Pilih barang terlebih dahulu!');
                return;
            }
            
            const kodeBarang = selectedOption.data('kode') || '';
            const namaBarang = selectedOption.data('nama') || '';
            const merek = (selectedOption.data('merek') || '').toString().trim();
            const ukuran = (selectedOption.data('ukuran') || '').toString().trim();
            const unitDasar = selectedOption.data('unit-dasar') || 'PCS';
            const composedNama = (merek || ukuran)
                ? `${namaBarang} (${merek || '-'}${merek && ukuran ? ', ' : ''}${ukuran || '-'})`
                : namaBarang;
            
            console.log('Data Barang (Large Mode):', {kodeBarang, namaBarang, merek, ukuran});
            
            const qty = parseFloat(row.find('.item-qty-large').val()) || 0;
            const satuanBesar = row.find('.item-satuan-besar-large').val();
            const harga = parseFloat(row.find('.item-harga-large').val()) || 0;
            const diskon = parseFloat(row.find('#diskon_large').val()) || 0;
            const ongkosKuli = parseFloat(row.find('#ongkos_kuli_large').val()) || 0;
            const keterangan = row.find('#keterangan_large').val();

            // Validation
            if (!qty || qty <= 0 || !satuanBesar) {
                alert('Silakan lengkapi semua field yang wajib (Qty, Satuan Besar)');
                return;
            }

            // Get conversion factor from API (using same route as small mode)
            $.ajax({
                url: "{{ route('sales-order.conversion-factor') }}",
                method: 'GET',
                data: { 
                    kode_barang_id: kodeBarangId,
                    unit: satuanBesar 
                },
                success: function(response) {
                    const factor = parseFloat(response.factor) || 1;
                    const qtyInBaseUnit = qty * factor; // Convert to base unit
                    
                    // Calculate total (harga per base unit)
                    const subtotal = harga * qtyInBaseUnit;
                    const diskonAmount = (subtotal * diskon) / 100;
                    const total = subtotal - diskonAmount;
                    
                    const newItem = {
                        kodeBarang,
                        namaBarang: composedNama,
                        merek: merek, // Add merek as separate field
                        ukuran: ukuran, // Add ukuran as separate field
                        keterangan,
                        harga: harga,
                        qty: qtyInBaseUnit, // Qty dalam unit dasar untuk backend
                        qtyDisplay: qty, // Qty yang diinput user
                        satuan: unitDasar, // Satuan dasar untuk backend
                        satuanDisplay: satuanBesar, // Satuan yang dipilih user untuk display
                        satuanBesar: satuanBesar,
                        conversionFactor: factor,
                        diskon,
                        ongkosKuli,
                        total
                    };

                    console.log('Item to be added (Penjualan Large):', newItem);
                    
                    items.push(newItem);
                    renderItems();
                    calculateTotals();

                    // Reset form
                    row.find('select, input').val('');
                    row.find('.item-satuan-besar-large').empty();
                    row.find('.item-satuan-large').val('');
                    $('#kode_barang_select_large').val(null).trigger('change');
                },
                error: function() {
                    alert('Gagal mendapatkan faktor konversi satuan. Pastikan satuan besar sudah dikonfigurasi.');
                }
            });
        });

        // Handle barang selection for SMALL mode
        $('#kode_barang_select').on('change', function() {
            const barangId = $(this).val();
            
            if (!barangId) {
                $('#stockInfoSmall').hide();
                return;
            }
            
            // Fetch and display available stock
            fetchStockInfo(barangId, 'Small');
        });
        
        // Handle barang selection for LARGE mode
        $('#kode_barang_select_large').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const barangId = $(this).val();
            const harga = selectedOption.data('harga') || 0;
            const unitDasar = selectedOption.data('unit-dasar') || 'PCS';
            
            if (!barangId) {
                $('#satuanBesarLarge').empty().append('<option value="">Pilih satuan besar</option>');
                $('#harga_large').val('');
                $('#item_total_large').val('');
                $('#stockInfoLarge').hide();
                return;
            }
            
            // Set price
            $('#harga_large').val(harga);
            
            // Fetch and display available stock
            fetchStockInfo(barangId, 'Large');
            
            // Fetch available large units (using same route as small mode)
            $.ajax({
                url: `{{ route('sales-order.available-units', '') }}/${barangId}`,
                method: 'GET',
                success: function(units) {
                    const select = $('#satuanBesarLarge');
                    select.empty();
                    
                    let hasLargeUnits = false;
                    if (Array.isArray(units) && units.length > 0) {
                        units.forEach(unit => {
                            if (unit !== unitDasar) {
                                select.append(`<option value="${unit}">${unit}</option>`);
                                hasLargeUnits = true;
                            }
                        });
                    }
                    
                    if (!hasLargeUnits) {
                        select.append(`<option value="">Tidak ada satuan besar</option>`);
                        alert('Barang ini tidak memiliki satuan besar yang dikonfigurasi. Silakan gunakan mode Satuan Kecil.');
                        return;
                    }
                    
                    // Set first as default and trigger calculation
                    const first = select.find('option').first().val() || '';
                    $('#satuan_large').val(first);
                    select.val(first).trigger('change');
                    
                    console.log('Satuan besar loaded:', units, 'Selected:', first);
                },
                error: function(xhr) {
                    console.error('Error fetching available units:', xhr);
                    $('#satuanBesarLarge').empty().append('<option value="">Error loading units</option>');
                    alert('Error mengambil satuan besar: ' + (xhr.responseJSON?.message || xhr.statusText));
                }
            });
        });

        // Auto-calculate total for large mode when qty or harga changes
        $('#quantity_large, #harga_large').on('input', function() {
            const qty = parseFloat($('#quantity_large').val()) || 0;
            const harga = parseFloat($('#harga_large').val()) || 0;
            const satuan = $('#satuanBesarLarge').val();
            const barangId = $('#kode_barang_select_large').val();
            
            if (!satuan || !barangId) {
                $('#item_total_large').val(qty * harga);
                return;
            }
            
            // Fetch conversion factor and calculate (using same route as small mode)
            $.ajax({
                url: "{{ route('sales-order.conversion-factor') }}",
                method: 'GET',
                data: { 
                    kode_barang_id: barangId,
                    unit: satuan 
                },
                success: function(response) {
                    const factor = parseFloat(response.factor) || 1;
                    const qtyInBase = qty * factor;
                    const total = qtyInBase * harga;
                    
                    console.log('Auto-calc Conversion:', {
                        response: response,
                        factor: factor,
                        qty: qty,
                        qtyInBase: qtyInBase,
                        harga: harga,
                        total: total
                    });
                    
                    $('#item_total_large').val(total);
                },
                error: function(xhr) {
                    console.error('Error calculating conversion:', xhr);
                    $('#item_total_large').val(qty * harga);
                }
            });
        });

        $('#satuanBesarLarge').on('change', function() {
            $('#satuan_large').val($(this).val());
            $('#quantity_large').trigger('input'); // Recalculate total
        });

        // Function to render items table
        function renderItems() {
            const tbody = $('#itemsList');
            tbody.empty();

            items.forEach((item, index) => {
                // Display dengan qty dan satuan yang user input (bisa satuan besar)
                const displayQty = item.qtyDisplay || item.qty;
                const displaySatuan = item.satuanDisplay || item.satuan;
                
                // Debug log untuk melihat data item
                console.log('Display Item (Penjualan):', {
                    qtyDisplay: item.qtyDisplay,
                    qty: item.qty,
                    satuan: item.satuan,
                    satuanDisplay: item.satuanDisplay,
                    satuanBesar: item.satuanBesar
                });
                
                // Format display: "2 BOX (20 PAIR)" jika ada konversi
                let qtyDisplay;
                if (item.satuanBesar && item.qtyDisplay && item.qty !== item.qtyDisplay) {
                    // Ada konversi: tampilkan input user + hasil konversi
                    qtyDisplay = `${item.qtyDisplay} ${item.satuanDisplay} (${Math.round(item.qty)} ${item.satuan})`;
                } else {
                    // Tidak ada konversi atau satuan kecil
                    qtyDisplay = `${displayQty} ${displaySatuan}`;
                }
                
                tbody.append(`
                    <tr>
                        <td>${item.kodeBarang}</td>
                        <td>${item.namaBarang}</td>
                        <td>${item.merek || '-'}</td>
                        <td>${item.ukuran || '-'}</td>
                        <td>${item.keterangan || '-'}</td>
                        <td class="text-right">${formatCurrency(item.harga)}</td>
                        <td>${qtyDisplay}</td>
                        <td>${item.satuanBesar ? 'Ya' : '-'}</td>
                        <td class="text-right">${formatCurrency(item.total)}</td>
                        <td class="text-right">${formatCurrency(item.ongkosKuli || 0)}</td>
                        <td class="text-right">${item.diskon || 0}%</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });

            // Remove item handling
            $('.remove-item').click(function() {
                const index = $(this).data('index');
                items.splice(index, 1);
                renderItems();
                calculateTotals();
            });

            $('#addItemModal').modal('hide');
        }

        // Calculate all totals
        function calculateTotals() {
            // Calculate subtotal
            const subtotal = items.reduce((sum, item) => sum + item.total, 0);
            $('#total').val(formatCurrency(subtotal));

            // Calculate discount
            let discountAmount = 0;
            if ($('#discount_checkbox').is(':checked')) {
                const discountPercent = parseFloat($('#discount_percent').val()) || 0;
                discountAmount = (subtotal * discountPercent) / 100;
            }
            $('#discount_amount').val(formatCurrency(discountAmount));

            // Calculate additional discount
            let discRp = 0;
            if ($('#disc_rp_checkbox').is(':checked')) {
                discRp = parseFloat($('#disc_rp').val()) || 0;
            }

            // Calculate PPN
            let ppnAmount = 0;
            if ($('#ppn_checkbox').is(':checked')) {
                const ppnRate = parseFloat($('#ppn_rate_hidden').val()||'11');
                ppnAmount = ((subtotal - discountAmount - discRp) * (ppnRate/100));
            }
            $('#ppn_amount').val(formatCurrency(ppnAmount));

            // Calculate DP
            let dpAmount = 0;
            if ($('#dp_checkbox').is(':checked')) {
                dpAmount = parseFloat($('#dp_amount').val()) || 0;
            }

            // Calculate grand total
            grandTotal = subtotal - discountAmount - discRp + ppnAmount - dpAmount;
            $('#grand_total').val(formatCurrency(grandTotal));
        }

        // Format currency
        /**
         * Fetch and display available stock for selected item
         */
        function fetchStockInfo(barangId, mode) {
            $.ajax({
                url: `/api/stock/available/${barangId}`,
                method: 'GET',
                success: function(response) {
                    const stockQty = response.stock || 0;
                    const stockUnit = response.unit || 'PCS';
                    
                    if (mode === 'Small') {
                        $('#stockQtySmall').text(formatNumber(stockQty));
                        $('#stockUnitSmall').text(stockUnit);
                        $('#stockInfoSmall').fadeIn();
                    } else if (mode === 'Large') {
                        $('#stockQtyLarge').text(formatNumber(stockQty));
                        $('#stockUnitLarge').text(stockUnit);
                        $('#stockInfoLarge').fadeIn();
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching stock info:', xhr);
                    if (mode === 'Small') {
                        $('#stockInfoSmall').hide();
                    } else if (mode === 'Large') {
                        $('#stockInfoLarge').hide();
                    }
                }
            });
        }

        /**
         * Format number with thousand separator
         */
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }
        
        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID').format(amount);
        }

        // Inject PPN rate from perusahaan default
        if ($('#ppn_rate_hidden').length === 0) {
            $('body').append('<input type="hidden" id="ppn_rate_hidden" value="{{ $ppnConfig['rate'] ?? 0 }}">');
        }
        
        // Auto-check PPN if enabled in company settings
        @if($ppnConfig['enabled'] ?? false)
            $('#ppn_checkbox').prop('checked', true);
        @else
            $('#ppn_checkbox').prop('checked', false);
        @endif

        // Save transaction
        $('#saveTransaction').click(function() {
    if (confirm('Apakah Anda Yakin ingin menyimpan?')){
        if (!$('#kode_customer').val()) {
            alert('Pilih customer dari daftar yang tersedia!');
            return;
        }

        if (items.length === 0) {
            alert('Tidak ada barang yang ditambahkan!');
            return;
        }

        const transactionData = {
            tanggal: $('#tanggal').val(),
            kode_customer: $('#kode_customer').val(),
            sales_order_id: $('#sales_order_id').val() || null,
            sales: $('#sales').val(),
            pembayaran: $('#metode_pembayaran').val(),
            cara_bayar: $('#cara_bayar').val() || $('#cara_bayar_akhir').val() || 'Tunai',
            no_po: $('#no_po').val() || '',
            hari_tempo: parseInt($('#hari_tempo').val()||'0',10),
            tanggal_jatuh_tempo: $('#tanggal_jatuh_tempo').val() || null,
            tanggal_jadi: $('#tanggal_jadi').val(),
            items: items,
            subtotal: $('#total').val().replace(/\./g, ''),
            discount: $('#discount_checkbox').is(':checked') ? parseFloat($('#discount_percent').val()) || 0 : 0,
            disc_rp: $('#disc_rp_checkbox').is(':checked') ? parseFloat($('#disc_rp').val()) || 0 : 0,
            ppn: $('#ppn_checkbox').is(':checked') ? parseFloat($('#ppn_amount').val().replace(/\./g, '')) || 0 : 0,
            dp: $('#dp_checkbox').is(':checked') ? parseFloat($('#dp_amount').val()) || 0 : 0,
            grand_total: grandTotal,
            notes: $('#notes').val()
        };

        showLoading();

        // Simpan transaksi ke backend (khusus dari Surat Jalan pakai endpoint khusus agar transfer FIFO)
        const isFromSuratJalan = !!noSj;
        let ajaxUrl = "{{ route('transaksi.store') }}";
        let ajaxMethod = "POST";
        let ajaxData = transactionData;
        if (isFromSuratJalan) {
            // Siapkan payload untuk storeFromSuratJalan
            ajaxUrl = "{{ route('api.transaksi.store-from-sj') }}";
            ajaxMethod = "POST";
            const itemsForSj = items.map(function(it){
                return {
                    surat_jalan_item_id: it.surat_jalan_item_id || null,
                    kode_barang: it.kodeBarang,
                    nama_barang: it.namaBarang,
                    qty: it.qty,
                    satuan: it.satuan || 'LBR',
                    harga: it.harga,
                    ongkos_kuli: it.ongkosKuli || 0,
                    total: it.total,
                    diskon: it.diskon || 0,
                    keterangan: it.keterangan || ''
                };
            });
            ajaxData = {
                no_transaksi: $('#no_transaksi').val() || '',
                tanggal: transactionData.tanggal,
                kode_customer: transactionData.kode_customer,
                sales: transactionData.sales,
                pembayaran: transactionData.pembayaran,
                cara_bayar: transactionData.cara_bayar,
                tanggal_jadi: transactionData.tanggal_jadi,
                no_po: $('#no_po').val() || '',
                hari_tempo: transactionData.hari_tempo,
                tanggal_jatuh_tempo: transactionData.tanggal_jatuh_tempo,
                subtotal: parseFloat((transactionData.subtotal||'0').toString().replace(/\./g, '')) || 0,
                discount: transactionData.discount,
                disc_rupiah: transactionData.disc_rp,
                ppn: transactionData.ppn,
                dp: transactionData.dp,
                grand_total: transactionData.grand_total,
                notes: transactionData.notes,
                surat_jalan_id: suratJalanId,
                items: itemsForSj
            };
        }

        $.ajax({
            url: ajaxUrl,
            method: ajaxMethod,
            data: ajaxData,
            success: function(response) {
                hideLoading();
                // Tampilkan modal invoice
                $('#invoiceNoTransaksi').text(response.no_transaksi);
                $('#invoiceTanggal').text(response.tanggal);
                $('#invoiceCustomer').text(response.customer);
                $('#invoiceGrandTotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.grand_total || 0));

                // Simpan ID transaksi untuk tombol Print
                const transactionId = response.id;

                // Tombol Print Standard
                $('#printInvoiceBtn').off('click').on('click', function() {
                    const printUrl = `{{ url('transaksi/lihatnota') }}/${transactionId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                // Tombol Print Nota Kecil
                $('#printNotaKecilBtn').off('click').on('click', function(e) {
                    e.preventDefault();
                    const printUrl = `{{ url('transaksi/nota-kecil') }}/${transactionId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                // Tombol Print Nota Besar
                $('#printNotaBesarBtn').off('click').on('click', function(e) {
                    e.preventDefault();
                    const printUrl = `{{ url('transaksi/nota-besar') }}/${transactionId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                // Tombol Print Nota Sementara
                $('#printNotaSementaraBtn').off('click').on('click', function(e) {
                    e.preventDefault();
                    const printUrl = `{{ url('transaksi/nota-sementara') }}/${transactionId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                $('#invoiceModal').modal('show');
            },
            error: function(xhr) {
                hideLoading();
                alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
            }
        });

        // Tombol Kembali
        $('#backToFormBtn').off('click').on('click', function(){
            $('#invoiceModal').modal('hide');
            $('#transactionForm')[0].reset();
            items = [];
            renderItems();
            calculateTotals();
            window.location.href = "{{ route('transaksi.penjualan') }}";
        });

        // You would typically send this data to your backend using AJAX
        console.log('Transaction data:', transactionData);
    }
});

        // Button Buat PO
        $('#buatPOBtn').click(function(){
        if (confirm('Simpan sebagai PO (tidak mempengaruhi stok)?')) {
            if (!$('#kode_customer').val()) {
                alert('Pilih customer dari daftar yang tersedia!');
                return;
            }

            if (items.length === 0) {
                alert('Tidak ada barang yang ditambahkan!');
                return;
            }

            // Format items for PO
            const poItems = items.map(item => ({
                kodeBarang: item.kodeBarang,
                namaBarang: item.namaBarang,
                keterangan: item.keterangan || '',
                harga: item.harga,
                panjang: item.panjang || 0,
                qty: item.qty,
                total: item.total,
                diskon: item.diskon || 0
            }));

            // Create the PO data
            const formData = new FormData();
            
            // Add form fields
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('tanggal', $('#tanggal').val());
            formData.append('kode_customer', $('#kode_customer').val());
            formData.append('sales', $('#kode_sales').val());
            formData.append('pembayaran', $('#metode_pembayaran').val());
            formData.append('cara_bayar', $('#cara_bayar').val());
            formData.append('subtotal', parseFloat($('#total').val().replace(/\./g, '')) || 0);
            formData.append('discount', $('#discount_checkbox').is(':checked') ? parseFloat($('#discount_percent').val()) || 0 : 0);
            formData.append('disc_rupiah', $('#disc_rp_checkbox').is(':checked') ? parseFloat($('#disc_rp').val()) || 0 : 0);
            formData.append('ppn', $('#ppn_checkbox').is(':checked') ? parseFloat($('#ppn_amount').val().replace(/\./g, '')) || 0 : 0);
            formData.append('dp', $('#dp_checkbox').is(':checked') ? parseFloat($('#dp_amount').val()) || 0 : 0);
            formData.append('grand_total', grandTotal);
            
            // Add items as JSON string
            formData.append('items', JSON.stringify(poItems));
                showLoading();
                $.ajax({
                    url: "{{ route('purchase-order.store') }}", // Ubah ini ke route PO
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        hideLoading();
                        alert('PO berhasil disimpan!');
                        // Opsional: reset form
                        $('#transactionForm')[0].reset();
                        items = [];
                        renderItems();
                        calculateTotals();
                        window.location.href = "{{ route('transaksi.penjualan') }}";
                    },
                    error: function(xhr) {
                        hideLoading();
                        alert('Gagal menyimpan PO: ' + xhr.responseJSON.message);
                    }
                });
            }
        });

        // Cancel transaction
        $('#cancelTransaction').click(function() {
            if (confirm('Batalkan transaksi? Semua data akan hilang.')) {
                $('#transactionForm')[0].reset();
                items = [];
                renderItems();
                calculateTotals();
            }
        });

        // No PO auto-generation; user inputs PO manually
    });

    // Removed generatePoNumber implementation
</script>
@endsection
