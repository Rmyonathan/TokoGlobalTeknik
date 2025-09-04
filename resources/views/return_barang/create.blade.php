@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>Tambah Return Barang
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('return-barang.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('return-barang.store') }}" id="returnForm">
                    @csrf
                    <div class="card-body">
                        <!-- Header Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal Return <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                           value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipe_return">Tipe Return <span class="text-danger">*</span></label>
                                    <select name="tipe_return" id="tipe_return" class="form-control @error('tipe_return') is-invalid @enderror" required>
                                        <option value="">Pilih Tipe Return</option>
                                        <option value="penjualan" {{ old('tipe_return') == 'penjualan' ? 'selected' : '' }}>Return Penjualan</option>
                                        <option value="pembelian" {{ old('tipe_return') == 'pembelian' ? 'selected' : '' }}>Return Pembelian</option>
                                    </select>
                                    @error('tipe_return')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Selection -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="no_transaksi_asal">Transaksi Asal <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="no_transaksi_asal" id="no_transaksi_asal" 
                                               class="form-control @error('no_transaksi_asal') is-invalid @enderror" 
                                               value="{{ old('no_transaksi_asal', $transaksi->no_transaksi ?? '') }}" 
                                               placeholder="Pilih atau ketik nomor transaksi" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary" onclick="searchTransactions()">
                                                <i class="fas fa-search"></i> Cari
                                            </button>
                                        </div>
                                    </div>
                                    @error('no_transaksi_asal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_customer">Customer <span class="text-danger">*</span></label>
                                    <input type="text" name="kode_customer" id="kode_customer" 
                                           class="form-control @error('kode_customer') is-invalid @enderror" 
                                           value="{{ old('kode_customer', $transaksi->kode_customer ?? '') }}" 
                                           readonly>
                                    @error('kode_customer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nama Customer</label>
                                    <input type="text" id="nama_customer" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="alasan_return">Alasan Return <span class="text-danger">*</span></label>
                                    <textarea name="alasan_return" id="alasan_return" class="form-control @error('alasan_return') is-invalid @enderror" 
                                              rows="3" placeholder="Jelaskan alasan return barang..." required>{{ old('alasan_return') }}</textarea>
                                    @error('alasan_return')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Rincian Barang Return</h5>
                            </div>
                            <div class="card-body">
                                <div id="items-container">
                                    @if($transaksi && $items->count() > 0)
                                        @foreach($items as $index => $item)
                                        <div class="item-row border p-3 mb-3" data-index="{{ $index }}">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Barang</label>
                                                        <input type="text" class="form-control item-nama" 
                                                               value="{{ $item->nama_barang }}" readonly>
                                                        <input type="hidden" name="items[{{ $index }}][kode_barang]" 
                                                               class="item-kode" value="{{ $item->kode_barang }}">
                                                        <input type="hidden" name="items[{{ $index }}][nama_barang]" 
                                                               class="item-nama-hidden" value="{{ $item->nama_barang }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Qty Asal</label>
                                                        <input type="number" class="form-control item-qty-asal" 
                                                               value="{{ $item->qty }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Qty Return <span class="text-danger">*</span></label>
                                                        <input type="number" name="items[{{ $index }}][qty_return]" 
                                                               class="form-control item-qty-return" 
                                                               value="{{ old('items.'.$index.'.qty_return', 0) }}" 
                                                               step="0.01" min="0.01" max="{{ $item->qty }}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Harga</label>
                                                        <input type="number" name="items[{{ $index }}][harga]" 
                                                               class="form-control item-harga" 
                                                               value="{{ old('items.'.$index.'.harga', $item->harga) }}" 
                                                               step="0.01" min="0" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Total</label>
                                                        <input type="number" class="form-control item-total" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <div>
                                                            <input type="checkbox" class="form-check-input item-checkbox" 
                                                                   onchange="toggleItem(this)" checked>
                                                            <label class="form-check-label">Pilih</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Keterangan</label>
                                                        <input type="text" name="items[{{ $index }}][keterangan]" 
                                                               class="form-control" 
                                                               value="{{ old('items.'.$index.'.keterangan', $item->keterangan) }}" 
                                                               placeholder="Keterangan return...">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Satuan</label>
                                                        <input type="text" name="items[{{ $index }}][satuan]" 
                                                               class="form-control" value="LBR" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Pilih transaksi terlebih dahulu untuk melihat items</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Total Return</h6>
                                        <h4 class="text-primary" id="total-return">Rp 0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Simpan Return
                        </button>
                        <a href="{{ route('return-barang.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Search Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="search_transaction">Cari Transaksi</label>
                    <input type="text" id="search_transaction" class="form-control" 
                           placeholder="Ketik nomor transaksi atau nama customer...">
                </div>
                <div id="transaction-results">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
console.log('Return Barang Create page loaded');
console.log('jQuery version:', $.fn.jquery);

let selectedTransaction = null;

// Load transaction data if available
@if($transaksi)
selectedTransaction = {
    no_transaksi: '{{ $transaksi->no_transaksi }}',
    kode_customer: '{{ $transaksi->kode_customer }}',
    customer: {
        nama: '{{ $transaksi->customer->nama ?? '' }}'
    }
};
$('#nama_customer').val(selectedTransaction.customer.nama);
@endif

// Calculate totals
function calculateTotals() {
    let total = 0;
    $('.item-row').each(function() {
        if ($(this).find('.item-checkbox').is(':checked')) {
            const qty = parseFloat($(this).find('.item-qty-return').val()) || 0;
            const harga = parseFloat($(this).find('.item-harga').val()) || 0;
            const itemTotal = qty * harga;
            $(this).find('.item-total').val(itemTotal.toFixed(2));
            total += itemTotal;
        }
    });
    $('#total-return').text('Rp ' + total.toLocaleString('id-ID'));
}

// Toggle item selection
function toggleItem(checkbox) {
    const row = $(checkbox).closest('.item-row');
    const inputs = row.find('input:not(.item-checkbox)');
    
    if (checkbox.checked) {
        inputs.prop('disabled', false);
        row.removeClass('bg-light');
    } else {
        inputs.prop('disabled', true);
        row.addClass('bg-light');
    }
    
    calculateTotals();
}

// Search transactions
function searchTransactions() {
    console.log('Opening transaction modal');
    $('#transactionModal').modal('show');
    $('#search_transaction').val('');
    $('#transaction-results').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    console.log('Modal opened, search field cleared');
}

// Transaction search
$('#search_transaction').on('input', function() {
    const query = $(this).val();
    console.log('Search input event triggered');
    console.log('Search query:', query);
    
    if (query.length < 3) {
        $('#transaction-results').html('<div class="text-center text-muted">Ketik minimal 3 karakter</div>');
        return;
    }
    
    // Show loading
    $('#transaction-results').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Mencari transaksi...</div>');
    
    console.log('Making AJAX request to:', '/return-barang-api/search-transactions');
    console.log('Query parameter:', query);
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.get('/return-barang-api/search-transactions', { q: query })
        .done(function(data) {
            console.log('Search response:', data);
            
            if (!data || data.length === 0) {
                $('#transaction-results').html('<div class="text-center text-muted">Tidak ada transaksi ditemukan</div>');
                return;
            }
            
            let html = '<div class="list-group">';
            data.forEach(function(transaction) {
                const tanggal = new Date(transaction.tanggal).toLocaleDateString('id-ID');
                html += `
                    <a href="#" class="list-group-item list-group-item-action" onclick="selectTransaction('${transaction.no_transaksi}')">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${transaction.no_transaksi}</h6>
                            <small>${tanggal}</small>
                        </div>
                        <p class="mb-1">Customer: ${transaction.kode_customer} - ${transaction.customer ? transaction.customer.nama : 'N/A'}</p>
                        <small>Total: Rp ${parseInt(transaction.grand_total).toLocaleString('id-ID')}</small>
                    </a>
                `;
            });
            html += '</div>';
            $('#transaction-results').html(html);
        })
        .fail(function(xhr, status, error) {
            console.error('Search error:', xhr, status, error);
            $('#transaction-results').html('<div class="text-center text-danger">Error loading transactions: ' + error + '</div>');
        });
});

// Select transaction
function selectTransaction(noTransaksi) {
    console.log('Selected transaction:', noTransaksi);
    $('#no_transaksi_asal').val(noTransaksi);
    $('#transactionModal').modal('hide');
    
    // Show loading in items container
    $('#items-container').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading items...</div>');
    
    // Load transaction items
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.get('/return-barang-api/transaction-items', { no_transaksi: noTransaksi })
        .done(function(data) {
            console.log('Transaction items response:', data);
            
            if (data.transaksi) {
                $('#kode_customer').val(data.transaksi.kode_customer);
                $('#nama_customer').val(data.transaksi.customer ? data.transaksi.customer.nama : '');
                
                // Load items
                let html = '';
                if (data.items && data.items.length > 0) {
                    data.items.forEach(function(item, index) {
                        html += `
                            <div class="item-row border p-3 mb-3" data-index="${index}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Barang</label>
                                            <input type="text" class="form-control item-nama" value="${item.nama_barang}" readonly>
                                            <input type="hidden" name="items[${index}][kode_barang]" class="item-kode" value="${item.kode_barang}">
                                            <input type="hidden" name="items[${index}][nama_barang]" class="item-nama-hidden" value="${item.nama_barang}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Qty Asal</label>
                                            <input type="number" class="form-control item-qty-asal" value="${item.qty_asal}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Qty Tersisa</label>
                                            <input type="number" class="form-control item-qty-sisa" value="${item.qty_sisa}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Qty Return <span class="text-danger">*</span></label>
                                            <input type="number" name="items[${index}][qty_return]" class="form-control item-qty-return" 
                                                   value="0" step="0.01" min="0.01" max="${item.qty_sisa}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Harga</label>
                                            <input type="number" name="items[${index}][harga]" class="form-control item-harga" 
                                                   value="${item.harga}" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Total</label>
                                            <input type="number" class="form-control item-total" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div>
                                                <input type="checkbox" class="form-check-input item-checkbox" onchange="toggleItem(this)" checked>
                                                <label class="form-check-label">Pilih</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input type="text" name="items[${index}][keterangan]" class="form-control" 
                                                   value="${item.keterangan || ''}" placeholder="Keterangan return...">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Satuan</label>
                                            <input type="text" name="items[${index}][satuan]" class="form-control" value="LBR" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html = '<div class="text-center text-muted">Tidak ada items ditemukan</div>';
                }
                $('#items-container').html(html);
                calculateTotals();
            } else {
                $('#items-container').html('<div class="text-center text-danger">Transaksi tidak ditemukan</div>');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error loading transaction items:', xhr, status, error);
            $('#items-container').html('<div class="text-center text-danger">Error loading items: ' + error + '</div>');
        });
}

// Event listeners
$(document).on('input', '.item-qty-return, .item-harga', function() {
    validateQtyReturn($(this));
    calculateTotals();
});

// Validate qty return
function validateQtyReturn(input) {
    const qtyReturn = parseFloat(input.val()) || 0;
    const qtySisa = parseFloat(input.closest('.item-row').find('.item-qty-sisa').val()) || 0;
    
    if (qtyReturn > qtySisa) {
        input.addClass('is-invalid');
        input.after('<div class="invalid-feedback">Qty return tidak boleh melebihi qty tersisa</div>');
    } else {
        input.removeClass('is-invalid');
        input.next('.invalid-feedback').remove();
    }
}

$(document).on('change', '.item-checkbox', function() {
    toggleItem(this);
});

// Form validation
$('#returnForm').on('submit', function(e) {
    const checkedItems = $('.item-checkbox:checked').length;
    if (checkedItems === 0) {
        e.preventDefault();
        alert('Pilih minimal satu item untuk di-return');
        return false;
    }
    
    // Validate qty return
    let hasValidQty = false;
    $('.item-checkbox:checked').each(function() {
        const row = $(this).closest('.item-row');
        const qtyReturn = parseFloat(row.find('.item-qty-return').val()) || 0;
        if (qtyReturn > 0) {
            hasValidQty = true;
        }
    });
    
    if (!hasValidQty) {
        e.preventDefault();
        alert('Qty return harus lebih dari 0 untuk minimal satu item');
        return false;
    }
});

// Initialize
calculateTotals();
</script>
@endpush
