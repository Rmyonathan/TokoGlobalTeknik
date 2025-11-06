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
</style>
<div class="container">
    <!-- Header Section -->
    <div class="title-box">
        <h2><i class="fas fa-truck mr-2"></i>Surat Jalan</h2>
    </div>

    <!-- Main Form Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Data Surat Jalan</h5>
        </div>
        <div class="card-body">
            <form id="suratjalanForm">
                @csrf
                <div class="row">
                    <!-- Left Column - Basic Information -->
                    <div class="col-md-6">
                        <!-- Surat Jalan Number -->
                        <div class="form-group">
                            <label for="no_suratjalan">No. Surat Jalan</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="no_suratjalan" 
                                   name="no_suratjalan" 
                                   value="{{ $noSuratJalan ?? 'SJ-001-00001' }}" 
                                   readonly 
                                   style="background-color: #ffc107; color: #000; font-weight: bold;">
                        </div>

                        <!-- Date -->
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <div class="input-group">
                                <input type="date" 
                                       class="form-control" 
                                       id="tanggal" 
                                       name="tanggal" 
                                       value="{{ date('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Selection -->
                        <div class="form-group">
                            <label for="customer">Customer</label>
                            <div class="input-group">
                                <select class="form-control" id="customer_display" name="customer_display">
                                    <option value="">-- Pilih Customer --</option>
                                    @if(isset($customers) && count($customers) > 0)
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->kode_customer }}" 
                                                    data-nama="{{ $customer->nama }}"
                                                    data-alamat="{{ $customer->alamat }}"
                                                    data-hp="{{ $customer->hp }}"
                                                    data-telepon="{{ $customer->telepon }}"
                                                    data-limit-hari-tempo="{{ $customer->limit_hari_tempo }}">
                                                {{ $customer->kode_customer }} - {{ $customer->nama }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">Tidak ada data customer</option>
                                    @endif
                                </select>
                                <div class="input-group-append">
                                    <button type="button" 
                                            class="btn btn-success" 
                                            data-toggle="modal" 
                                            data-target="#addCustomerModal" 
                                            title="Tambah Customer Baru">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="kode_customer" name="kode_customer">
                        </div>

                        <!-- Customer Address -->
                        <div class="form-group">
                            <label for="alamatCustomer">Alamat Customer</label>
                            <input type="text" 
                                   id="alamatCustomer" 
                                   name="customer-alamat" 
                                   class="form-control" 
                                   readonly>
                        </div>

                        <!-- Customer Contact -->
                        <div class="form-group">
                            <label for="hpCustomer">No HP / Telp Customer</label>
                            <input type="text" 
                                   id="hpCustomer" 
                                   name="customer-hp" 
                                   class="form-control" 
                                   readonly>
                        </div>

                        <!-- Delivery Address -->
                        <div class="form-group">
                            <label for="alamat_suratjalan">Alamat di Surat Jalan</label>
                            <textarea class="form-control" 
                                      id="alamat_suratjalan" 
                                      name="alamat_suratjalan" 
                                      rows="2" 
                                      placeholder="Alamat pengiriman"></textarea>
                        </div>

                        <!-- PO Number -->
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
                    </div>

                    <!-- Right Column - Payment Information -->
                    <div class="col-md-6">
                        <!-- Money Deposit -->
                        <div class="form-group">
                            <label for="titipan_uang">Titipan Uang</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="titipan_uang" 
                                   name="titipan_uang" 
                                   value="0" 
                                   min="0">
                        </div>

                        <!-- Mode Input Barang -->
                        <div class="form-group">
                            <label for="mode_input_barang">Mode Input Barang</label>
                            <select class="form-control" id="mode_input_barang">
                                <option value="kecil" selected>Satuan Kecil</option>
                                <option value="besar">Satuan Besar</option>
                            </select>
                            <small class="form-text text-muted">Pilih cara input: satuan kecil atau langsung satuan besar</small>
                        </div>

                        <!-- Remaining Debt -->
                        <div class="form-group">
                            <label for="sisa_piutang">Sisa Piutang</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="sisa_piutang" 
                                   name="sisa_piutang" 
                                   value="0" 
                                   min="0">
                        </div>

                        <!-- Payment Method -->
                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran</label>
                            <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
                                <option value="Tunai">Tunai</option>
                                <option value="Non Tunai" selected>Non Tunai</option>
                            </select>
                            <small class="form-text text-muted">Sumber dari master Cara Bayar</small>
                        </div>

                        <!-- Payment Type -->
                        <div class="form-group">
                            <label for="cara_bayar">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar" name="cara_bayar">
                                @if(isset($caraBayars) && count($caraBayars) > 0)
                                    @foreach($caraBayars as $cb)
                                        <option value="{{ $cb->nama }}" data-metode="{{ $cb->metode }}" {{ $cb->nama === 'Kredit' ? 'selected' : '' }}>
                                            {{ $cb->nama }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="Kredit" selected>Kredit</option>
                                @endif
                            </select>
                        </div>

                        <!-- Credit Terms -->
                        <div class="form-group" id="hariTempoGroup" style="display:block;">
                            <label for="hari_tempo">Hari Tempo</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="hari_tempo" 
                                   name="hari_tempo" 
                                   min="0" 
                                   value="0">
                            <small class="form-text text-muted">Isi 0 jika tanpa tempo</small>
                        </div>

                        <!-- Due Date -->
                        <div class="form-group" id="jatuhTempoGroup" style="display:block;">
                            <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="tanggal_jatuh_tempo" 
                                   name="tanggal_jatuh_tempo">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Item Section (Satuan Kecil) -->
    <div class="card mb-4" id="cardSmallItems">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-box mr-2"></i>Tambah Barang (Satuan Kecil)</h5>
        </div>
        <div class="card-body">
            <!-- Form Tambah Barang -->
            <div class="bg-light p-3 rounded mb-3 border">
                <!-- Baris 1: Data Utama Barang -->
                <div class="row mb-2">
                    <div class="col-lg-4 col-md-6 mb-2">
                        <label class="font-weight-bold small mb-1">Barang <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="newKodeBarangSelect">
                            <option value="">Pilih Barang</option>
                            @if(isset($kodeBarangs) && count($kodeBarangs) > 0)
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
                        <!-- Keep old inputs hidden for compatibility -->
                        <input type="hidden" id="newKodeBarangInput" value="">
                        <input type="hidden" id="newKodeBarangId" value="">
                        <!-- Stock info badge -->
                        <div id="stockInfoSmall" class="mt-1" style="display:none;">
                            <small class="badge badge-info">
                                <i class="fas fa-box"></i> Sisa Stok: <span id="stockQtySmall">0</span> <span id="stockUnitSmall"></span>
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="newQty" step="0.01" min="0.01" placeholder="0">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Satuan Kecil</label>
                        <select class="form-control form-control-sm" id="newSatuanKecil">
                            <option value=""></option>
                        </select>
                        <input type="hidden" id="newSatuan" value="">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="font-weight-bold small mb-1">Satuan Besar</label>
                        <select class="form-control form-control-sm" id="newSatuanBesar"></select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="font-weight-bold small mb-1">Harga <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="newHarga" step="0.01" min="0" placeholder="0">
                    </div>
                </div>
                
                <!-- Baris 2: Detail Tambahan -->
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Total</label>
                        <input type="number" class="form-control form-control-sm" id="newTotal" readonly style="background-color: #e9ecef;">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Diskon (%)</label>
                        <input type="number" class="form-control form-control-sm" id="newDiskon" placeholder="0" min="0" max="100">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Ongkos Kuli</label>
                        <input type="number" class="form-control form-control-sm" id="newOngkosKuli" placeholder="0">
                    </div>
                    <div class="col-lg-5 col-md-9 mb-2">
                        <label class="font-weight-bold small mb-1">Keterangan</label>
                        <input type="text" class="form-control form-control-sm" id="newKeterangan" placeholder="Keterangan tambahan (opsional)">
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

    <!-- Add Item Section (Satuan Besar) -->
    <div class="card mb-4" id="cardLargeItems" style="display:none;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-box mr-2"></i>Tambah Barang (Satuan Besar)</h5>
        </div>
        <div class="card-body">
            <!-- Form Tambah Barang Satuan Besar -->
            <div class="bg-light p-3 rounded mb-3 border">
                <!-- Baris 1: Data Utama Barang -->
                <div class="row mb-2">
                    <div class="col-lg-5 col-md-6 mb-2">
                        <label class="font-weight-bold small mb-1">Barang <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="newKodeBarangSelectLarge">
                            <option value="">Pilih Barang</option>
                            @if(isset($kodeBarangs) && count($kodeBarangs) > 0)
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
                        <input type="hidden" id="newKodeBarangIdLarge" value="">
                        <!-- Stock info badge -->
                        <div id="stockInfoLarge" class="mt-1" style="display:none;">
                            <small class="badge badge-info">
                                <i class="fas fa-box"></i> Sisa Stok: <span id="stockQtyLarge">0</span> <span id="stockUnitLarge"></span>
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="newQtyLarge" step="0.01" min="0.01" placeholder="0">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="font-weight-bold small mb-1">Satuan Besar <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" id="newSatuanBesarLarge">
                            <option value="">Pilih satuan besar</option>
                        </select>
                        <input type="hidden" id="newSatuanLarge" value="">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="font-weight-bold small mb-1">Harga <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" id="newHargaLarge" step="0.01" min="0" placeholder="0">
                    </div>
                </div>
                
                <!-- Baris 2: Detail Tambahan -->
                <div class="row align-items-end">
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Total</label>
                        <input type="number" class="form-control form-control-sm" id="newTotalLarge" readonly style="background-color: #e9ecef;">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Diskon (%)</label>
                        <input type="number" class="form-control form-control-sm" id="newDiskonLarge" placeholder="0" min="0" max="100">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="font-weight-bold small mb-1">Ongkos Kuli</label>
                        <input type="number" class="form-control form-control-sm" id="newOngkosKuliLarge" placeholder="0">
                    </div>
                    <div class="col-lg-5 col-md-9 mb-2">
                        <label class="font-weight-bold small mb-1">Keterangan</label>
                        <input type="text" class="form-control form-control-sm" id="newKeteranganLarge" placeholder="Keterangan tambahan (opsional)">
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

    <!-- Items List Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Barang</h5>
        </div>
        <div class="card-body">
            <!-- Items Table -->
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
            
            <!-- Summary Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator mr-2"></i>Ringkasan Total</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Kolom Kiri: Perhitungan -->
                        <div class="col-lg-6 mb-3">
                            <div class="bg-light p-3 rounded border">
                                <h6 class="font-weight-bold mb-3 text-primary"><i class="fas fa-coins mr-2"></i>Perhitungan</h6>
                                
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold mb-1">Subtotal</label>
                                    <div class="alert alert-info p-2 mb-0">
                                        <span id="summary_subtotal" class="font-weight-bold" style="font-size: 1.2rem;">Rp 0</span>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="ppn_checkbox_sj">
                                        <label class="custom-control-label small font-weight-bold" for="ppn_checkbox_sj">
                                            PPN ({{ $ppnConfig['rate'] ?? 11 }}%)
                                        </label>
                                    </div>
                                    <div class="mt-2">
                                        <span id="summary_ppn" class="text-muted">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan: Total & Aksi -->
                        <div class="col-lg-6 mb-3">
                            <div class="bg-light p-3 rounded border h-100">
                                <h6 class="font-weight-bold mb-3 text-success"><i class="fas fa-money-bill-wave mr-2"></i>Total Akhir</h6>
                                
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold mb-2">Grand Total</label>
                                    <div class="alert alert-success p-2 mb-0" style="background-color: #d4edda; border: 2px solid #28a745;">
                                        <span id="summary_grand_total" class="font-weight-bold text-success d-block text-center" style="font-size: 1.8rem;">Rp 0</span>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="button" class="btn btn-success btn-block btn-lg mb-2" id="saveSuratJalan">
                                        <i class="fas fa-save mr-2"></i>Simpan Surat Jalan
                                    </button>
                                    <button type="button" class="btn btn-warning btn-block" id="resetForm">
                                        <i class="fas fa-redo mr-2"></i>Reset Form
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

<script>
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
    $('#newKodeBarangSelect').select2({
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
    $('#newKodeBarangSelectLarge').select2({
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
    
    // Initialize Select2 for customer dropdown
    $('#customer_display').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Customer --',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Customer tidak ditemukan";
            },
            searching: function() {
                return "Mencari...";
            }
        }
    });
    
    // Handle new select dropdown change event (SMALL mode)
    $('#newKodeBarangSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const barangId = $(this).val();
        const kodeBarang = selectedOption.data('kode');
        const namaBarang = selectedOption.data('nama');
        const harga = selectedOption.data('harga') || 0;
        const unitDasar = selectedOption.data('unit-dasar') || 'PCS';
        const merek = selectedOption.data('merek') || '';
        const ukuran = selectedOption.data('ukuran') || '';
        
        if (!barangId) {
            $('#newSatuanKecil').empty().append('<option value=""></option>');
            $('#newSatuanBesar').empty();
            $('#newHarga').val('');
            $('#newTotal').val('');
            $('#newKodeBarangId').val('');
            $('#newKodeBarangInput').val('');
            $('#stockInfoSmall').hide();
            return;
        }
        
        // Update hidden inputs for compatibility with existing code
        $('#newKodeBarangId').val(barangId);
        $('#newKodeBarangInput').val(kodeBarang + ' - ' + namaBarang);
        
        // Fetch and display available stock
        fetchStockInfo(barangId, 'Small');
        
        // Store selected barang data for later use (including merek & ukuran)
        $('#newKodeBarangInput').data('selectedBarang', {
            id: barangId,
            kode: kodeBarang,
            nama: namaBarang,
            harga: harga,
            unitDasar: unitDasar,
            merek: merek,
            ukuran: ukuran
        });
        
        // Set price
        $('#newHarga').val(harga);
        
        // Set unit options for satuan kecil
        $('#newSatuanKecil').html(`<option value="${unitDasar}">${unitDasar}</option>`);
        $('#newSatuan').val(unitDasar);
        
        // Fetch available large units
        $.ajax({
            url: `{{ route('suratjalan.available-units', '') }}/${barangId}`,
            method: 'GET',
            success: function(units) {
                const besarSelect = $('#newSatuanBesar');
                besarSelect.empty();
                
                if (Array.isArray(units) && units.length > 0) {
                    units.forEach(unit => {
                        if (unit !== unitDasar) {
                            besarSelect.append(`<option value="${unit}">${unit}</option>`);
                        }
                    });
                }
                
                console.log('Satuan besar loaded for small mode:', units);
            },
            error: function(xhr) {
                console.log('No large units available or error:', xhr);
                $('#newSatuanBesar').empty();
            }
        });
        
        // Auto calculate total when qty changes
        $('#newQty').trigger('input');
        
        console.log('Selected item:', {barangId, kodeBarang, namaBarang, harga, unitDasar});
    });
    
    // Handle new select dropdown change event for LARGE mode
    $('#newKodeBarangSelectLarge').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const barangId = $(this).val();
        const kodeBarang = selectedOption.data('kode');
        const namaBarang = selectedOption.data('nama');
        const harga = selectedOption.data('harga') || 0;
        const unitDasar = selectedOption.data('unit-dasar') || 'PCS';
        
        if (!barangId) {
            $('#newSatuanBesarLarge').empty().append('<option value="">Pilih satuan besar</option>');
            $('#newHargaLarge').val('');
            $('#newTotalLarge').val('');
            $('#newKodeBarangIdLarge').val('');
            $('#stockInfoLarge').hide();
            return;
        }
        
        // Update hidden input
        $('#newKodeBarangIdLarge').val(barangId);
        
        // Set price
        $('#newHargaLarge').val(harga);
        
        // Fetch and display available stock
        fetchStockInfo(barangId, 'Large');
        
        // Fetch available large units (using same route as small mode)
        $.ajax({
            url: `{{ route('suratjalan.available-units', '') }}/${barangId}`,
            method: 'GET',
            success: function(units) {
                const select = $('#newSatuanBesarLarge');
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
                    alert('Barang ini tidak memiliki satuan besar yang dikonfigurasi. Silakan gunakan mode Satuan Kecil atau tambahkan konversi satuan di Master Barang.');
                    return;
                }
                
                // Set first as default and trigger calculation
                const first = select.find('option').first().val() || '';
                $('#newSatuanLarge').val(first);
                select.val(first).trigger('change');
                
                console.log('Satuan besar loaded:', units, 'Selected:', first);
            },
            error: function(xhr) {
                console.error('Error fetching available units:', xhr);
                $('#newSatuanBesarLarge').empty().append('<option value="">Error loading units</option>');
                alert('Error mengambil satuan besar: ' + (xhr.responseJSON?.message || xhr.statusText));
            }
        });
        
        console.log('Selected item (Large):', {barangId, kodeBarang, namaBarang, harga, unitDasar});
    });
});

// ========================================
// GLOBAL VARIABLES
// ========================================
let items = [];

// ========================================
// UTILITY FUNCTIONS
// ========================================

/**
 * Format currency to Indonesian Rupiah
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Calculate due date based on base date and credit terms
 */
function recalcJatuhTempo() {
    const base = $('#tanggal').val();
    const hari = parseInt($('#hari_tempo').val() || '0', 10);
    
    if (!base || isNaN(hari)) return;
    
    const d = new Date(base);
    d.setDate(d.getDate() + hari);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    
    $('#tanggal_jatuh_tempo').val(`${yyyy}-${mm}-${dd}`);
}

/**
 * Calculate total for new item
 */
function calculateNewItemTotal() {
    const qty = parseFloat($('#newQty').val()) || 0;
    const harga = parseFloat($('#newHarga').val()) || 0;
    const diskon = parseFloat($('#newDiskon').val()) || 0;
    
    const subtotal = qty * harga;
    const diskonAmount = (subtotal * diskon) / 100;
    const total = subtotal - diskonAmount;
    
    $('#newTotal').val(total.toFixed(2));
}

// ========================================
// EVENT HANDLERS
// ========================================

/**
 * Handle customer selection change
 */
$('#customer_display').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    
    if (selectedOption.val()) {
        // Fill customer data
        $('#kode_customer').val(selectedOption.val());
        $('#alamatCustomer').val(selectedOption.data('alamat') || '');
        $('#hpCustomer').val(`${selectedOption.data('hp') || ''} / ${selectedOption.data('telepon') || ''}`);
        $('#alamat_suratjalan').val(selectedOption.data('alamat') || '');
        
        // Auto-fill credit terms
        const hariTempo = selectedOption.data('limit-hari-tempo') || 0;
        $('#hari_tempo').val(hariTempo);
        recalcJatuhTempo();
    } else {
        // Clear customer data
        $('#kode_customer').val('');
        $('#alamatCustomer').val('');
        $('#hpCustomer').val('');
        $('#alamat_suratjalan').val('');
        $('#hari_tempo').val(0);
        $('#tanggal_jatuh_tempo').val('');
    }
});

/**
 * Handle product selection change - DEPRECATED (now using input with search)
 * This function is kept for backward compatibility but is no longer used
 */

/**
 * Handle quantity and price changes for total calculation
 */
$('#newQty, #newHarga, #newDiskon').on('input', function() {
    calculateNewItemTotal();
});

/**
 * Handle satuan besar change with conversion
 */
$('#newSatuanBesar').on('change', function() {
    const satuanBesar = $(this).val();
    const kodeBarangId = $('#newKodeBarangId').val();
    
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
                // Store conversion factor in input data attribute
                $('#newKodeBarangInput').data('conversion-factor', response.factor);
                $('#newKodeBarangInput').data('unit-dasar', response.unit_dasar);
                console.log('Conversion factor loaded for surat jalan:', response);
                
                // Recalculate total
                calculateNewItemTotal();
            },
            error: function(xhr) {
                console.error('Error getting conversion factor:', xhr.responseText);
                $('#newKodeBarangInput').data('conversion-factor', 1);
            }
        });
    } else {
        // Reset to base unit
        $('#newKodeBarangInput').data('conversion-factor', 1);
        calculateNewItemTotal();
    }
});

/**
 * Handle date and credit terms changes for due date calculation
 */
$('#tanggal').on('change', recalcJatuhTempo);
$('#hari_tempo').on('input', recalcJatuhTempo);

/**
 * Handle PPN checkbox change
 */
$('#ppn_checkbox_sj').on('change', function() {
    updateSummaryTotal();
});

/**
 * Handle add item button click
 */
$('#addItemBtn').on('click', function() {
    addNewItem();
});

/**
 * Handle add item button for LARGE mode
 */
$('#addItemBtnLarge').on('click', function() {
    addNewItemLarge();
});

// Auto-calculate total for large mode when qty or harga changes
$('#newQtyLarge, #newHargaLarge').on('input', function() {
    const qty = parseFloat($('#newQtyLarge').val()) || 0;
    const harga = parseFloat($('#newHargaLarge').val()) || 0;
    const satuan = $('#newSatuanBesarLarge').val();
    const barangId = $('#newKodeBarangIdLarge').val();
    
    if (!satuan || !barangId) {
        $('#newTotalLarge').val(qty * harga);
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
            
            console.log('Auto-calc Conversion (SJ):', {
                response: response,
                factor: factor,
                qty: qty,
                qtyInBase: qtyInBase,
                harga: harga,
                total: total
            });
            
            $('#newTotalLarge').val(total);
        },
        error: function(xhr) {
            console.error('Error calculating conversion:', xhr);
            $('#newTotalLarge').val(qty * harga);
        }
    });
});

$('#newSatuanBesarLarge').on('change', function() {
    $('#newSatuanLarge').val($(this).val());
    $('#newQtyLarge').trigger('input'); // Recalculate total
});

/**
 * Handle remove item button click
 */
$(document).on('click', '.remove-item', function() {
    const index = $(this).data('index');
    if (confirm('Hapus barang ini?')) {
        items.splice(index, 1);
        updateItemsTable();
    }
});

/**
 * Handle save surat jalan button click
 */
$('#saveSuratJalan').on('click', function() {
    saveSuratJalan();
});

/**
 * Handle reset form button click
 */
$('#resetForm').on('click', function() {
    if (confirm('Apakah Anda yakin ingin mereset form? Semua data yang sudah diisi akan hilang.')) {
        resetForm();
    }
});

// ========================================
// AJAX FUNCTIONS
// ========================================

/**
 * Load available units for selected product
 */
function loadAvailableUnits(kodeBarangId, unitDasar) {
    $('#newSatuanBesar').empty();
    $('#newSatuanBesar').append(`<option value="">-- Pilih Satuan Besar --</option>`);
    
    if (kodeBarangId) {
        $.ajax({
            url: `{{ route('suratjalan.available-units', '') }}/${kodeBarangId}`,
            method: 'GET',
            success: function(units) {
                if (Array.isArray(units) && units.length > 0) {
                    let hasOtherUnits = false;
                    units.forEach(function(unit) {
                        if (unit !== unitDasar) {
                            $('#newSatuanBesar').append(`<option value="${unit}">${unit}</option>`);
                            hasOtherUnits = true;
                        }
                    });
                    
                    // Only auto-select if there are other units (not satuan dasar)
                    if (hasOtherUnits) {
                        const firstUnit = units.find(unit => unit !== unitDasar);
                        if (firstUnit) {
                            $('#newSatuanBesar').val(firstUnit);
                        }
                    }
                    // If no other units, leave dropdown empty (don't add satuan dasar)
                } else {
                    // If no units available, leave dropdown empty (don't add satuan dasar)
                }
                calculateNewItemTotal();
            },
            error: function() {
                // If error, leave dropdown empty (don't add satuan dasar)
                calculateNewItemTotal();
            }
        });
    } else {
        $('#newSatuanKecil').html('<option value=""></option>');
        $('#newSatuanBesar').html('<option value="">-- Pilih Satuan Besar --</option>');
        // $('#newSatuan').val('');
        calculateNewItemTotal();
    }
}

// ========================================
// ITEM MANAGEMENT FUNCTIONS
// ========================================

/**
 * Add new item to the list
 */
function addNewItem() {
    const kodeBarangId = $('#newKodeBarangId').val();
    const inputValue = $('#newKodeBarangInput').val();
    
    if (!kodeBarangId || !inputValue) {
        alert('Pilih barang terlebih dahulu!');
        return;
    }
    
    // Extract kode and nama from input value
    const parts = inputValue.split(' - ');
    const kodeBarang = parts[0] || '';
    const namaBarang = parts[1] || '';
    
    // Get additional data from stored selection
    const selectedBarang = $('#newKodeBarangInput').data('selectedBarang') || {};
    const merek = selectedBarang.merek || '';
    const ukuran = selectedBarang.ukuran || '';
        
    const qty = parseFloat($('#newQty').val());
    const satuanKecil = $('#newSatuanKecil').val();
    const satuanBesar = $('#newSatuanBesar').val() || null; // Set to null if empty
    const harga = parseFloat($('#newHarga').val()) || 0;
    const diskon = parseFloat($('#newDiskon').val()) || 0;
    const ongkosKuli = parseFloat($('#newOngkosKuli').val()) || 0;
    const keterangan = $('#newKeterangan').val().trim();

    // Validation
    if (!kodeBarang || !namaBarang || !qty || qty <= 0 || !satuanKecil) {
        alert('Silakan lengkapi semua field yang wajib (Kode Barang, Qty, Satuan Kecil)');
        return;
    }

    // Calculate total
    const subtotal = harga * qty;
    const diskonAmount = (subtotal * diskon) / 100;
    const total = subtotal - diskonAmount;
    const qtyInBaseUnit = qty; // Di mode satuan kecil, qty sudah dalam unit dasar
    const displayQty = qty;
    const displaySatuan = satuanKecil; // Selalu gunakan satuan kecil di mode ini

    
    // Add item to array
    items.push({
        kode_barang: kodeBarang,
        nama_barang: namaBarang,
        merek: merek,
        ukuran: ukuran,
        keterangan: keterangan,
        harga: harga,
        qty: qtyInBaseUnit, // Qty dalam unit dasar untuk backend
        qtyDisplay: displayQty, // Qty untuk display
        satuan: satuanKecil, // Satuan dasar untuk backend
        satuanDisplay: displaySatuan, // Satuan untuk display
        satuan_besar: '', // Mode satuan kecil tidak pakai satuan besar
        diskon: diskon,
        ongkos_kuli: ongkosKuli,
        total: total
    });

    // Update display and clear form
    updateItemsTable();
    clearItemForm();
}

/**
 * Add new item from LARGE mode (direct satuan besar input)
 */
function addNewItemLarge() {
    const kodeBarangId = $('#newKodeBarangIdLarge').val();
    const selectedOption = $('#newKodeBarangSelectLarge').find('option:selected');
    
    if (!kodeBarangId) {
        alert('Pilih barang terlebih dahulu!');
        return;
    }
    
    const kodeBarang = selectedOption.data('kode') || '';
    const namaBarang = selectedOption.data('nama') || '';
    const merek = selectedOption.data('merek') || '';
    const ukuran = selectedOption.data('ukuran') || '';
    const unitDasar = selectedOption.data('unit-dasar') || 'PCS';
    
    const qty = parseFloat($('#newQtyLarge').val());
    const satuanBesar = $('#newSatuanBesarLarge').val();
    const harga = parseFloat($('#newHargaLarge').val()) || 0;
    const diskon = parseFloat($('#newDiskonLarge').val()) || 0;
    const ongkosKuli = parseFloat($('#newOngkosKuliLarge').val()) || 0;
    const keterangan = $('#newKeteranganLarge').val().trim();

    // Validation
    if (!kodeBarang || !namaBarang || !qty || qty <= 0 || !satuanBesar) {
        alert('Silakan lengkapi semua field yang wajib (Barang, Qty, Satuan Besar)');
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
            // alert(`DEBUG KONVERSI (SURAT JALAN):\n\nQty Input: ${qty} ${satuanBesar}\nFactor Konversi: ${factor}\nHasil: ${qtyInBaseUnit} ${unitDasar}\n\nResponse API:\n${JSON.stringify(response, null, 2)}`);
            const subtotal = harga * qtyInBaseUnit;
            const diskonAmount = (subtotal * diskon) / 100;
            const total = subtotal - diskonAmount;
            
            // Prepare item object
            const itemToAdd = {
                kode_barang: kodeBarang,
                nama_barang: namaBarang,
                merek: merek,
                ukuran: ukuran,
                keterangan: keterangan,
                harga: harga,
                qty: qtyInBaseUnit, // Qty dalam unit dasar untuk backend
                qtyDisplay: qty, // Qty yang diinput user
                satuan: unitDasar, // Satuan dasar untuk backend
                satuanDisplay: satuanBesar, // Satuan yang dipilih user untuk display
                satuan_besar: satuanBesar,
                diskon: diskon,
                ongkos_kuli: ongkosKuli,
                total: total
            };
                        
            // Add item to array
            items.push(itemToAdd);

            // Update display and clear form
            updateItemsTable();
            clearItemFormLarge();
        },
        error: function() {
            alert('Gagal mendapatkan faktor konversi satuan. Pastikan satuan besar sudah dikonfigurasi.');
        }
    });
}

/**
 * Update items table display
 */
function updateItemsTable() {
    const tbody = $('#itemsList');
    tbody.empty();
    
    if (items.length === 0) {
        tbody.append('<tr><td colspan="10" class="text-center text-muted">Tidak ada barang</td></tr>');
        updateSummaryTotal();
        return;
    }
    
    items.forEach((item, index) => {
        // Display dengan qty dan satuan yang user input (bisa satuan besar)
        const displayQty = item.qtyDisplay || item.qty;
        const displaySatuan = item.satuanDisplay || item.satuan;
        
        // Debug log untuk melihat data item
        console.log('Display Item:', {
            qtyDisplay: item.qtyDisplay,
            qty: item.qty,
            satuan: item.satuan,
            satuanDisplay: item.satuanDisplay,
            satuan_besar: item.satuan_besar
        });
        
        // Format display: "2 BOX (20 PAIR)" jika ada konversi
        let qtyDisplay;
        if (item.satuan_besar && item.qtyDisplay && item.qty !== item.qtyDisplay) {
            // Ada konversi: tampilkan input user + hasil konversi
            qtyDisplay = `${item.qtyDisplay} ${item.satuanDisplay} (${Math.round(item.qty)} ${item.satuan})`;
        } else {
            // Tidak ada konversi atau satuan kecil
            qtyDisplay = `${displayQty} ${displaySatuan}`;
        }
        
        const satuanBesarDisplay = item.satuan_besar ? 'Ya' : '-';
        
        const row = `
            <tr>
                <td>${item.kode_barang || 'N/A'}</td>
                <td>${item.nama_barang || 'N/A'}</td>
                <td>${item.merek || '-'}</td>
                <td>${item.ukuran || '-'}</td>
                <td>${item.keterangan || '-'}</td>
                <td class="text-right">${formatCurrency(item.harga || 0)}</td>
                <td class="text-center">${qtyDisplay}</td>
                <td class="text-center">${satuanBesarDisplay}</td>
                <td class="text-right">${formatCurrency(item.total || 0)}</td>
                <td class="text-right">${formatCurrency(item.ongkos_kuli || 0)}</td>
                <td class="text-right">${item.diskon || 0}%</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-item" data-index="${index}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    updateSummaryTotal();
}

/**
 * Clear item form
 */
function clearItemForm() {
    $('#newKodeBarangInput').val('');
    $('#newKodeBarangId').val('');
    $('#newQty').val('');
    $('#newSatuanKecil').empty().append('<option value=""></option>');
    $('#newSatuanBesar').empty();
    $('#newHarga').val('');
    $('#newTotal').val('');
    $('#newDiskon').val('');
    $('#newOngkosKuli').val('');
    $('#newKeterangan').val('');
    $('#newKodeBarangInput').removeData('selectedBarang');
    $('#newKodeBarangSelect').val(null).trigger('change');
}

/**
 * Clear large item input form
 */
function clearItemFormLarge() {
    $('#newKodeBarangSelectLarge').val(null).trigger('change');
    $('#newKodeBarangIdLarge').val('');
    $('#newQtyLarge').val('');
    $('#newSatuanBesarLarge').empty();
    $('#newSatuanLarge').val('');
    $('#newHargaLarge').val('');
    $('#newTotalLarge').val('');
    $('#newDiskonLarge').val('');
    $('#newOngkosKuliLarge').val('');
    $('#newKeteranganLarge').val('');
}

// ========================================
// STOCK INFO FUNCTIONS
// ========================================

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

// ========================================
// CALCULATION FUNCTIONS
// ========================================

/**
 * Update summary total with PPN calculation
 */
function updateSummaryTotal() {
    let subtotal = 0;
    
    // Calculate subtotal from all items
    items.forEach(item => {
        subtotal += parseFloat(item.total || 0);
    });
    
    // Calculate PPN
    let ppnAmount = 0;
    if ($('#ppn_checkbox_sj').is(':checked')) {
        const ppnRate = {{ $ppnConfig['rate'] ?? 11 }};
        ppnAmount = (subtotal * ppnRate) / 100;
    }
    
    // Calculate grand total
    const grandTotal = subtotal + ppnAmount;
    
    // Update display
    $('#summary_subtotal').text('Rp ' + formatCurrency(subtotal));
    $('#summary_ppn').text('Rp ' + formatCurrency(ppnAmount));
    $('#summary_grand_total').text('Rp ' + formatCurrency(grandTotal));
}

// ========================================
// SAVE FUNCTIONS
// ========================================

/**
 * Save surat jalan
 */
function saveSuratJalan() {
    if (items.length === 0) {
        alert('Tidak ada barang yang ditambahkan!');
        return;
    }

    const formData = {
        no_suratjalan: $('#no_suratjalan').val(),
        tanggal: $('#tanggal').val(),
        kode_customer: $('#kode_customer').val(),
        alamat_suratjalan: $('#alamat_suratjalan').val(),
        no_po: $('#no_po').val(),
        titipan_uang: $('#titipan_uang').val(),
        sisa_piutang: $('#sisa_piutang').val(),
        metode_pembayaran: $('#metode_pembayaran').val(),
        cara_bayar: $('#cara_bayar').val(),
        hari_tempo: parseInt($('#hari_tempo').val() || '0', 10),
        tanggal_jatuh_tempo: $('#tanggal_jatuh_tempo').val(),
        items: items,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: "{{ route('suratjalan.store') }}",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                // Tampilkan modal print
                $('#suratJalanNo').text(response.data.no_suratjalan || 'N/A');
                $('#suratJalanTanggal').text(response.data.tanggal || 'N/A');
                $('#suratJalanCustomer').text(response.data.customer_name || 'N/A');
                $('#suratJalanAlamat').text(response.data.alamat_suratjalan || 'N/A');
                $('#suratJalanTotalItem').text(response.data.items_count || items.length);

                // Simpan ID surat jalan untuk tombol Print
                const suratJalanId = response.data.id;

                // Tombol Print Standard
                $('#printSuratJalanBtn').off('click').on('click', function() {
                    const printUrl = `{{ url('suratjalan/print') }}/${suratJalanId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                // Tombol Print Format Kecil
                $('#printSuratJalanKecilBtn').off('click').on('click', function(e) {
                    e.preventDefault();
                    const printUrl = `{{ url('suratjalan/print-kecil') }}/${suratJalanId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                // Tombol Print Format Besar
                $('#printSuratJalanBesarBtn').off('click').on('click', function(e) {
                    e.preventDefault();
                    const printUrl = `{{ url('suratjalan/print-besar') }}/${suratJalanId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                // Tombol Kembali
                $('#backToSuratJalanFormBtn').off('click').on('click', function() {
                    $('#printSuratJalanModal').modal('hide');
                    $('#suratjalanForm')[0].reset();
                    items = [];
                    updateItemsTable();
                    updateSummaryTotal();
                    window.location.href = "{{ route('suratjalan.create') }}";
                });

                $('#printSuratJalanModal').modal('show');
            } else {
                alert('Gagal menyimpan Surat Jalan: ' + (response.message || response.error || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving surat jalan:', error);
            alert('Terjadi kesalahan saat menyimpan Surat Jalan.');
        }
    });
}

/**
 * Reset form
 */
function resetForm() {
    items = [];
    updateItemsTable();
    $('#suratjalanForm')[0].reset();
    $('#newKodeBarangInput').val('');
    $('#newKodeBarangId').val('');
    $('#newSatuanKecil').empty().append('<option value=""></option>');
    $('#newSatuanBesar').empty();
    $('#newKodeBarangInput').removeData('selectedBarang');
}

// ========================================
// SEARCH FUNCTIONALITY
// ========================================

// Real-time search for barang input
$('#newKodeBarangInput').on('input', function() {
    const keyword = $(this).val();
    const dropdown = $('#barangDropdownSuratJalan');
    
    if (keyword.length >= 2) {
        $.ajax({
            url: "{{ route('kodeBarang.search') }}",
            method: 'GET',
            data: { keyword },
            success: function(data) {
                let dropdownHtml = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        dropdownHtml += `<a class="dropdown-item barang-item-suratjalan" 
                            data-id="${item.id}"
                            data-kode="${item.kode_barang}"
                            data-nama="${item.name}"
                            data-harga="${item.cost || 0}"
                            data-unit-dasar="${item.unit_dasar || 'PCS'}"
                            data-merek="${item.merek || ''}"
                            data-ukuran="${item.ukuran || ''}"
                            href="#">
                            ${item.kode_barang} - ${item.name}${item.merek || item.ukuran ? ` (${item.merek || '-'}${item.merek && item.ukuran ? ', ' : ''}${item.ukuran || '-'})` : ''}
                        </a>`;
                    });
                } else {
                    dropdownHtml = '<a class="dropdown-item disabled">Tidak ada barang ditemukan</a>';
                }
                dropdown.html(dropdownHtml).show();
            },
            error: function() {
                dropdown.html('<a class="dropdown-item disabled">Error saat mencari</a>').show();
            }
        });
    } else {
        dropdown.hide();
    }
});

// Select barang from dropdown
$(document).on('click', '.barang-item-suratjalan', function(e) {
    e.preventDefault();
    const input = $('#newKodeBarangInput');
    const hiddenId = $('#newKodeBarangId');
    
    const id = $(this).data('id');
    const kode = $(this).data('kode');
    const nama = $(this).data('nama');
    const harga = $(this).data('harga');
    const unitDasar = $(this).data('unit-dasar');
    const merek = $(this).data('merek');
    const ukuran = $(this).data('ukuran');
    
    // Set input value and hidden id
    input.val(`${kode} - ${nama}`);
    hiddenId.val(id);
    
    // Store selected barang data
    $('#newKodeBarangInput').data('selectedBarang', {
        id: id,
        kode: kode,
        nama: nama,
        harga: harga,
        unitDasar: unitDasar,
        merek: merek,
        ukuran: ukuran
    });
    
    // Hide dropdown
    $('#barangDropdownSuratJalan').hide();
    
    // Set harga and satuan
    $('#newHarga').val(harga);
    $('#newSatuanKecil').empty().append(`<option value="${unitDasar}">${unitDasar}</option>`);
    $('#newSatuan').val(unitDasar);
    
    // Load available units
    loadAvailableUnits(id, unitDasar);
    
    // Calculate total
    calculateNewItemTotal();
});

// Open search modal
$('#searchBarangBtnSuratJalan').click(function() {
    $('#searchKodeBarangInputSuratJalan').val('');
    $('#kodeBarangSearchResultsSuratJalan').html('<tr><td colspan="5" class="text-center">Masukkan kata kunci untuk mencari barang</td></tr>');
    $('#kodeBarangSearchModalSuratJalan').modal('show');
});

// Modal search
$('#searchKodeBarangBtnSuratJalan').click(function() {
    const keyword = $('#searchKodeBarangInputSuratJalan').val();
    if (keyword.length > 0) {
        $.ajax({
            url: "{{ route('kodeBarang.search') }}",
            method: 'GET',
            data: { keyword },
            success: function(data) {
                let html = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        html += `<tr>
                            <td>${item.kode_barang}</td>
                            <td>${item.name}</td>
                            <td>${formatCurrency(item.cost || 0)}</td>
                            <td>${item.remaining_stock || 0} ${item.stock_unit || 'PCS'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary select-kode-barang-suratjalan"
                                    data-id="${item.id}"
                                    data-kode="${item.kode_barang}"
                                    data-name="${item.name}"
                                    data-harga="${item.cost || 0}"
                                    data-unit-dasar="${item.unit_dasar || 'PCS'}"
                                    data-merek="${item.merek || ''}"
                                    data-ukuran="${item.ukuran || ''}">
                                    <i class="fas fa-check"></i> Pilih
                                </button>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center">Tidak ada data ditemukan</td></tr>';
                }
                $('#kodeBarangSearchResultsSuratJalan').html(html);
            },
            error: function() {
                alert('Terjadi kesalahan saat mencari kode barang.');
            }
        });
    } else {
        alert('Masukkan kata kunci pencarian!');
    }
});

// Select barang from modal
$(document).on('click', '.select-kode-barang-suratjalan', function() {
    const input = $('#newKodeBarangInput');
    const hiddenId = $('#newKodeBarangId');
    
    const id = $(this).data('id');
    const kode = $(this).data('kode');
    const nama = $(this).data('name');
    const harga = $(this).data('harga');
    const unitDasar = $(this).data('unit-dasar');
    const merek = $(this).data('merek');
    const ukuran = $(this).data('ukuran');
    
    // Set input value and hidden id
    input.val(`${kode} - ${nama}`);
    hiddenId.val(id);
    
    // Store selected barang data
    $('#newKodeBarangInput').data('selectedBarang', {
        id: id,
        kode: kode,
        nama: nama,
        harga: harga,
        unitDasar: unitDasar,
        merek: merek,
        ukuran: ukuran
    });
    
    // Set harga and satuan
    $('#newHarga').val(harga);
    $('#newSatuanKecil').empty().append(`<option value="${unitDasar}">${unitDasar}</option>`);
    $('#newSatuan').val(unitDasar);
    
    // Load available units
    loadAvailableUnits(id, unitDasar);
    
    // Calculate total
    calculateNewItemTotal();
    
    // Close modal
    $('#kodeBarangSearchModalSuratJalan').modal('hide');
});

// Hide dropdown when clicking outside
$(document).click(function(e) {
    if (!$(e.target).closest('#newKodeBarangInput, #barangDropdownSuratJalan').length) {
        $('#barangDropdownSuratJalan').hide();
    }
});

// ========================================
// INITIALIZATION
// ========================================

// Auto-check PPN if enabled in company settings
@if($ppnConfig['enabled'] ?? false)
    $('#ppn_checkbox_sj').prop('checked', true);
@endif

// No PO auto-generation; user inputs PO manually

// Sync metode_pembayaran with selected cara_bayar's metode and toggle tempo fields
$('#cara_bayar').on('change', function() {
    const metode = $('#cara_bayar option:selected').data('metode');
    if (metode) {
        $('#metode_pembayaran').val(metode);
    }

    // If metode is Kredit/Non Tunai show tempo fields, if Tunai hide and reset
    const isCredit = (metode && metode.toLowerCase() !== 'tunai');
    if (isCredit) {
        $('#hariTempoGroup').show();
        $('#jatuhTempoGroup').show();
    } else {
        $('#hariTempoGroup').hide();
        $('#jatuhTempoGroup').hide();
        $('#hari_tempo').val(0);
        $('#tanggal_jatuh_tempo').val('');
    }
}).trigger('change');

// Filter cara_bayar options based on selected metode_pembayaran
let originalCaraBayarOptions = null;
function ensureOriginalCaraBayarOptions() {
    if (!originalCaraBayarOptions) {
        originalCaraBayarOptions = $('#cara_bayar option').clone();
    }
}

function filterCaraBayarOptionsByMetode() {
    ensureOriginalCaraBayarOptions();
    const selectedMetode = ($('#metode_pembayaran').val() || '').toLowerCase();
    const currentValue = $('#cara_bayar').val();

    const filtered = originalCaraBayarOptions.filter(function() {
        const m = ($(this).data('metode') || '').toLowerCase();
        if (selectedMetode === 'tunai') {
            return m === 'tunai';
        }
        // Non Tunai: exclude Tunai
        return m !== 'tunai';
    });

    $('#cara_bayar').empty().append(filtered);

    // Try to preserve previous selection if still valid; else select first
    if (currentValue && $('#cara_bayar option[value="' + currentValue + '"]').length) {
        $('#cara_bayar').val(currentValue);
    } else {
        const firstVal = $('#cara_bayar option:first').val();
        if (firstVal) {
            $('#cara_bayar').val(firstVal);
        }
    }

    // Trigger change to sync tempo visibility
    $('#cara_bayar').trigger('change');
}

$('#metode_pembayaran').on('change', function() {
    filterCaraBayarOptionsByMetode();
});

// Initial filter on load
filterCaraBayarOptionsByMetode();
</script>

<!-- Kode Barang Search Modal -->
<div class="modal fade" id="kodeBarangSearchModalSuratJalan" tabindex="-1" role="dialog" aria-labelledby="kodeBarangSearchModalSuratJalanLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kodeBarangSearchModalSuratJalanLabel">Cari Kode Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="searchKodeBarangInputSuratJalan">Masukkan kata kunci pencarian:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchKodeBarangInputSuratJalan" placeholder="Ketik kode barang atau nama barang...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button" id="searchKodeBarangBtnSuratJalan">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Harga Jual</th>
                                <th>Stok Tersisa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kodeBarangSearchResultsSuratJalan">
                            <!-- Search results will be shown here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Print Surat Jalan -->
<div class="modal fade" id="printSuratJalanModal" tabindex="-1" role="dialog" aria-labelledby="printSuratJalanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printSuratJalanModalLabel">Print Surat Jalan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="suratJalanContent">
                    <h4>No Surat Jalan: <span id="suratJalanNo"></span></h4>
                    <p>Tanggal: <span id="suratJalanTanggal"></span></p>
                    <p>Customer: <span id="suratJalanCustomer"></span></p>
                    <p>Alamat: <span id="suratJalanAlamat"></span></p>
                    <p>Total Item: <span id="suratJalanTotalItem"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" id="printSuratJalanBtn">
                        <i class="fas fa-print"></i> Print Standard
                    </button>
                    <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#" id="printSuratJalanKecilBtn">
                            <i class="fas fa-file-alt"></i> Format Kecil
                        </a>
                        <a class="dropdown-item" href="#" id="printSuratJalanBesarBtn">
                            <i class="fas fa-file-alt"></i> Format Besar
                        </a>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" id="backToSuratJalanFormBtn">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
            </div>
        </div>
    </div>
</div>

@endsection