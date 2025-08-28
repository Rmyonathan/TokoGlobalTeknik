@extends('layout.Nav')

@section('content')
<section id="edit-kode-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Kode Barang</h2>
        <a href="{{ route('code.view-code') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('code.update', $code->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group row">
                    <label for="kode_barang" class="col-sm-3 col-form-label">Kode Barang</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="{{ old('kode_barang', $code->kode_barang) }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="attribute" class="col-sm-3 col-form-label">Nama Panel</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="attribute" name="attribute" value="{{ old('attribute', $code->attribute) }}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="unit_dasar" class="col-sm-3 col-form-label">Satuan Dasar</label>
                    <div class="col-sm-9">
                        <select class="form-control @error('unit_dasar') is-invalid @enderror" id="unit_dasar" name="unit_dasar" required>
                            <option value="">Pilih Satuan Dasar</option>
                            <option value="LBR" {{ old('unit_dasar', $code->unit_dasar) == 'LBR' ? 'selected' : '' }}>LBR (Lembar)</option>
                            <option value="KG" {{ old('unit_dasar', $code->unit_dasar) == 'KG' ? 'selected' : '' }}>KG (Kilogram)</option>
                            <option value="M" {{ old('unit_dasar', $code->unit_dasar) == 'M' ? 'selected' : '' }}>M (Meter)</option>
                            <option value="PCS" {{ old('unit_dasar', $code->unit_dasar) == 'PCS' ? 'selected' : '' }}>PCS (Pieces)</option>
                            <option value="PAK" {{ old('unit_dasar', $code->unit_dasar) == 'PAK' ? 'selected' : '' }}>PAK (Pack)</option>
                        </select>
                        @error('unit_dasar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="harga_jual" class="col-sm-3 col-form-label">Harga Jual per Satuan Dasar</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control @error('harga_jual') is-invalid @enderror" id="harga_jual" name="harga_jual" step="0.01" 
                            value="{{ old('harga_jual', $code->harga_jual) }}" required>
                        @error('harga_jual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="ongkos_kuli_default" class="col-sm-3 col-form-label">Ongkos Kuli Default</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control @error('ongkos_kuli_default') is-invalid @enderror" id="ongkos_kuli_default" name="ongkos_kuli_default" step="0.01" 
                            value="{{ old('ongkos_kuli_default', $code->ongkos_kuli_default) }}">
                        @error('ongkos_kuli_default')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-9 offset-sm-3">
                        <a href="{{ route('unit_conversion.index', $code->id) }}" class="btn btn-info">
                            <i class="fas fa-cogs mr-1"></i> Kelola Satuan Konversi
                        </a>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection