@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-truck mr-2"></i>Surat Jalan</h2>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Data Surat Jalan</h5>
        </div>
        <div class="card-body">
            <form id="suratjalanForm">
                @csrf
                <div class="row">
                    <!-- Kiri -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_suratjalan">No. Surat Jalan</label>
                            <input type="text" class="form-control" id="no_suratjalan" name="no_suratjalan" value="{{ $noSuratJalan ?? 'SJ-001-00001' }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
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
                            <label for="customer">Customer</label>
                            <div class="input-group">
                                <input type="text" id="customer_display" name="customer_display" class="form-control" placeholder="Masukkan nama customer">
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
                            <label for="alamatCustomer">Alamat Customer</label>
                            <input type="text" id="alamatCustomer" name="customer-alamat" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="hpCustomer">No HP / Telp Customer</label>
                            <input type="text" id="hpCustomer" name="customer-hp" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="alamat_suratjalan">Alamat di Surat Jalan</label>
                            <textarea class="form-control" id="alamat_suratjalan" name="alamat_suratjalan" rows="2" placeholder="Alamat pengiriman"></textarea>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="select_faktur">Pilih Faktur (Opsional)</label>
                            <select class="form-control" id="select_faktur" name="select_faktur">
                                <option value="">-- Pilih Faktur --</option>
                                @foreach($availableTransactions as $transaction)
                                    <option value="{{ $transaction->id }}" 
                                            data-no-transaksi="{{ $transaction->no_transaksi }}"
                                            data-kode-customer="{{ $transaction->kode_customer }}"
                                            data-customer-name="{{ $transaction->customer->nama ?? 'N/A' }}"
                                            data-customer-alamat="{{ $transaction->customer->alamat ?? 'N/A' }}"
                                            data-customer-hp="{{ $transaction->customer->hp ?? 'N/A' }}"
                                            data-customer-telp="{{ $transaction->customer->telepon ?? 'N/A' }}"
                                            data-tanggal="{{ $transaction->tanggal }}"
                                            data-grand-total="{{ $transaction->grand_total }}">
                                        {{ $transaction->no_transaksi }} - {{ $transaction->customer->nama ?? 'N/A' }} ({{ \Carbon\Carbon::parse($transaction->tanggal)->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih faktur untuk mengisi otomatis data customer dan items</small>
                        </div>

                        <div class="form-group">
                            <label for="no_transaksi">No. Faktur</label>
                            <input type="text" id="no_transaksi" name="no_transaksi" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_transaksi">Tanggal Faktur</label>
                            <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" readonly>
                        </div>

                        <div class="form-group">
                            <label for="titipan_uang">Titipan Uang</label>
                            <input type="number" class="form-control" id="titipan_uang" name="titipan_uang" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label for="sisa_piutang">Sisa Piutang</label>
                            <input type="number" class="form-control" id="sisa_piutang" name="sisa_piutang" value="0" min="0">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Tambah Barang</h5>
        </div>
        <div class="card-body">
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
                                        {{ $barang->kode_barang }} - {{ $barang->name }}@if($barang->merek || $barang->ukuran) ({{ $barang->merek ?? '-' }}@if($barang->merek && $barang->ukuran), @endif{{ $barang->ukuran ?? '-' }})@endif
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
                        <input type="number" class="form-control" id="newQty" step="0.01" min="0.01">
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
                        <input type="number" class="form-control" id="newHarga" step="0.01" min="0">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>Total</label>
                        <input type="number" class="form-control" id="newTotal" readonly>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Diskon (%)</label>
                        <input type="number" class="form-control" id="newDiskon" placeholder="0" min="0" max="100">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Ongkos Kuli</label>
                        <input type="number" class="form-control" id="newOngkosKuli" placeholder="0">
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" class="form-control" id="newKeterangan" placeholder="Keterangan">
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

    <!-- Items Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Barang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
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
let items = [];

// Format currency function
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Handle kode barang selection change
$('#newKodeBarang').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const harga = selectedOption.data('harga') || 0;
    const unitDasar = selectedOption.data('unit-dasar') || 'PCS';
    const unitTurunan = selectedOption.data('unit-turunan') || '';
    
    // Set harga
    $('#newHarga').val(harga);
    
    // Set satuan kecil options
    $('#newSatuanKecil').empty().append(`<option value="${unitDasar}">${unitDasar}</option>`);
    $('#newSatuan').val(unitDasar);
    
    // Set satuan besar options
    $('#newSatuanBesar').empty();
    if (unitTurunan && unitTurunan !== unitDasar) {
        $('#newSatuanBesar').append(`<option value="${unitTurunan}">${unitTurunan}</option>`);
    }
    
    // Calculate total
    calculateNewItemTotal();
});

// Handle qty and harga changes for total calculation
$('#newQty, #newHarga, #newDiskon').on('input', function() {
    calculateNewItemTotal();
});

// Calculate total for new item
function calculateNewItemTotal() {
    const qty = parseFloat($('#newQty').val()) || 0;
    const harga = parseFloat($('#newHarga').val()) || 0;
    const diskon = parseFloat($('#newDiskon').val()) || 0;
    
    const subtotal = qty * harga;
    const diskonAmount = (subtotal * diskon) / 100;
    const total = subtotal - diskonAmount;
    
    $('#newTotal').val(total.toFixed(2));
}

// Add item button handler
$('#addItemBtn').on('click', function() {
    const selectedOption = $('#newKodeBarang option:selected');
    const kodeBarang = selectedOption.data('kode');
    const namaBarang = selectedOption.data('nama');
    const qty = parseFloat($('#newQty').val());
    const satuanKecil = $('#newSatuanKecil').val();
    const satuanBesar = $('#newSatuanBesar').val();
    const harga = parseFloat($('#newHarga').val()) || 0;
    const diskon = parseFloat($('#newDiskon').val()) || 0;
    const ongkosKuli = parseFloat($('#newOngkosKuli').val()) || 0;
    const keterangan = $('#newKeterangan').val().trim();

    if (!kodeBarang || !namaBarang || !qty || qty <= 0 || !satuanKecil) {
        alert('Silakan lengkapi semua field yang wajib (Kode Barang, Qty, Satuan Kecil)');
        return;
    }

    // Check if item already exists
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
        keterangan: keterangan,
        harga: harga,
        qty: qty,
        satuan: satuanKecil,
        satuan_besar: satuanBesar,
        diskon: diskon,
        ongkos_kuli: ongkosKuli,
        total: total
    });

    // Update table
    updateItemsTable();

    // Clear form
    $('#newKodeBarang').val('');
    $('#newQty').val('');
    $('#newSatuanKecil').empty().append('<option value=""></option>');
    $('#newSatuanBesar').empty();
    $('#newHarga').val('');
    $('#newTotal').val('');
    $('#newDiskon').val('');
    $('#newOngkosKuli').val('');
    $('#newKeterangan').val('');
});

// Update items table display
function updateItemsTable() {
    const tbody = $('#itemsList');
    tbody.empty();
    
    if (items.length === 0) {
        tbody.append('<tr><td colspan="10" class="text-center text-muted">Tidak ada barang</td></tr>');
        return;
    }
    
    items.forEach((item, index) => {
        const qtyDisplay = `${item.qty} ${item.satuan}`;
        const satuanBesarDisplay = item.satuan_besar || '-';
        
        const row = `
            <tr>
                <td>${item.kode_barang || 'N/A'}</td>
                <td>${item.nama_barang || 'N/A'}</td>
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
}

// Remove item button handler
$(document).on('click', '.remove-item', function() {
    const index = $(this).data('index');
    if (confirm('Hapus barang ini?')) {
        items.splice(index, 1);
        updateItemsTable();
    }
});

// Handle faktur selection
$('#select_faktur').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    if (selectedOption.val()) {
        // Fill customer data
        $('#customer_display').val(`${selectedOption.data('kode-customer')} - ${selectedOption.data('customer-name')}`);
        $('#kode_customer').val(selectedOption.data('kode-customer'));
        $('#alamatCustomer').val(selectedOption.data('customer-alamat'));
        $('#hpCustomer').val(`${selectedOption.data('customer-hp')} / ${selectedOption.data('customer-telp')}`);
        $('#alamat_suratjalan').val(selectedOption.data('customer-alamat'));
        $('#no_transaksi').val(selectedOption.data('no-transaksi'));
        $('#tanggal_transaksi').val(selectedOption.data('tanggal'));
        
        // Load transaction items
        loadTransactionItems(selectedOption.val());
    } else {
        // Clear customer data
        $('#customer_display').val('');
        $('#kode_customer').val('');
        $('#alamatCustomer').val('');
        $('#hpCustomer').val('');
        $('#alamat_suratjalan').val('');
        $('#no_transaksi').val('');
        $('#tanggal_transaksi').val('');
        
        // Clear items
        items = [];
        updateItemsTable();
    }
});

// Load transaction items
function loadTransactionItems(transaksiId) {
    $.ajax({
        url: "{{ url('api/suratjalan/transaksiitem/') }}/" + transaksiId,
        method: "GET",
        success: function(response) {
            console.log('Transaction items response:', response);
            
            items = [];
            response.forEach(function(item, index) {
                items.push({
                    kode_barang: item.kode_barang,
                    nama_barang: item.keterangan,
                    keterangan: item.keterangan,
                    harga: item.harga,
                    qty: item.qty,
                    satuan: item.satuan,
                    satuan_besar: '',
                    diskon: item.diskon,
                    ongkos_kuli: item.ongkos_kuli,
                    total: item.total
                });
            });
            
            updateItemsTable();
        },
        error: function(xhr, status, error) {
            console.error('Error fetching transaction items:', error);
            alert('Terjadi kesalahan saat mengambil detail transaksi.');
        }
    });
}

// Save surat jalan
$('#saveSuratJalan').on('click', function() {
    if (items.length === 0) {
        alert('Tidak ada barang yang ditambahkan!');
        return;
    }

    const formData = {
        no_suratjalan: $('#no_suratjalan').val(),
        tanggal: $('#tanggal').val(),
        kode_customer: $('#kode_customer').val(),
        alamat_suratjalan: $('#alamat_suratjalan').val(),
        no_transaksi: $('#no_transaksi').val(),
        tanggal_transaksi: $('#tanggal_transaksi').val(),
        titipan_uang: $('#titipan_uang').val(),
        sisa_piutang: $('#sisa_piutang').val(),
        items: items,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: "{{ route('suratjalan.store') }}",
        method: "POST",
        data: formData,
        success: function(response) {
            if (response.success) {
                alert('Surat Jalan berhasil disimpan!');
                window.location.href = "{{ route('suratjalan.history') }}";
            } else {
                alert('Gagal menyimpan Surat Jalan: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving surat jalan:', error);
            alert('Terjadi kesalahan saat menyimpan Surat Jalan.');
        }
    });
});

// Reset form
$('#resetForm').on('click', function() {
    if (confirm('Apakah Anda yakin ingin mereset form? Semua data yang sudah diisi akan hilang.')) {
        items = [];
        updateItemsTable();
        $('#suratjalanForm')[0].reset();
        $('#newKodeBarang').val('');
        $('#newSatuanKecil').empty().append('<option value=""></option>');
        $('#newSatuanBesar').empty();
    }
});
</script>
@endsection
