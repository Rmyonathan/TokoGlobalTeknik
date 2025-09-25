@extends('layout.Nav')

@section('title', 'Detail Persediaan Barang')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-box mr-2"></i>
                        Detail Persediaan: {{ $barang['kode_barang'] }} - {{ $barang['nama_barang'] }}
                    </h3>
                    <div>
                        <a href="{{ route('laporan.cogs') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white"><div class="card-body">
                                <h6>Total Qty</h6>
                                <h4>{{ number_format($barang['total_qty'], 2) }}</h4>
                            </div></div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white"><div class="card-body">
                                <h6>Total Nilai</h6>
                                <h4>Rp {{ number_format($barang['total_value'], 0, ',', '.') }}</h4>
                            </div></div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white"><div class="card-body">
                                <h6>Rata-rata Cost</h6>
                                <h4>Rp {{ number_format($barang['average_cost'], 0, ',', '.') }}</h4>
                            </div></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Batch ID</th>
                                    <th>Qty Sisa</th>
                                    <th>Harga Beli</th>
                                    <th>Total Nilai</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Batch Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($barang['batches'] ?? []) as $b)
                                <tr>
                                    <td>{{ $b['batch_id'] }}</td>
                                    <td class="text-right">{{ number_format($b['qty_sisa'], 2) }}</td>
                                    <td class="text-right">Rp {{ number_format($b['harga_beli'], 0, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format(($b['qty_sisa'] ?? 0) * ($b['harga_beli'] ?? 0), 0, ',', '.') }}</td>
                                    <td>{{ $b['tanggal_masuk'] ? \Carbon\Carbon::parse($b['tanggal_masuk'])->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $b['batch_number'] ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center">Tidak ada batch.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


