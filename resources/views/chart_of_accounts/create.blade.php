@extends('layout.Nav')

@section('content')
<div class="container">
    <h1>Tambah Akun</h1>

    <form method="POST" action="{{ route('chart-of-accounts.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Kode</label>
            <input type="text" name="code" class="form-control" value="{{ old('code') }}">
            @error('code')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
            @error('name')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Tipe Akun</label>
            <select name="account_type_id" class="form-select">
                <option value="">- pilih -</option>
                @foreach($accountTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('account_type_id')==$type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            @error('account_type_id')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Parent</label>
            <select name="parent_id" class="form-select">
                <option value="">- none -</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id')==$parent->id)>{{ $parent->code }} - {{ $parent->name }}</option>
                @endforeach
            </select>
            @error('parent_id')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
            <label class="form-check-label">Aktif</label>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection


