@extends('layout.Nav')

@section('content')
<div class="container">
    <h2 class="title-box">Tambah Kas Baru</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('kas.store') }}">
        @csrf

        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Deskripsi x Qty</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            @error('description') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Nominal</label>
            <input type="number" name="qty" class="form-control" required value="{{ old('qty') }}">
            @error('qty') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        

        <div class="form-group">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="">--Pilih Type--</option>
                <option value="Kredit" {{ old('type') == 'Kredit' ? 'selected' : '' }}>Kredit</option>
                <option value="Debit" {{ old('type') == 'Debit' ? 'selected' : '' }}>Debit</option>
            </select>
            @error('type') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Kas</button>
    </form>
</div>
@endsection
