
@extends('layout.Nav')

@section('content')
<section id="create-perusahaan">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Perusahaan</h2>
        <a href="{{ route('perusahaan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Tambah Perusahaan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('perusahaan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="kota" class="form-label">Kota</label>
                                <input type="text" class="form-control @error('kota') is-invalid @enderror" id="kota" name="kota" value="{{ old('kota') }}">
                                @error('kota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="kode_pos" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control @error('kode_pos') is-invalid @enderror" id="kode_pos" name="kode_pos" value="{{ old('kode_pos') }}">
                                @error('kode_pos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon') }}">
                                @error('telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="fax" class="form-label">Fax</label>
                                <input type="text" class="form-control @error('fax') is-invalid @enderror" id="fax" name="fax" value="{{ old('fax') }}">
                                @error('fax')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="catatan_nota" class="form-label">Catatan Nota</label>
                            <textarea class="form-control @error('catatan_nota') is-invalid @enderror" id="catatan_nota" name="catatan_nota" rows="3">{{ old('catatan_nota') }}</textarea>
                            <small class="text-muted">Catatan tambahan yang akan muncul di nota.</small>
                            @error('catatan_nota')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="catatan_surat_jalan" class="form-label">Catatan Surat Jalan</label>
                            <textarea class="form-control @error('catatan_surat_jalan') is-invalid @enderror" id="catatan_surat_jalan" name="catatan_surat_jalan" rows="3">{{ old('catatan_surat_jalan') }}</textarea>
                            <small class="text-muted">Catatan tambahan yang akan muncul di surat jalan.</small>
                            @error('catatan_surat_jalan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    
                    <a href="{{ route('perusahaan.index') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-arrow-left mr-1"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection