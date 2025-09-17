@extends('layout.Nav')

@section('content')
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

    <!-- Add Item Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Tambah Barang</h5>
        </div>
        <div class="card-body">
            <!-- Product Selection Row -->
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Kode Barang</label>
                        <select class="form-control" id="newKodeBarang">
                            <option value="">-- Pilih Barang --</option>
                            @if(isset($kodeBarangs) && count($kodeBarangs) > 0)
                                @foreach($kodeBarangs as $barang)
                                    <option value="{{ $barang->id }}" 
                                            data-harga="{{ $barang->harga_jual }}"
                                            data-unit-dasar="{{ $barang->unit_dasar }}"
                                            data-unit-turunan="{{ $barang->unit_turunan }}"
                                            data-kode="{{ $barang->kode_barang }}"
                                            data-nama="{{ $barang->name }}"
                                            data-merek="{{ $barang->merek }}"
                                            data-ukuran="{{ $barang->ukuran }}">
                                        {{ $barang->kode_barang }} - {{ $barang->name }}
                                        @if($barang->merek || $barang->ukuran)
                                            ({{ $barang->merek ?? '-' }}@if($barang->merek && $barang->ukuran), @endif{{ $barang->ukuran ?? '-' }})
                                        @endif
                                    </option>
                                @endforeach
                            @else
                                <option value="">Tidak ada data barang</option>
                            @endif
                        </select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Qty</label>
                        <input type="number" 
                               class="form-control" 
                               id="newQty" 
                               step="0.01" 
                               min="0.01">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Satuan Kecil</label>
                        <select class="form-control" id="newSatuanKecil">
                            <option value=""></option>
                        </select>
                        <input type="hidden" id="newSatuan" value="">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Satuan Besar</label>
                        <select class="form-control" id="newSatuanBesar"></select>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Harga</label>
                        <input type="number" 
                               class="form-control" 
                               id="newHarga" 
                               step="0.01" 
                               min="0">
                    </div>
                </div>
                
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Total</label>
                        <input type="number" 
                               class="form-control" 
                               id="newTotal" 
                               readonly>
                    </div>
                </div>
            </div>
            
            <!-- Additional Fields Row -->
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Diskon (%)</label>
                        <input type="number" 
                               class="form-control" 
                               id="newDiskon" 
                               placeholder="0" 
                               min="0" 
                               max="100">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Ongkos Kuli</label>
                        <input type="number" 
                               class="form-control" 
                               id="newOngkosKuli" 
                               placeholder="0">
                    </div>
                </div>
                
                <div class="col-md-7">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" 
                               class="form-control" 
                               id="newKeterangan" 
                               placeholder="Keterangan">
                    </div>
                </div>
                
                <div class="col-md-1 d-flex align-items-end justify-content-end">
                    <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                        <i class="fas fa-plus"></i> Add
                    </button>
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
                            <th>Ukuran</th>
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
            <div class="row mt-4">
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Ringkasan Total</h6>
                            
                            <!-- Subtotal -->
                            <div class="row">
                                <div class="col-6">
                                    <label>Subtotal:</label>
                                </div>
                                <div class="col-6 text-right">
                                    <span id="summary_subtotal">Rp 0</span>
                                </div>
                            </div>
                            
                            <!-- PPN -->
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="ppn_checkbox_sj">
                                        <label class="form-check-label" for="ppn_checkbox_sj">
                                            PPN ({{ $ppnConfig['rate'] ?? 11 }}%)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 text-right">
                                    <span id="summary_ppn">Rp 0</span>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Grand Total -->
                            <div class="row">
                                <div class="col-6">
                                    <strong>Grand Total:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <strong><span id="summary_grand_total">Rp 0</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="form-group text-right mt-4">
                <button type="button" class="btn btn-success" id="saveSuratJalan">
                    <i class="fas fa-save"></i> Simpan Surat Jalan
                </button>
                <button type="button" class="btn btn-warning" id="resetForm">
                    <i class="fas fa-times"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

<script>
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
 * Handle product selection change
 */
$('#newKodeBarang').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const harga = selectedOption.data('harga') || 0;
    const unitDasar = selectedOption.data('unit-dasar') || 'PCS';
    const merek = selectedOption.data('merek') || '';
    const ukuran = selectedOption.data('ukuran') || '';
    const kodeBarangId = $(this).val();
    
    // Set price
    $('#newHarga').val(harga);
    
    // Set small unit options
    $('#newSatuanKecil').empty().append(`<option value="${unitDasar}">${unitDasar}</option>`);
    $('#newSatuan').val(unitDasar);
    
    // Load available units for large unit
    loadAvailableUnits(kodeBarangId, unitDasar);
    // cache meta on selects for later add
    $('#newKodeBarang').data('merek', merek);
    $('#newKodeBarang').data('ukuran', ukuran);
});

/**
 * Handle quantity and price changes for total calculation
 */
$('#newQty, #newHarga, #newDiskon').on('input', function() {
    calculateNewItemTotal();
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
                    
                    // Auto-select first available unit
                    if (hasOtherUnits) {
                        const firstUnit = units.find(unit => unit !== unitDasar);
                        if (firstUnit) {
                            $('#newSatuanBesar').val(firstUnit);
                        }
                    } else {
                        $('#newSatuanBesar').append(`<option value="${unitDasar}">${unitDasar}</option>`);
                        $('#newSatuanBesar').val(unitDasar);
                    }
                } else {
                    $('#newSatuanBesar').append(`<option value="${unitDasar}">${unitDasar}</option>`);
                    $('#newSatuanBesar').val(unitDasar);
                }
                calculateNewItemTotal();
            },
            error: function() {
                $('#newSatuanBesar').append(`<option value="${unitDasar}">${unitDasar}</option>`);
                $('#newSatuanBesar').val(unitDasar);
                calculateNewItemTotal();
            }
        });
    } else {
        $('#newSatuanKecil').html('<option value=""></option>');
        $('#newSatuanBesar').html('<option value="">-- Pilih Satuan Besar --</option>');
        $('#newSatuan').val('');
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
    const selectedOption = $('#newKodeBarang option:selected');
    const kodeBarang = selectedOption.data('kode');
    const namaBarang = selectedOption.data('nama');
    const merek = $('#newKodeBarang').data('merek') || selectedOption.data('merek') || '';
    const ukuran = $('#newKodeBarang').data('ukuran') || selectedOption.data('ukuran') || '';
    const qty = parseFloat($('#newQty').val());
    const satuanKecil = $('#newSatuanKecil').val();
    const satuanBesar = $('#newSatuanBesar').val();
    const harga = parseFloat($('#newHarga').val()) || 0;
    const diskon = parseFloat($('#newDiskon').val()) || 0;
    const ongkosKuli = parseFloat($('#newOngkosKuli').val()) || 0;
    const keterangan = $('#newKeterangan').val().trim();

    // Validation
    if (!kodeBarang || !namaBarang || !qty || qty <= 0 || !satuanKecil) {
        alert('Silakan lengkapi semua field yang wajib (Kode Barang, Qty, Satuan Kecil)');
        return;
    }

    // Check for duplicate items
    const existingItem = items.find(item => item.kode_barang === kodeBarang);
    if (existingItem) {
        alert('Barang sudah ada dalam daftar!');
        return;
    }

    // Calculate total
    const subtotal = harga * qty;
    const diskonAmount = (subtotal * diskon) / 100;
    const total = subtotal - diskonAmount;

    // Add item to array
    items.push({
        kode_barang: kodeBarang,
        nama_barang: namaBarang,
        merek: merek,
        ukuran: ukuran,
        keterangan: keterangan,
        harga: harga,
        qty: qty,
        satuan: satuanKecil,
        satuan_besar: satuanBesar,
        diskon: diskon,
        ongkos_kuli: ongkosKuli,
        total: total
    });

    // Update display and clear form
    updateItemsTable();
    clearItemForm();
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
        const qtyDisplay = `${item.qty} ${item.satuan}`;
        const satuanBesarDisplay = item.satuan_besar || '-';
        
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
    $('#newKodeBarang').val('');
    $('#newQty').val('');
    $('#newSatuanKecil').empty().append('<option value=""></option>');
    $('#newSatuanBesar').empty();
    $('#newHarga').val('');
    $('#newTotal').val('');
    $('#newDiskon').val('');
    $('#newOngkosKuli').val('');
    $('#newKeterangan').val('');
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
                alert(response.message || 'Surat Jalan berhasil disimpan!');
                window.location.href = "{{ route('suratjalan.history') }}";
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
    $('#newKodeBarang').val('');
    $('#newSatuanKecil').empty().append('<option value=""></option>');
    $('#newSatuanBesar').empty();
}

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
@endsection