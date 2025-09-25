@extends('layout.Nav')

@section('title', 'Laporan COGS Per Periode')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator mr-2"></i>
                        Laporan COGS Per Periode
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
                                        <h5>Total Transaksi</h5>
                                        <h3>{{ $data['summary']['total_transaksi'] }}</h3>
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

                        <!-- Period Info -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <strong>Periode:</strong> {{ \Carbon\Carbon::parse($data['periode']['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data['periode']['end_date'])->format('d/m/Y') }}
                                    @if($kodeBarang)
                                        | <strong>Barang:</strong> {{ $kodeBarang }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Barang Summary -->
                        @if(count($data['barang_summary']) > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Ringkasan per Barang</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Kode Barang</th>
                                                        <th>Nama Barang</th>
                                                        <th>Total Qty</th>
                                                        <th>Total Penjualan</th>
                                                        <th>Total COGS</th>
                                                        <th>Total Margin</th>
                                                        <th>Margin %</th>
                                                        <th>COGS/Unit</th>
                                                        <th>Margin/Unit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($data['barang_summary'] as $barang)
                                                    <tr>
                                                        <td>{{ $barang['kode_barang'] }}</td>
                                                        <td>{{ $barang['nama_barang'] }}</td>
                                                        <td class="text-right">{{ number_format($barang['total_qty'], 2) }}</td>
                                                        <td class="text-right">Rp {{ number_format($barang['total_penjualan'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($barang['total_cogs'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($barang['total_margin'], 0, ',', '.') }}</td>
                                                        <td class="text-right">{{ number_format($barang['margin_percentage'], 2) }}%</td>
                                                        <td class="text-right">Rp {{ number_format($barang['cogs_per_unit'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($barang['margin_per_unit'] ?? 0, 0, ',', '.') }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Transaction Details -->
                        @if(count($data['transaksi_details']) > 0)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Detail Transaksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>No Transaksi</th>
                                                        <th>Tanggal</th>
                                                        <th>Total Penjualan</th>
                                                        <th>Total COGS</th>
                                                        <th>Total Margin</th>
                                                        <th>Margin %</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($data['transaksi_details'] as $transaksi)
                                                    <tr>
                                                        <td>{{ $transaksi['no_transaksi'] }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($transaksi['tanggal'])->format('d/m/Y') }}</td>
                                                        <td class="text-right">Rp {{ number_format($transaksi['total_penjualan'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($transaksi['total_cogs'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($transaksi['total_margin'], 0, ',', '.') }}</td>
                                                        <td class="text-right">{{ number_format($transaksi['margin_percentage'], 2) }}%</td>
                                                        <td>
                                                            <a href="{{ route('laporan.cogs.detail', $transaksi['transaksi_id']) }}" class="btn btn-sm btn-info" target="_blank">
                                                                <i class="fas fa-eye"></i> Detail
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
