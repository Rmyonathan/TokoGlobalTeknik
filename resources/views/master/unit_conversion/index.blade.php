@extends('layout.Nav')

@section('title', 'Kelola Satuan Konversi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-cogs"></i> Satuan Konversi untuk: {{ $kodeBarang->name }}
                        </h4>
                        <div>
                            <a href="{{ route('unit_conversion.create', $kodeBarang->id) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Satuan Konversi
                            </a>
                            <a href="{{ route('code.edit', $kodeBarang->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali ke Edit Barang
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Barang</h6>
                                    <p class="mb-1"><strong>Kode:</strong> {{ $kodeBarang->kode_barang }}</p>
                                    <p class="mb-1"><strong>Nama:</strong> {{ $kodeBarang->name }}</p>
                                    <p class="mb-1"><strong>Satuan Kecil:</strong> {{ $kodeBarang->unit_dasar }}</p>
                                    <p class="mb-1"><strong>Harga per {{ $kodeBarang->unit_dasar }}:</strong> Rp {{ number_format($kodeBarang->harga_jual, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Cara Kerja Konversi</h6>
                                    <p class="mb-1">• Harga dihitung berdasarkan satuan dasar ({{ $kodeBarang->unit_dasar }})</p>
                                    <p class="mb-1">• Satuan besar akan dikonversi ke satuan dasar</p>
                                    <p class="mb-0">• Contoh: 1 DUS = 40 {{ $kodeBarang->unit_dasar }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($unitConversions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No</th>
                                        <th>Satuan Besar</th>
                                        <th>Nilai Konversi</th>
                                        <th>Harga per Satuan Besar</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($unitConversions as $index => $conversion)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $conversion->unit_turunan }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    1 {{ $conversion->unit_turunan }} = {{ $conversion->nilai_konversi }} {{ $kodeBarang->unit_dasar }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>Rp {{ number_format($kodeBarang->harga_jual * $conversion->nilai_konversi, 0, ',', '.') }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    ({{ number_format($kodeBarang->harga_jual, 0, ',', '.') }} × {{ $conversion->nilai_konversi }})
                                                </small>
                                            </td>
                                            <td>{{ $conversion->keterangan ?? '-' }}</td>
                                            <td>
                                                @if($conversion->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('unit_conversion.edit', [$kodeBarang->id, $conversion->id]) }}" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form action="{{ route('unit_conversion.toggle_status', [$kodeBarang->id, $conversion->id]) }}" 
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-info" title="Toggle Status">
                                                            <i class="fas fa-toggle-on"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('unit_conversion.destroy', [$kodeBarang->id, $conversion->id]) }}" 
                                                          method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Yakin ingin menghapus satuan konversi ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> Belum ada satuan konversi yang dibuat.
                            <br>
                            <a href="{{ route('unit_conversion.create', $kodeBarang->id) }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Tambah Satuan Konversi Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
