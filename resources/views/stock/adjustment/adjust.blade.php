@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Adjustment Stock</span>
                        <a href="{{ route('master.barang') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Kode Barang:</strong></label>
                                <p>{{ $stock->kode_barang }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Nama Barang:</strong></label>
                                <p>{{ $stock->nama_barang }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Harga:</strong></label>
                                <p>Rp {{ number_format($kodeBarang->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Current Stock:</strong></label>
                                <p>{{ $stock->good_stock }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('stock.adjustment.store') }}">
                        @csrf
                        <input type="hidden" name="kode_barang" value="{{ $stock->kode_barang }}">
                        <input type="hidden" name="quantity_before" value="{{ $stock->good_stock }}">

                        <div class="form-group">
                            <label for="quantity_after">Kuantitas Setelah Adjustment:</label>
                            <input type="number" 
                                   class="form-control @error('quantity_after') is-invalid @enderror" 
                                   id="quantity_after" 
                                   name="quantity_after"
                                   value="{{ old('quantity_after', $stock->good_stock) }}" 
                                   required>
                            @error('quantity_after')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan Adjustment:</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                     id="keterangan" 
                                     name="keterangan" 
                                     rows="3" 
                                     required>{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group" id="adjustment-preview" style="display: none;">
                            <label><strong>Adjustment Preview:</strong></label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Stok Awal:</strong> <span id="before-value">{{ $stock->good_stock }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Stok Akhir:</strong> <span id="after-value">{{ $stock->good_stock }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Selisih:</strong> <span id="diff-value" class="">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 text-center">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin melakukan adjustment stock?')">
                                <i class="fas fa-save"></i> Simpan Adjustment
                            </button>
                            <a href="{{ route('stock.adjustment.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initial stock value
        const initialStock = {{ $stock->good_stock }};
        
        // Update adjustment preview on input change
        $('#quantity_after').on('input', function() {
            const afterValue = parseInt($(this).val()) || 0;
            const diff = afterValue - initialStock;
            
            $('#after-value').text(afterValue);
            $('#diff-value').text(diff > 0 ? '+' + diff : diff);
            
            // Add color classes based on the difference
            if (diff > 0) {
                $('#diff-value').removeClass('text-danger').addClass('text-success');
            } else if (diff < 0) {
                $('#diff-value').removeClass('text-success').addClass('text-danger');
            } else {
                $('#diff-value').removeClass('text-success text-danger');
            }
            
            // Show the preview
            $('#adjustment-preview').show();
        });
    });
</script>
@endpush
@endsection