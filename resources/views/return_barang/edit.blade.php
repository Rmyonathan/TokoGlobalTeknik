@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i>Edit Return Barang
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('return-barang.show', $returnBarang) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <form method="POST" action="{{ route('return-barang.update', $returnBarang) }}" id="returnForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Header Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal Return <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                           value="{{ old('tanggal', $returnBarang->tanggal->format('Y-m-d')) }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipe_return">Tipe Return</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($returnBarang->tipe_return) }}" readonly>
                                    <input type="hidden" name="tipe_return" value="{{ $returnBarang->tipe_return }}">
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Transaksi Asal</label>
                                    <input type="text" class="form-control" value="{{ $returnBarang->no_transaksi_asal }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $returnBarang->customer->kode_customer ?? 'N/A' }} - {{ $returnBarang->customer->nama ?? 'N/A' }}" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="alasan_return">Alasan Return <span class="text-danger">*</span></label>
                                    <textarea name="alasan_return" id="alasan_return" class="form-control @error('alasan_return') is-invalid @enderror" 
                                              rows="3" placeholder="Jelaskan alasan return barang..." required>{{ old('alasan_return', $returnBarang->alasan_return) }}</textarea>
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
                                    @foreach($returnBarang->items as $index => $item)
                                    <div class="item-row border p-3 mb-3" data-index="{{ $index }}">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Barang</label>
                                                    <input type="text" class="form-control item-nama" 
                                                           value="{{ $item->nama_barang }}" readonly>
                                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                    <input type="hidden" name="items[{{ $index }}][kode_barang]" 
                                                           class="item-kode" value="{{ $item->kode_barang }}">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Qty Return <span class="text-danger">*</span></label>
                                                    <input type="number" name="items[{{ $index }}][qty_return]" 
                                                           class="form-control item-qty-return @error('items.'.$index.'.qty_return') is-invalid @enderror" 
                                                           value="{{ old('items.'.$index.'.qty_return', $item->qty_return) }}" 
                                                           step="0.01" min="0.01" required>
                                                    @error('items.'.$index.'.qty_return')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Harga <span class="text-danger">*</span></label>
                                                    <input type="number" name="items[{{ $index }}][harga]" 
                                                           class="form-control item-harga @error('items.'.$index.'.harga') is-invalid @enderror" 
                                                           value="{{ old('items.'.$index.'.harga', $item->harga) }}" 
                                                           step="0.01" min="0" required>
                                                    @error('items.'.$index.'.harga')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Total</label>
                                                    <input type="number" class="form-control item-total" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Keterangan</label>
                                                    <input type="text" name="items[{{ $index }}][keterangan]" 
                                                           class="form-control" 
                                                           value="{{ old('items.'.$index.'.keterangan', $item->keterangan) }}" 
                                                           placeholder="Keterangan return...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
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

                        <!-- Status Information -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Status:</strong> 
                                    @switch($returnBarang->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge badge-success">Approved</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge badge-danger">Rejected</span>
                                            @break
                                        @case('processed')
                                            <span class="badge badge-primary">Processed</span>
                                            @break
                                    @endswitch
                                    <br>
                                    <small>Return barang ini dapat diedit karena statusnya masih pending.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Update Return
                        </button>
                        <a href="{{ route('return-barang.show', $returnBarang) }}" class="btn btn-secondary">
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
// Calculate totals
function calculateTotals() {
    let total = 0;
    $('.item-row').each(function() {
        const qty = parseFloat($(this).find('.item-qty-return').val()) || 0;
        const harga = parseFloat($(this).find('.item-harga').val()) || 0;
        const itemTotal = qty * harga;
        $(this).find('.item-total').val(itemTotal.toFixed(2));
        total += itemTotal;
    });
    $('#total-return').text('Rp ' + total.toLocaleString('id-ID'));
}

// Event listeners
$(document).on('input', '.item-qty-return, .item-harga', function() {
    calculateTotals();
});

// Form validation
$('#returnForm').on('submit', function(e) {
    // Validate qty return
    let hasValidQty = false;
    $('.item-qty-return').each(function() {
        const qtyReturn = parseFloat($(this).val()) || 0;
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
