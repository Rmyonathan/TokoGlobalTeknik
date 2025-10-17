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
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="auto_group_merek" name="auto_group_merek" value="1" checked>
                        <label class="form-check-label" for="auto_group_merek">
                            <strong>Otomatis kelompokkan merek</strong>
                        </label>
                        <div class="form-text">
                            Jika dicentang, sistem akan otomatis membuat grup barang berdasarkan kolom MERK. 
                            Jika tidak dicentang, semua barang akan masuk ke grup "GENERAL".
                        </div>
                    </div>
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
            
            <div class="mt-3">
                <h6>Penjelasan Kolom:</h6>
                <ul class="list-unstyled">
                    <li><strong>NO.</strong> (diabaikan) - Nomor urut</li>
                    <li><strong>TGL STOK</strong> (diabaikan) - Tanggal stok (menggunakan created_at)</li>
                    <li><strong>NAMA BRG</strong> (wajib) - Nama barang</li>
                    <li><strong>MERK</strong> (opsional) - Merek barang</li>
                    <li><strong>TYPE/UKURAN</strong> (opsional) - Ukuran atau tipe barang</li>
                    <li><strong>QTY</strong> (opsional) - Jumlah stok awal</li>
                    <li><strong>SAT</strong> (opsional) - Satuan dasar</li>
                    <li><strong>HARGA</strong> (opsional) - Harga beli barang. Harga jual akan dihitung otomatis dengan margin 30% dari harga beli</li>
                    <li><strong>JUMLAH</strong> (diabaikan) - Total nilai (QTY × HARGA)</li>
                    <li><strong>KETERANGAN BRG /(TGL BELI)</strong> (opsional) - Deskripsi atau catatan tambahan barang</li>
                    <li><strong>BY</strong> (opsional) - Nama orang yang menginput data barang</li>
                </ul>
            </div>
            
            <div class="alert alert-info mt-3">
                <h6><i class="fas fa-info-circle"></i> Catatan Penting:</h6>
                <ul class="mb-0">
                    <li>Kolom <strong>HARGA</strong> adalah harga beli barang dan akan disimpan sebagai <strong>cost</strong> di database</li>
                    <li>Harga jual akan dihitung otomatis dengan margin 30% dari harga beli</li>
                    <li>Kolom <strong>QTY</strong> akan disimpan sebagai stok awal barang</li>
                    <li>Format angka dengan koma harus dibungkus tanda kutip: <code>"79,000"</code></li>
                    <li><strong>Pengelompokan Merek:</strong>
                        <ul>
                            <li>Jika "Otomatis kelompokkan merek" dicentang: Kolom MERK akan otomatis membuat grup barang baru</li>
                            <li>Jika tidak dicentang: Semua barang akan masuk ke grup "GENERAL"</li>
                            <li>Grup barang yang sudah ada akan digunakan kembali</li>
                        </ul>
                    </li>
                    <li><strong>Generate Kode Barang:</strong>
                        <ul>
                            <li>Jika kolom kode_barang kosong, sistem akan generate otomatis berdasarkan merek</li>
                            <li>Format: 3 huruf pertama merek + 3 digit angka (contoh: SPI001, ADI001)</li>
                            <li>Jika merek kosong: Format AUTO-YYYYMMDD-###</li>
                            <li>Angka akan otomatis increment untuk merek yang sama</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
@endsection


