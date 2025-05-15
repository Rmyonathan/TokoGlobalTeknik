
@extends('layout.Nav')

@section('content')
<section id="edit-perusahaan">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Perusahaan</h2>
        <a href="{{ route('perusahaan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Form Edit Perusahaan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('perusahaan.update', $perusahaan->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $perusahaan->nama) }}" required>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $perusahaan->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="kota" class="form-label">Kota</label>
                                <input type="text" class="form-control @error('kota') is-invalid @enderror" id="kota" name="kota" value="{{ old('kota', $perusahaan->kota) }}">
                                @error('kota')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="kode_pos" class="form-label">Kode Pos</label>
                                <input type="text" class="form-control @error('kode_pos') is-invalid @enderror" id="kode_pos" name="kode_pos" value="{{ old('kode_pos', $perusahaan->kode_pos) }}">
                                @error('kode_pos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control @error('telepon') is-invalid @enderror" id="telepon" name="telepon" value="{{ old('telepon', $perusahaan->telepon) }}">
                                @error('telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="fax" class="form-label">Fax</label>
                                <input type="text" class="form-control @error('fax') is-invalid @enderror" id="fax" name="fax" value="{{ old('fax', $perusahaan->fax) }}">
                                @error('fax')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $perusahaan->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', $perusahaan->website) }}" placeholder="https://">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control @error('npwp') is-invalid @enderror" id="npwp" name="npwp" value="{{ old('npwp', $perusahaan->npwp) }}">
                            @error('npwp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="logo" class="form-label">Logo Perusahaan</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo">
                            <small class="text-muted">Format: JPG, JPEG, PNG. Maksimal 2MB.</small>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            @if($perusahaan->logo)
                            <div class="mt-2">
                                <p class="mb-1">Logo Saat Ini:</p>
                                <img src="{{ $perusahaan->logo }}" alt="Logo {{ $perusahaan->nama }}" class="img-thumbnail" style="max-height: 100px;">
                                <p class="small text-muted mt-1">Unggah logo baru untuk mengganti</p>
                            </div>
                            @endif
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $perusahaan->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="catatan_nota" class="form-label">Catatan Nota</label>
                        <textarea class="form-control @error('catatan_nota') is-invalid @enderror" id="catatan_nota" name="catatan_nota" rows="3">{{ old('catatan_nota', $perusahaan->catatan_nota) }}</textarea>
                        <small class="text-muted">Catatan tambahan yang akan muncul di nota.</small>
                        @error('catatan_nota')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="catatan_surat_jalan" class="form-label">Catatan Surat Jalan</label>
                        <textarea class="form-control @error('catatan_surat_jalan') is-invalid @enderror" id="catatan_surat_jalan" name="catatan_surat_jalan" rows="3">{{ old('catatan_surat_jalan', $perusahaan->catatan_surat_jalan) }}</textarea>
                        <small class="text-muted">Catatan tambahan yang akan muncul di surat jalan.</small>
                        @error('catatan_surat_jalan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection