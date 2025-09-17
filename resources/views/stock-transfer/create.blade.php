@extends('layout.Nav')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="title-box">
        <h2><i class="fas fa-plus mr-2"></i>Buat Transfer Stok</h2>
    </div>

    <form id="transferForm" method="POST" action="{{ route('stock-transfer.store') }}">
        @csrf
        
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Transfer</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal_transfer">Tanggal Transfer <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('tanggal_transfer') is-invalid @enderror" 
                                   id="tanggal_transfer" 
                                   name="tanggal_transfer" 
                                   value="{{ old('tanggal_transfer', date('Y-m-d')) }}" 
                                   required>
                            @error('tanggal_transfer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="from_database">Dari Database <span class="text-danger">*</span></label>
                            <select class="form-control @error('from_database') is-invalid @enderror" 
                                    id="from_database" 
                                    name="from_database" 
                                    required>
                                <option value="">-- Pilih Database Sumber --</option>
                                @foreach($databases as $key => $name)
                                    <option value="{{ $key }}" {{ old('from_database') == $key ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('from_database')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="to_database">Ke Database <span class="text-danger">*</span></label>
                            <select class="form-control @error('to_database') is-invalid @enderror" 
                                    id="to_database" 
                                    name="to_database" 
                                    required>
                                <option value="">-- Pilih Database Tujuan --</option>
                                @foreach($databases as $key => $name)
                                    <option value="{{ $key }}" {{ old('to_database') == $key ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_database')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="2" 
                                      placeholder="Masukkan keterangan transfer...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Items Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tambah Item Transfer</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="product_select">Pilih Produk <span class="text-danger">*</span></label>
                            <select class="form-control" id="product_select">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->kode_barang }}" 
                                            data-nama="{{ $product->name }}"
                                            data-satuan="{{ $product->unit_dasar }}"
                                            data-harga="{{ $product->harga_jual }}">
                                        {{ $product->kode_barang }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="qty_transfer">Qty Transfer <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="qty_transfer" 
                                   step="0.01" 
                                   min="0.01" 
                                   placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="harga_per_unit">Harga/Unit <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="harga_per_unit" 
                                   step="0.01" 
                                   min="0.01" 
                                   placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="total_value">Total Value</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="total_value" 
                                   readonly 
                                   placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-success btn-block" id="addItemBtn">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="item_keterangan">Keterangan Item</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item_keterangan" 
                                   placeholder="Keterangan item (opsional)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items List -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Daftar Item Transfer</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Harga/Unit</th>
                                <th>Total Value</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsList">
                            <tr id="emptyRow">
                                <td colspan="8" class="text-center text-muted">Belum ada item yang ditambahkan</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <!-- Stock Information -->
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Informasi Stok</h6>
                                <div id="stockInfo">
                                    <p class="text-muted">Pilih produk untuk melihat informasi stok</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Ringkasan Transfer</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <label>Jumlah Item:</label>
                                    </div>
                                    <div class="col-6 text-right">
                                        <span id="summary_item_count">0</span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label>Total Value:</label>
                                    </div>
                                    <div class="col-6 text-right">
                                        <strong><span id="summary_total_value">Rp 0</span></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-group text-right">
            <a href="{{ route('stock-transfer.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-success" id="submitBtn" disabled>
                <i class="fas fa-save"></i> Simpan Transfer
            </button>
        </div>
    </form>
</div>

<script>
let transferItems = [];

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

// Handle product selection
$('#product_select').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const harga = selectedOption.data('harga') || 0;
    const kodeBarang = selectedOption.val();
    
    $('#harga_per_unit').val(harga);
    calculateTotal();
    
    // Load stock information
    if (kodeBarang) {
        loadStockInfo(kodeBarang);
    } else {
        $('#stockInfo').html('<p class="text-muted">Pilih produk untuk melihat informasi stok</p>');
    }
});

// Handle quantity and price changes
$('#qty_transfer, #harga_per_unit').on('input', function() {
    calculateTotal();
});

// Calculate total value
function calculateTotal() {
    const qty = parseFloat($('#qty_transfer').val()) || 0;
    const harga = parseFloat($('#harga_per_unit').val()) || 0;
    const total = qty * harga;
    
    $('#total_value').val(total.toFixed(2));
}

// Add item to transfer
$('#addItemBtn').on('click', function() {
    const productSelect = $('#product_select');
    const selectedOption = productSelect.find('option:selected');
    
    if (!selectedOption.val()) {
        alert('Pilih produk terlebih dahulu');
        return;
    }
    
    const qty = parseFloat($('#qty_transfer').val());
    if (!qty || qty <= 0) {
        alert('Masukkan quantity yang valid');
        return;
    }
    
    const harga = parseFloat($('#harga_per_unit').val()) || 0;
    const total = qty * harga;
    const keterangan = $('#item_keterangan').val().trim();
    
    // Check if item already exists
    const existingItem = transferItems.find(item => item.kode_barang === selectedOption.val());
    if (existingItem) {
        alert('Item sudah ada dalam daftar transfer');
        return;
    }
    
    // Add item to array
    transferItems.push({
        kode_barang: selectedOption.val(),
        nama_barang: selectedOption.data('nama'),
        qty_transfer: qty,
        satuan: selectedOption.data('satuan'),
        harga_per_unit: harga,
        total_value: total,
        keterangan: keterangan
    });
    
    // Update display
    updateItemsTable();
    updateSummary();
    
    // Clear form
    productSelect.val('');
    $('#qty_transfer').val('');
    $('#harga_per_unit').val('');
    $('#total_value').val('');
    $('#item_keterangan').val('');
});

// Update items table
function updateItemsTable() {
    const tbody = $('#itemsList');
    tbody.empty();
    
    if (transferItems.length === 0) {
        tbody.append('<tr id="emptyRow"><td colspan="8" class="text-center text-muted">Belum ada item yang ditambahkan</td></tr>');
        return;
    }
    
    transferItems.forEach((item, index) => {
        const row = `
            <tr>
                <td>${item.kode_barang}</td>
                <td>${item.nama_barang}</td>
                <td class="text-right">${item.qty_transfer}</td>
                <td>${item.satuan}</td>
                <td class="text-right">${formatCurrency(item.harga_per_unit)}</td>
                <td class="text-right">${formatCurrency(item.total_value)}</td>
                <td>${item.keterangan || '-'}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Remove item
function removeItem(index) {
    if (confirm('Hapus item ini dari daftar transfer?')) {
        transferItems.splice(index, 1);
        updateItemsTable();
        updateSummary();
    }
}

// Update summary
function updateSummary() {
    const itemCount = transferItems.length;
    const totalValue = transferItems.reduce((sum, item) => sum + item.total_value, 0);
    
    $('#summary_item_count').text(itemCount);
    $('#summary_total_value').text(formatCurrency(totalValue));
    
    // Enable/disable submit button
    $('#submitBtn').prop('disabled', itemCount === 0);
}

// Handle form submission
$('#transferForm').on('submit', function(e) {
    if (transferItems.length === 0) {
        e.preventDefault();
        alert('Tambahkan minimal satu item untuk transfer');
        return;
    }
    
    // Add items to form data
    transferItems.forEach((item, index) => {
        Object.keys(item).forEach(key => {
            $(`<input type="hidden" name="items[${index}][${key}]" value="${item[key]}">`).appendTo(this);
        });
    });
});

// Validate database selection
$('#from_database, #to_database').on('change', function() {
    const fromDb = $('#from_database').val();
    const toDb = $('#to_database').val();
    
    if (fromDb && toDb && fromDb === toDb) {
        alert('Database sumber dan tujuan tidak boleh sama');
        $('#to_database').val('');
    }
});

// Load stock information
function loadStockInfo(kodeBarang) {
    $('#stockInfo').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    
    // Load stock breakdown
    $.ajax({
        url: '{{ route("api.stock-transfer.stock-breakdown") }}',
        method: 'GET',
        data: { kode_barang: kodeBarang },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                const fromDb = $('#from_database').val();
                const toDb = $('#to_database').val();
                
                let html = '<div class="row">';
                html += '<div class="col-6">';
                html += '<strong>Database Utama:</strong><br>';
                html += `<span class="text-primary">${data.primary?.good_stock || 0} ${data.primary?.satuan || 'PCS'}</span>`;
                html += '</div>';
                html += '<div class="col-6">';
                html += '<strong>Database Kedua:</strong><br>';
                html += `<span class="text-success">${data.secondary?.good_stock || 0} ${data.secondary?.satuan || 'PCS'}</span>`;
                html += '</div>';
                html += '</div>';
                
                // Show warning if insufficient stock in source database
                if (fromDb && data[fromDb] && data[fromDb].good_stock < parseFloat($('#qty_transfer').val() || 0)) {
                    html += '<div class="alert alert-warning mt-2">';
                    html += '<i class="fas fa-exclamation-triangle"></i> Stok tidak mencukupi di database sumber!';
                    html += '</div>';
                }
                
                $('#stockInfo').html(html);
            }
        },
        error: function() {
            $('#stockInfo').html('<p class="text-danger">Error loading stock information</p>');
        }
    });
    
    // Load global stock
    $.ajax({
        url: '{{ route("api.stock-transfer.global-stock") }}',
        method: 'GET',
        data: { kode_barang: kodeBarang },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                const globalStockHtml = `
                    <div class="mt-2">
                        <strong>Global Stock:</strong><br>
                        <span class="text-info">${data.good_stock} ${data.satuan || 'PCS'} (Total: ${data.total_stock})</span>
                    </div>
                `;
                $('#stockInfo').append(globalStockHtml);
            }
        },
        error: function() {
            // Ignore global stock error, breakdown is more important
        }
    });
}
</script>
@endsection
