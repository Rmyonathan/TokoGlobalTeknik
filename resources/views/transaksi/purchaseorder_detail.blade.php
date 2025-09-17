@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Detail Purchase Order</h4>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small">No. PO</label>
                        <div class="font-weight-bold">{{ $po->no_po }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Tanggal</label>
                        <div class="font-weight-bold">{{ \Carbon\Carbon::parse($po->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Sales</label>
                        <div class="font-weight-bold">{{ $po->sales }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            @if($po->status === 'pending')
                                <span class="badge badge-pill badge-warning">Pending</span>
                            @elseif($po->status === 'completed')
                                <span class="badge badge-pill badge-success">Completed</span>
                            @elseif(strpos($po->status, 'cancelled') !== false)
                                <span class="badge badge-pill badge-danger">Batal</span>
                            @elseif($po->status === 'edited')
                                <span class="badge badge-pill badge-info">Diedit</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small">Customer</label>
                        <div class="font-weight-bold">{{ $po->kode_customer }} - {{ $po->customer->nama ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Alamat Customer</label>
                        <div class="font-weight-bold">{{ $po->customer->alamat ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">HP / Telp Customer</label>
                        <div class="font-weight-bold">HP: {{ $po->customer->hp ?? 'N/A' }} / TLP: {{ $po->customer->telepon ?? 'N/A' }}</div>
                    </div>
                    @if($po->tanggal_jadi)
                    <div class="mb-3">
                        <label class="text-muted small">Tanggal Jadi</label>
                        <div class="font-weight-bold">{{ \Carbon\Carbon::parse($po->tanggal_jadi)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
                    </div>
                    @endif
                    
                    @if($po->is_edited)
                    <div class="mb-3">
                        <label class="text-muted small">Diedit Oleh</label>
                        <div class="font-weight-bold">{{ $po->edited_by ?? 'Unknown' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Tanggal Edit</label>
                        <div class="font-weight-bold">{{ $po->edited_at ? \Carbon\Carbon::parse($po->edited_at)->locale('id')->isoFormat('dddd, D MMMM YYYY HH:mm') : '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Alasan Edit</label>
                        <div class="font-weight-bold">{{ $po->edit_reason ?? '-' }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4 shadow-sm">
                <div class="card-header mb-3">
                    <h5 class="mb-0">Daftar Item</h5>
                </div>
                <div class="table-responsive mb-3" style="max-height: 350px; overflow-y: auto; border-radius: 0.25rem;">
                    <table class="table table-hover table-striped mb-0" id="itemsTable">
                        <thead>
                            <tr class="bg-dark text-white" style="position: sticky; top: 0; z-index: 999;">
                                <th style="width: 12%; border-top-left-radius: 0.25rem;">Kode Barang</th>
                                <th style="width: 22%;">Nama Barang</th>
                                <th style="width: 26%;">Keterangan</th>
                                <th style="width: 10%; text-align: right;">Harga</th>
                                <th style="width: 8%; text-align: center;">Panjang</th>
                                <th style="width: 6%; text-align: center;">Qty</th>
                                <th style="width: 6%; text-align: center;">Diskon</th>
                                <th style="width: 10%; text-align: right; border-top-right-radius: 0.25rem;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $item)
                                <tr>
                                    <td>{{ $item->kode_barang }}</td>
                                    <td>{{ $item->nama_barang }}</td>
                                    <td>{{ $item->keterangan }}</td>
                                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="text-center">{{ $item->panjang }}</td>
                                    <td class="text-center">{{ $item->qty }}</td>
                                    <td class="text-center">{{ $item->diskon }}%</td>
                                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Pembayaran</div>
                                <div class="col-7 font-weight-bold">{{ $po->pembayaran ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Cara Bayar</div>
                                <div class="col-7 font-weight-bold">{{ $po->cara_bayar ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Rincian Biaya</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td>Subtotal</td>
                                        <td class="text-right font-weight-bold">Rp {{ number_format($po->subtotal ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Diskon</td>
                                        <td class="text-right">{{ $po->discount ?? 0 }}%</td>
                                    </tr>
                                    <tr>
                                        <td>Diskon Rupiah</td>
                                        <td class="text-right">Rp {{ number_format($po->disc_rupiah ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>PPN</td>
                                        <td class="text-right">Rp {{ number_format($po->ppn ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>DP</td>
                                        <td class="text-right">Rp {{ number_format($po->dp ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td class="font-weight-bold">Grand Total</td>
                                        <td class="text-right font-weight-bold">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                <a href="{{ route('transaksi.purchaseorder') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar PO
                </a>
                
                @if($po->status === 'pending')
                    {{-- Edit Button --}}
                    <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#editModal">
                        <i class="fas fa-edit mr-1"></i> Edit PO
                    </button>
                    
                    {{-- Form Selesaikan Transaksi --}}
                    <form action="{{ route('purchase-order.complete', ['id' => $po->id]) }}" method="POST" class="mr-2">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Yakin ingin selesaikan transaksi ini?');">
                            <i class="fas fa-check mr-1"></i> Selesaikan Transaksi
                        </button>
                    </form>

                    {{-- Form Batalkan PO --}}
                    <form action="{{ route('purchase-order.cancel', ['id' => $po->id]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin membatalkan PO ini?');">
                            <i class="fas fa-times mr-1"></i> Batalkan PO
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Purchase Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" action="{{ route('purchase-order.update', ['id' => $po->id]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tanggal">Tanggal</label>
                                <input type="date" class="form-control" id="edit_tanggal" name="tanggal" value="{{ $po->tanggal ? \Carbon\Carbon::parse($po->tanggal)->format('Y-m-d') : '' }}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_kode_customer">Customer</label>
                                <select class="form-control" id="edit_kode_customer" name="kode_customer" required>
                                    <option value="{{ $po->kode_customer }}">{{ $po->kode_customer }} - {{ $po->customer->nama ?? 'N/A' }}</option>
                                    <!-- You need to fetch other customers via JavaScript/AJAX -->
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_sales">Sales</label>
                                <select class="form-control" id="edit_sales" name="sales" required>
                                    <option value="{{ $po->sales }}">{{ $po->sales }}</option>
                                    <!-- You need to fetch other sales via JavaScript/AJAX -->
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_pembayaran">Pembayaran</label>
                                <select class="form-control" id="edit_pembayaran" name="pembayaran" required>
                                    <option value="Tunai" {{ $po->pembayaran == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                    <option value="Kredit" {{ $po->pembayaran == 'Kredit' ? 'selected' : '' }}>Kredit</option>
                                    <option value="Transfer" {{ $po->pembayaran == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_cara_bayar">Cara Bayar</label>
                                <input type="text" class="form-control" id="edit_cara_bayar" name="cara_bayar" value="{{ $po->cara_bayar }}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_reason">Alasan Edit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_reason" name="edit_reason" placeholder="Masukkan alasan perubahan PO" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">Rincian Biaya</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="edit_subtotal">Subtotal</label>
                                        <input type="number" class="form-control" id="edit_subtotal" name="subtotal" value="{{ $po->subtotal }}" readonly>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="edit_discount">Diskon (%)</label>
                                        <input type="number" class="form-control" id="edit_discount" name="discount" value="{{ $po->discount ?? 0 }}" min="0" max="100" step="0.01">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="edit_disc_rupiah">Diskon (Rp)</label>
                                        <input type="number" class="form-control" id="edit_disc_rupiah" name="disc_rupiah" value="{{ $po->disc_rupiah ?? 0 }}" min="0">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="edit_ppn">PPN</label>
                                        <input type="number" class="form-control" id="edit_ppn" name="ppn" value="{{ $po->ppn ?? 0 }}" min="0">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="edit_dp">DP</label>
                                        <input type="number" class="form-control" id="edit_dp" name="dp" value="{{ $po->dp ?? 0 }}" min="0">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="edit_grand_total">Grand Total</label>
                                        <input type="number" class="form-control" id="edit_grand_total" name="grand_total" value="{{ $po->grand_total }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Edit Items</h5>
                        <div class="table-responsive">
                            <table class="table table-striped" id="editItemsTable">
                                <thead>
                                    <tr>
                                        <th>Kode Barang</th>
                                        <th>Nama Barang</th>
                                        <th>Keterangan</th>
                                        <th>Harga</th>
                                        <th>Panjang</th>
                                        <th>Qty</th>
                                        <th>Diskon (%)</th>
                                        <th>Total</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="editItemsTableBody">
                                    @foreach($po->items as $index => $item)
                                    <tr class="item-row">
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            <input type="text" class="form-control item-kode" name="items[{{ $index }}][kodeBarang]" value="{{ $item->kode_barang }}" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="items[{{ $index }}][namaBarang]" value="{{ $item->nama_barang }}" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="items[{{ $index }}][keterangan]" value="{{ $item->keterangan }}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-harga" name="items[{{ $index }}][harga]" value="{{ $item->harga }}" min="0" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="items[{{ $index }}][panjang]" value="{{ $item->panjang }}" step="0.01" min="0">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-qty" name="items[{{ $index }}][qty]" value="{{ $item->qty }}" step="0.01" min="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-diskon" name="items[{{ $index }}][diskon]" value="{{ $item->diskon ?? 0 }}" min="0" max="100">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control item-total" name="items[{{ $index }}][total]" value="{{ $item->total }}" readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="addItemBtn">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Tambah Item</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="searchKodeBarang">Cari Kode Barang</label>
                    <input type="text" class="form-control" id="searchKodeBarang" placeholder="Masukkan kode atau nama barang">
                    <div id="searchResults" class="mt-2"></div>
                </div>
                
                <div id="selectedItemForm" style="display: none;">
                    <div class="form-group">
                        <label for="newKodeBarang">Kode Barang</label>
                        <input type="text" class="form-control" id="newKodeBarang" readonly>
                    </div>
                    <div class="form-group">
                        <label for="newNamaBarang">Nama Barang</label>
                        <input type="text" class="form-control" id="newNamaBarang" readonly>
                    </div>
                    <div class="form-group">
                        <label for="newKeterangan">Keterangan</label>
                        <input type="text" class="form-control" id="newKeterangan">
                    </div>
                    <div class="form-group">
                        <label for="newHarga">Harga</label>
                        <input type="number" class="form-control" id="newHarga" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="newPanjang">Panjang</label>
                        <input type="number" class="form-control" id="newPanjang" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label for="newQty">Qty</label>
                        <input type="number" class="form-control" id="newQty" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label for="newDiskon">Diskon (%)</label>
                        <input type="number" class="form-control" id="newDiskon" min="0" max="100" value="0">
                    </div>
                    <div class="form-group">
                        <label for="newTotal">Total</label>
                        <input type="number" class="form-control" id="newTotal" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="saveItemBtn" disabled>Tambahkan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
    // Calculate item total
    function calculateItemTotal(row) {
        const harga = parseFloat($(row).find('.item-harga').val()) || 0;
        const qty = parseInt($(row).find('.item-qty').val()) || 0;
        const diskon = parseFloat($(row).find('.item-diskon').val()) || 0;
        
        let total = harga * qty;
        if (diskon > 0) {
            total = total - (total * diskon / 100);
        }
        
        $(row).find('.item-total').val(total.toFixed(0));
        calculateGrandTotal();
    }
    
    // Calculate grand total
    function calculateGrandTotal() {
        let subtotal = 0;
        $('.item-total').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        
        $('#edit_subtotal').val(subtotal.toFixed(0));
        
        const discountPercent = parseFloat($('#edit_discount').val()) || 0;
        const discountRupiah = parseFloat($('#edit_disc_rupiah').val()) || 0;
        const ppn = parseFloat($('#edit_ppn').val()) || 0;
        const dp = parseFloat($('#edit_dp').val()) || 0;
        
        let discountAmount = (subtotal * discountPercent / 100) + discountRupiah;
        let grandTotal = subtotal - discountAmount + ppn - dp;
        
        $('#edit_grand_total').val(grandTotal.toFixed(0));
    }
    
    // Initialize calculations
    $('.item-row').each(function() {
        calculateItemTotal(this);
    });
    
    // Handle input changes
    $(document).on('input', '.item-harga, .item-qty, .item-diskon', function() {
        calculateItemTotal($(this).closest('tr'));
    });
    
    // Handle discount, PPN, DP changes
    $('#edit_discount, #edit_disc_rupiah, #edit_ppn, #edit_dp').on('input', function() {
        calculateGrandTotal();
    });
    
    // Remove item
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        } else {
            alert('Minimal harus ada 1 item!');
        }
    });
    
    // Add item button
    $('#addItemBtn').click(function() {
        $('#addItemModal').modal('show');
    });
    
    // Search kode barang
    $('#searchKodeBarang').on('input', function() {
        const keyword = $(this).val();
        if (keyword.length >= 2) {
            $.ajax({
                url: '/api/panels/search-available',
                method: 'GET',
                data: { keyword: keyword },
                success: function(data) {
                    let html = '<div class="list-group">';
                    if (data.length > 0) {
                        data.forEach(function(item) {
                            html += `<a href="#" class="list-group-item list-group-item-action select-item" 
                                        data-kode="${item.group_id}" 
                                        data-nama="${item.name}"
                                        data-price="${item.price}"
                                        data-panjang="${item.panjang || 0}">
                                        ${item.group_id} - ${item.name}
                                    </a>`;
                        });
                    } else {
                        html += '<div class="list-group-item">Tidak ditemukan</div>';
                    }
                    html += '</div>';
                    $('#searchResults').html(html);
                }
            });
        } else {
            $('#searchResults').html('');
        }
    });
    
    // Select item from search results
    $(document).on('click', '.select-item', function(e) {
        e.preventDefault();
        const kode = $(this).data('kode');
        const nama = $(this).data('nama');
        const price = $(this).data('price');
        const panjang = $(this).data('panjang') || 0;
        
        $('#newKodeBarang').val(kode);
        $('#newNamaBarang').val(nama);
        // Autofill keterangan with the same value as nama barang
        $('#newKeterangan').val(nama);
        $('#newHarga').val(price);
        $('#newPanjang').val(panjang);
        $('#newQty').val(1);
        $('#newDiskon').val(0);
        
        // Calculate total
        const harga = parseFloat(price) || 0;
        const qty = 1;
        $('#newTotal').val(harga * qty);
        
        $('#searchResults').html('');
        $('#selectedItemForm').show();
        $('#saveItemBtn').prop('disabled', false);
    });
    
    // Calculate new item total
    $('#newHarga, #newQty, #newDiskon').on('input', function() {
        const harga = parseFloat($('#newHarga').val()) || 0;
        const qty = parseInt($('#newQty').val()) || 0;
        const diskon = parseFloat($('#newDiskon').val()) || 0;
        
        let total = harga * qty;
        if (diskon > 0) {
            total = total - (total * diskon / 100);
        }
        
        $('#newTotal').val(total.toFixed(0));
    });
    
    // Save new item
    $('#saveItemBtn').click(function() {
        const kodeBarang = $('#newKodeBarang').val();
        const namaBarang = $('#newNamaBarang').val();
        const keterangan = $('#newKeterangan').val();
        const harga = $('#newHarga').val();
        const panjang = $('#newPanjang').val() || 0;
        const qty = $('#newQty').val();
        const diskon = $('#newDiskon').val();
        const total = $('#newTotal').val();
        
        if (!kodeBarang || !namaBarang || !harga || !qty) {
            alert('Mohon lengkapi data item!');
            return;
        }
        
        const index = $('.item-row').length;
        const newRow = `
            <tr class="item-row">
                <td>
                    <input type="hidden" name="items[${index}][id]" value="new">
                    <input type="text" class="form-control item-kode" name="items[${index}][kodeBarang]" value="${kodeBarang}" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" name="items[${index}][namaBarang]" value="${namaBarang}" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" name="items[${index}][keterangan]" value="${keterangan}">
                </td>
                <td>
                    <input type="number" class="form-control item-harga" name="items[${index}][harga]" value="${harga}" min="0" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="items[${index}][panjang]" value="${panjang}" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" class="form-control item-qty" name="items[${index}][qty]" value="${qty}" step="0.01" min="0.01" required>
                </td>
                <td>
                    <input type="number" class="form-control item-diskon" name="items[${index}][diskon]" value="${diskon}" min="0" max="100">
                </td>
                <td>
                    <input type="number" class="form-control item-total" name="items[${index}][total]" value="${total}" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#editItemsTableBody').append(newRow);
        calculateGrandTotal();
        
        // Reset form and close modal
        $('#selectedItemForm').hide();
        $('#searchKodeBarang').val('');
        $('#saveItemBtn').prop('disabled', true);
        $('#addItemModal').modal('hide');
    });
    
    // Form validation before submit
    $('#editForm').submit(function(e) {
        const editReason = $('#edit_reason').val().trim();
        if (!editReason) {
            e.preventDefault();
            alert('Harap isi alasan edit!');
            return false;
        }
        
        if ($('.item-row').length === 0) {
            e.preventDefault();
            alert('Minimal harus ada 1 item!');
            return false;
        }
        
        return true;
    });
});
</script>
@endsection