@extends('layout.Nav')

@section('content')
<section id="import-kode-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Import Barang (CSV)</h2>
        <a href="{{ route('master.barang') }}" class="btn btn-secondary">Kembali</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div><strong>Terjadi kesalahan:</strong></div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Upload File CSV</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('code.import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Pilih File (.csv)</label>
                    <input type="file" class="form-control" id="file" name="file" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Format Kolom CSV</h5>
        </div>
        <div class="card-body">
            <p class="mb-2">Gunakan header berikut di baris pertama file CSV:</p>
            <code>
                {{ implode(',', $sampleHeaders) }}
            </code>
            <p class="mt-2 mb-0"><small>Kolom wajib: <strong>kode_barang</strong>, <strong>name</strong>. Kolom lain opsional.</small></p>
            <p class="mt-1 mb-0"><small>Jika kolom <strong>attribute</strong> diisi dan cocok dengan nama pada master Grup Barang, barang akan otomatis ditautkan.</small></p>
        </div>
    </div>
</section>
@endsection


