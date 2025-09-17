@extends('layout.Nav')

@section('title', 'Laporan COGS Per Barang')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-box mr-2"></i>
                        Laporan COGS Per Barang
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('laporan.cogs') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($data['success'])
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5>Total Qty</h5>
                                        <h3>{{ number_format($data['summary']['total_qty'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5>Total Penjualan</h5>
                                        <h3>Rp {{ number_format($data['summary']['total_penjualan'], 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5>Total COGS</h5>
                                        <h3>Rp {{ number_format($data['summary']['total_cogs'], 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5>Total Margin</h5>
                                        <h3>Rp {{ number_format($data['summary']['total_margin'], 0, ',', '.') }}</h3>
                                        <small>{{ number_format($data['summary']['margin_percentage'], 2) }}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Rata-rata Harga Jual per Unit</h6>
                                        <h4>Rp {{ number_format($data['summary']['average_selling_price'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6>Rata-rata COGS per Unit</h6>
                                        <h4>Rp {{ number_format($data['summary']['average_cogs_per_unit'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Period Info -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <strong>Periode:</strong> {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                                    | <strong>Barang:</strong> {{ $kodeBarang }}
                                </div>
                            </div>
                        </div>

                        <!-- Batch Details -->
                        @if(count($data['batch_details']) > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Detail Batch FIFO</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Batch ID</th>
                                                        <th>Qty Diambil</th>
                                                        <th>Harga Modal</th>
                                                        <th>Total COGS</th>
                                                        <th>Tanggal Masuk</th>
                                                        <th>Batch Number</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($data['batch_details'] as $batch)
                                                    <tr>
                                                        <td>{{ $batch['batch_id'] }}</td>
                                                        <td class="text-right">{{ number_format($batch['qty_diambil'], 2) }}</td>
                                                        <td class="text-right">Rp {{ number_format($batch['harga_modal'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($batch['total_cogs'], 0, ',', '.') }}</td>
                                                        <td>{{ $batch['tanggal_masuk'] ? \Carbon\Carbon::parse($batch['tanggal_masuk'])->format('d/m/Y') : '-' }}</td>
                                                        <td>{{ $batch['batch_number'] ?? '-' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Tidak ada data batch untuk barang ini dalam periode yang dipilih.
                        </div>
                        @endif
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ $data['message'] ?? 'Terjadi kesalahan saat memproses data.' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
