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
                    <label for="length" class="col-sm-3 col-form-label">Panjang (m)</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control" id="length" name="length" step="0.01" value="{{ old('length', $code->length) }}" required>
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