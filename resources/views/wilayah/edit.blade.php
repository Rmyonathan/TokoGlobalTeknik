@extends('layout.Nav')

@section('title', 'Edit Wilayah')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit"></i> Edit Wilayah: {{ $wilayah->nama_wilayah }}
                        </h4>
                        <a href="{{ route('wilayah.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('wilayah.update', $wilayah) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nama_wilayah" class="font-weight-bold">
                                        Nama Wilayah <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('nama_wilayah') is-invalid @enderror" 
                                           id="nama_wilayah" 
                                           name="nama_wilayah" 
                                           value="{{ old('nama_wilayah', $wilayah->nama_wilayah) }}" 
                                           placeholder="Contoh: Jakarta Pusat, Bandung, Surabaya"
                                           required 
                                           maxlength="100">
                                    @error('nama_wilayah')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Masukkan nama wilayah atau kota (maksimal 100 karakter)
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active" class="font-weight-bold">Status</label>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', $wilayah->is_active) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">
                                            Aktif
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Wilayah yang aktif dapat digunakan untuk pelanggan baru
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="keterangan" class="font-weight-bold">Keterangan</label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="3" 
                                      placeholder="Tambahkan keterangan tambahan tentang wilayah ini (opsional)"
                                      maxlength="255">{{ old('keterangan', $wilayah->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">
                                Deskripsi singkat tentang wilayah (maksimal 255 karakter)
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Informasi Sistem</label>
                                    <div class="form-control-plaintext">
                                        <small class="text-muted">
                                            <strong>ID:</strong> {{ $wilayah->id }}<br>
                                            <strong>Dibuat:</strong> {{ $wilayah->created_at->format('d/m/Y H:i') }}<br>
                                            <strong>Terakhir Update:</strong> {{ $wilayah->updated_at->format('d/m/Y H:i') }}<br>
                                            <strong>Jumlah Pelanggan:</strong> {{ $wilayah->customers->count() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-secondary mr-2">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Wilayah
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Character counter for textarea
    $('#keterangan').on('input', function() {
        var maxLength = 255;
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        if (remaining < 0) {
            $(this).val($(this).val().substring(0, maxLength));
            remaining = 0;
        }
        
        // Update character count display
        if (!$(this).next('.char-count').length) {
            $(this).after('<small class="char-count text-muted"></small>');
        }
        $(this).next('.char-count').text(remaining + ' karakter tersisa');
    });
    
    // Trigger on page load
    $('#keterangan').trigger('input');
    
    // Form validation
    $('form').on('submit', function(e) {
        var namaWilayah = $('#nama_wilayah').val().trim();
        
        if (namaWilayah === '') {
            alert('Nama wilayah harus diisi!');
            $('#nama_wilayah').focus();
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });
    
    // Reset form confirmation
    $('button[type="reset"]').on('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin mereset form? Semua data yang sudah diisi akan hilang.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
