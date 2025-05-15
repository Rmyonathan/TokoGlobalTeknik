@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Create Stock Adjustment</span>
                        <a href="{{ route('stock.adjustment.index') }}" class="btn btn-sm btn-secondary">
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

                    <form method="POST" action="{{ route('stock.adjustment.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="kode_barang">Pilih Barang:</label>
                            <select class="form-control @error('kode_barang') is-invalid @enderror" 
                                    id="kode_barang" 
                                    name="kode_barang" 
                                    required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach ($stocks as $stock)
                                    <option value="{{ $stock->kode_barang }}" 
                                            data-current-stock="{{ $stock->good_stock }}"
                                            data-nama="{{ $stock->nama_barang }}">
                                        {{ $stock->kode_barang }} - {{ $stock->nama_barang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_barang')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div id="stock-details" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Detail Barang</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Kode Barang:</strong> <span id="detail-kode"></span></p>
                                            <p><strong>Nama Barang:</strong> <span id="detail-nama"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Current Stock:</strong> <span id="detail-stock"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="quantity_before" id="quantity_before">

                            <div class="form-group">
                                <label for="quantity_after">Kuantitas Setelah Adjustment:</label>
                                <input type="number" 
                                      class="form-control @error('quantity_after') is-invalid @enderror" 
                                      id="quantity_after" 
                                      name="quantity_after" 
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
                                                <strong>Stok Awal:</strong> <span id="before-value">0</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Stok Akhir:</strong> <span id="after-value">0</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Selisih:</strong> <span id="diff-value" class="">0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4 text-center">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin melakukan adjustment stock?')">
                                <i class="fas fa-save"></i> Simpan Adjustment
                            </button>
                            <a href="{{ route('stock.adjustment.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Batal
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
        $('#kode_barang').on('change', function() {
            var selected = $(this).find('option:selected');
            
            if (selected.val()) {
                var kode = selected.val();
                var nama = selected.data('nama');
                var currentStock = selected.data('current-stock');
                
                // Update the details
                $('#detail-kode').text(kode);
                $('#detail-nama').text(nama);
                $('#detail-stock').text(currentStock);
                $('#quantity_before').val(currentStock);
                $('#quantity_after').val(currentStock);
                
                // Show the stock details section
                $('#stock-details').show();
            } else {
                $('#stock-details').hide();
            }
        });
        
        // Update adjustment preview on input change
        $('#quantity_after').on('input', function() {
            const beforeValue = parseInt($('#quantity_before').val()) || 0;
            const afterValue = parseInt($(this).val()) || 0;
            const diff = afterValue - beforeValue;
            
            $('#before-value').text(beforeValue);
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