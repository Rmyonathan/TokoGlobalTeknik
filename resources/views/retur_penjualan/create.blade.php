@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus mr-2"></i>Tambah Retur Penjualan
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('retur-penjualan.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <form id="returForm" method="POST" action="{{ route('retur-penjualan.store') }}">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Header Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal Retur <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                           value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="kode_customer">Customer <span class="text-danger">*</span></label>
                                    <select class="form-control" id="kode_customer" name="kode_customer" required>
                                        <option value="">Pilih Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->kode_customer }}" 
                                                    {{ old('kode_customer') == $customer->kode_customer ? 'selected' : '' }}>
                                                {{ $customer->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="transaksi_id">Pilih Transaksi <span class="text-danger">*</span></label>
                                    <select class="form-control" id="transaksi_id" name="transaksi_id" required>
                                        <option value="">Pilih Transaksi</option>
                                        @foreach($transactions as $transaction)
                                            <option value="{{ $transaction->id }}" 
                                                    data-customer="{{ $transaction->customer->kode_customer ?? '' }}"
                                                    {{ old('transaksi_id') == $transaction->id ? 'selected' : '' }}>
                                                {{ $transaction->no_transaksi }} - {{ $transaction->customer->nama_customer ?? 'N/A' }} 
                                                ({{ $transaction->created_at->format('d/m/Y') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="alasan_retur">Alasan Retur <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="alasan_retur" name="alasan_retur" 
                                              rows="3" required placeholder="Masukkan alasan retur...">{{ old('alasan_retur') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Items -->
                        <div class="row">
                            <div class="col-12">
                                <h5>Item yang akan di-retur</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="itemsTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="15%">Kode Barang</th>
                                                <th width="25%">Nama Barang</th>
                                                <th width="10%">Qty Asli</th>
                                                <th width="10%">Qty Retur</th>
                                                <th width="10%">Satuan</th>
                                                <th width="10%">Harga</th>
                                                <th width="10%">Total</th>
                                                <th width="15%">Alasan</th>
                                                <th width="5%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsTableBody">
                                            <tr id="noItemsRow">
                                                <td colspan="10" class="text-center text-muted">
                                                    Pilih transaksi terlebih dahulu untuk melihat item
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Ringkasan Retur</h6>
                                        <div class="row">
                                            <div class="col-6">Total Item:</div>
                                            <div class="col-6"><strong id="totalItems">0</strong></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">Total Retur:</div>
                                            <div class="col-6"><strong id="totalRetur">Rp 0</strong></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save mr-1"></i>Simpan Retur
                        </button>
                        <a href="{{ route('retur-penjualan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedItems = [];
let transactionItems = [];

$(document).ready(function() {
    // Handle customer selection
    $('#kode_customer').change(function() {
        filterTransactions();
    });

    // Handle transaction selection
    $('#transaksi_id').change(function() {
        const transaksiId = $(this).val();
        if (transaksiId) {
            loadTransactionItems(transaksiId);
        } else {
            clearItemsTable();
        }
    });

    // Initialize
    filterTransactions();
});

function filterTransactions() {
    const selectedCustomer = $('#kode_customer').val();
    const transaksiSelect = $('#transaksi_id');
    
    transaksiSelect.find('option').each(function() {
        const option = $(this);
        const customerCode = option.data('customer');
        
        if (selectedCustomer === '' || customerCode === selectedCustomer) {
            option.show();
        } else {
            option.hide();
            if (option.is(':selected')) {
                option.prop('selected', false);
                clearItemsTable();
            }
        }
    });
}

function loadTransactionItems(transaksiId) {
    $.ajax({
        url: `/api/retur-penjualan/transaction-items?transaksi_id=${transaksiId}`,
        method: 'GET',
        success: function(response) {
            transactionItems = response.items;
            populateItemsTable();
        },
        error: function(xhr) {
            console.error('Error loading transaction items:', xhr);
            alert('Gagal memuat item transaksi');
        }
    });
}

function populateItemsTable() {
    const tbody = $('#itemsTableBody');
    tbody.empty();
    
    if (transactionItems.length === 0) {
        tbody.append(`
            <tr id="noItemsRow">
                <td colspan="10" class="text-center text-muted">
                    Tidak ada item dalam transaksi ini
                </td>
            </tr>
        `);
        return;
    }

    transactionItems.forEach((item, index) => {
        const row = `
            <tr data-item-id="${item.id}">
                <td>${index + 1}</td>
                <td>${item.kode_barang}</td>
                <td>${item.nama_barang}</td>
                <td class="text-right">${parseFloat(item.qty).toFixed(2)}</td>
                <td>
                    <input type="number" class="form-control qty-retur" 
                           data-item-id="${item.id}" 
                           data-max-qty="${item.qty}"
                           data-harga="${item.harga}"
                           min="0" 
                           max="${item.qty}" 
                           step="0.01" 
                           value="0">
                </td>
                <td>${item.satuan}</td>
                <td class="text-right">Rp ${parseFloat(item.harga).toLocaleString()}</td>
                <td class="text-right item-total">Rp 0</td>
                <td>
                    <input type="text" class="form-control item-alasan" 
                           data-item-id="${item.id}" 
                           placeholder="Alasan retur">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-success add-item" 
                            data-item-id="${item.id}" 
                            style="display: none;">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger remove-item" 
                            data-item-id="${item.id}" 
                            style="display: none;">
                        <i class="fas fa-minus"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Bind events
    bindItemEvents();
}

function bindItemEvents() {
    // Qty retur change
    $('.qty-retur').on('input', function() {
        const itemId = $(this).data('item-id');
        const qtyRetur = parseFloat($(this).val()) || 0;
        const harga = parseFloat($(this).data('harga'));
        const total = qtyRetur * harga;
        
        // Update total
        $(this).closest('tr').find('.item-total').text(`Rp ${total.toLocaleString()}`);
        
        // Show/hide buttons
        const addBtn = $(this).closest('tr').find('.add-item');
        const removeBtn = $(this).closest('tr').find('.remove-item');
        
        if (qtyRetur > 0) {
            addBtn.show();
            removeBtn.hide();
        } else {
            addBtn.hide();
            removeBtn.hide();
        }
        
        updateSummary();
    });

    // Add item to retur
    $('.add-item').click(function() {
        const itemId = $(this).data('item-id');
        const row = $(this).closest('tr');
        const qtyRetur = parseFloat(row.find('.qty-retur').val());
        const alasan = row.find('.item-alasan').val();
        
        if (qtyRetur <= 0) {
            alert('Qty retur harus lebih dari 0');
            return;
        }
        
        // Add to selected items
        const existingIndex = selectedItems.findIndex(item => item.transaksi_item_id == itemId);
        if (existingIndex >= 0) {
            selectedItems[existingIndex] = {
                transaksi_item_id: itemId,
                qty_retur: qtyRetur,
                alasan: alasan
            };
        } else {
            selectedItems.push({
                transaksi_item_id: itemId,
                qty_retur: qtyRetur,
                alasan: alasan
            });
        }
        
        // Update UI
        row.find('.add-item').hide();
        row.find('.remove-item').show();
        row.addClass('table-success');
        
        updateSummary();
        updateSubmitButton();
    });

    // Remove item from retur
    $('.remove-item').click(function() {
        const itemId = $(this).data('item-id');
        const row = $(this).closest('tr');
        
        // Remove from selected items
        selectedItems = selectedItems.filter(item => item.transaksi_item_id != itemId);
        
        // Reset row
        row.find('.qty-retur').val(0);
        row.find('.item-alasan').val('');
        row.find('.item-total').text('Rp 0');
        row.find('.add-item').hide();
        row.find('.remove-item').hide();
        row.removeClass('table-success');
        
        updateSummary();
        updateSubmitButton();
    });
}

function updateSummary() {
    const totalItems = selectedItems.length;
    const totalRetur = selectedItems.reduce((sum, item) => {
        const transactionItem = transactionItems.find(ti => ti.id == item.transaksi_item_id);
        return sum + (item.qty_retur * parseFloat(transactionItem.harga));
    }, 0);
    
    $('#totalItems').text(totalItems);
    $('#totalRetur').text(`Rp ${totalRetur.toLocaleString()}`);
}

function updateSubmitButton() {
    const submitBtn = $('#submitBtn');
    if (selectedItems.length > 0) {
        submitBtn.prop('disabled', false);
    } else {
        submitBtn.prop('disabled', true);
    }
}

function clearItemsTable() {
    $('#itemsTableBody').html(`
        <tr id="noItemsRow">
            <td colspan="10" class="text-center text-muted">
                Pilih transaksi terlebih dahulu untuk melihat item
            </td>
        </tr>
    `);
    selectedItems = [];
    transactionItems = [];
    updateSummary();
    updateSubmitButton();
}

// Form submission
$('#returForm').submit(function(e) {
    if (selectedItems.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu item untuk di-retur');
        return false;
    }
    
    // Add selected items to form
    selectedItems.forEach((item, index) => {
        $(`<input type="hidden" name="items[${index}][transaksi_item_id]" value="${item.transaksi_item_id}">`).appendTo(this);
        $(`<input type="hidden" name="items[${index}][qty_retur]" value="${item.qty_retur}">`).appendTo(this);
        $(`<input type="hidden" name="items[${index}][alasan]" value="${item.alasan}">`).appendTo(this);
    });
});
</script>
@endpush
