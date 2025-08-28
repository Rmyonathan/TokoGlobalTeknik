@extends('layout.Nav')

@section('title', 'Edit Satuan Konversi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-edit"></i> Edit Satuan Konversi: {{ $unitConversion->unit_turunan }}
                        </h4>
                        <a href="{{ route('unit_conversion.index', $kodeBarang->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
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

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Barang</h6>
                                    <p class="mb-1"><strong>Kode:</strong> {{ $kodeBarang->kode_barang }}</p>
                                    <p class="mb-1"><strong>Nama:</strong> {{ $kodeBarang->name }}</p>
                                    <p class="mb-1"><strong>Satuan Dasar:</strong> {{ $kodeBarang->unit_dasar }}</p>
                                    <p class="mb-0"><strong>Harga per {{ $kodeBarang->unit_dasar }}:</strong> Rp {{ number_format($kodeBarang->harga_jual, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Satuan yang Sedang Diedit</h6>
                                    <p class="mb-1"><strong>Satuan Besar:</strong> {{ $unitConversion->unit_turunan }}</p>
                                    <p class="mb-1"><strong>Nilai Konversi Saat Ini:</strong> {{ $unitConversion->nilai_konversi }} {{ $kodeBarang->unit_dasar }}</p>
                                    <p class="mb-0"><strong>Harga Saat Ini:</strong> Rp {{ number_format($kodeBarang->harga_jual * $unitConversion->nilai_konversi, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('unit_conversion.update', [$kodeBarang->id, $unitConversion->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group row mb-3">
                            <label for="unit_turunan" class="col-sm-3 col-form-label">Satuan Besar <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('unit_turunan') is-invalid @enderror" 
                                       id="unit_turunan" name="unit_turunan" 
                                       value="{{ old('unit_turunan', $unitConversion->unit_turunan) }}" 
                                       placeholder="Contoh: DUS, BOX, PACK, KG" required>
                                @error('unit_turunan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Masukkan nama satuan besar (misal: DUS, BOX, PACK)</small>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="nilai_konversi" class="col-sm-3 col-form-label">Nilai Konversi <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input type="number" class="form-control @error('nilai_konversi') is-invalid @enderror" 
                                           id="nilai_konversi" name="nilai_konversi" 
                                           value="{{ old('nilai_konversi', $unitConversion->nilai_konversi) }}" 
                                           min="1" step="1" required>
                                    <span class="input-group-text">{{ $kodeBarang->unit_dasar }}</span>
                                </div>
                                @error('nilai_konversi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Berapa {{ $kodeBarang->unit_dasar }} dalam 1 satuan besar ini? 
                                    <br>
                                    <strong>Contoh:</strong> Jika 1 DUS = 40 {{ $kodeBarang->unit_dasar }}, maka masukkan 40
                                </small>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="keterangan" class="col-sm-3 col-form-label">Keterangan</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                          id="keterangan" name="keterangan" rows="3" 
                                          placeholder="Keterangan tambahan (opsional)">{{ old('keterangan', $unitConversion->keterangan) }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Keterangan tambahan untuk satuan ini (opsional)</small>
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label">Preview Harga</label>
                            <div class="col-sm-9">
                                <div class="alert alert-success">
                                    <strong>Harga per {{ $kodeBarang->unit_dasar }}:</strong> Rp {{ number_format($kodeBarang->harga_jual, 0, ',', '.') }}
                                    <br>
                                    <strong>Harga per satuan besar:</strong> 
                                    <span id="preview-harga">Rp 0</span>
                                    <br>
                                    <small class="text-muted">Harga akan dihitung otomatis berdasarkan nilai konversi</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Satuan Konversi
                                </button>
                                <a href="{{ route('unit_conversion.index', $kodeBarang->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nilaiKonversiInput = document.getElementById('nilai_konversi');
    const previewHarga = document.getElementById('preview-harga');
    const hargaPerUnit = {{ $kodeBarang->harga_jual }};
    
    function updatePreviewHarga() {
        const nilaiKonversi = parseInt(nilaiKonversiInput.value) || 0;
        const totalHarga = nilaiKonversi * hargaPerUnit;
        previewHarga.textContent = `Rp ${totalHarga.toLocaleString('id-ID')}`;
    }
    
    nilaiKonversiInput.addEventListener('input', updatePreviewHarga);
    updatePreviewHarga(); // Initial calculation
});
</script>
@endsection
