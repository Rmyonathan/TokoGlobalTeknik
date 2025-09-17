@extends('layout.Nav')

@section('title', 'Detail COGS Transaksi')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator mr-2"></i>
                        Detail COGS Transaksi: {{ $data['no_transaksi'] ?? 'N/A' }}
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
                                        <h5>Total Penjualan</h5>
                                        <h3>Rp {{ number_format($data['total_penjualan'], 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <h5>Total COGS</h5>
                                        <h3>Rp {{ number_format($data['total_cogs'], 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5>Total Margin</h5>
                                        <h3>Rp {{ number_format($data['total_margin'], 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5>Margin %</h5>
                                        <h3>{{ number_format($data['margin_percentage'], 2) }}%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Info -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Informasi Transaksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>No Transaksi:</strong> {{ $data['no_transaksi'] }}<br>
                                                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($data['tanggal'])->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Detail Item COGS</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Kode Barang</th>
                                                        <th>Nama Barang</th>
                                                        <th>Qty</th>
                                                        <th>Harga Jual</th>
                                                        <th>Total Jual</th>
                                                        <th>COGS/Unit</th>
                                                        <th>Total COGS</th>
                                                        <th>Margin</th>
                                                        <th>Margin %</th>
                                                        <th>Detail Batch</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($data['item_details'] as $item)
                                                    <tr>
                                                        <td>{{ $item['kode_barang'] }}</td>
                                                        <td>{{ $item['nama_barang'] }}</td>
                                                        <td class="text-right">{{ number_format($item['qty'], 2) }}</td>
                                                        <td class="text-right">Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($item['total_jual'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($item['cogs_per_unit'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($item['total_cogs'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($item['margin'], 0, ',', '.') }}</td>
                                                        <td class="text-right">{{ number_format($item['margin_percentage'], 2) }}%</td>
                                                        <td>
                                                            <button class="btn btn-sm btn-info" onclick="showBatchDetail('{{ $item['kode_barang'] }}', {{ json_encode($item['batch_details']) }})">
                                                                <i class="fas fa-eye"></i> Lihat
                                                            </button>
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

<!-- Modal Batch Detail -->
<div class="modal fade" id="batchDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Batch - <span id="batchKodeBarang"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
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
                        <tbody id="batchDetailTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showBatchDetail(kodeBarang, batchDetails) {
    $('#batchKodeBarang').text(kodeBarang);
    
    let html = '';
    batchDetails.forEach(function(batch) {
        html += `
            <tr>
                <td>${batch.batch_id}</td>
                <td class="text-right">${batch.qty_diambil}</td>
                <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(batch.harga_modal)}</td>
                <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(batch.total_cogs)}</td>
                <td>${batch.tanggal_masuk ? new Date(batch.tanggal_masuk).toLocaleDateString('id-ID') : '-'}</td>
                <td>${batch.batch_number || '-'}</td>
            </tr>
        `;
    });
    
    $('#batchDetailTableBody').html(html);
    $('#batchDetailModal').modal('show');
}
</script>
@endpush
