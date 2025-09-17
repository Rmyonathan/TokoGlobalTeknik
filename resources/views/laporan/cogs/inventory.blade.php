@extends('layout.Nav')

@section('title', 'Nilai Persediaan Saat Ini')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-warehouse mr-2"></i>
                        Nilai Persediaan Saat Ini (FIFO)
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
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5>Total Qty</h5>
                                        <h3>{{ number_format($data['summary']['total_qty'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5>Total Nilai</h5>
                                        <h3>Rp {{ number_format($data['summary']['total_value'], 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5>Rata-rata Cost</h5>
                                        <h3>Rp {{ number_format($data['summary']['average_cost'], 0, ',', '.') }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Info -->
                        @if($kodeBarang)
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-filter mr-2"></i>
                                    <strong>Filter:</strong> Barang {{ $kodeBarang }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Barang Details -->
                        @if(count($data['barang_details']) > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Detail Persediaan per Barang</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Kode Barang</th>
                                                        <th>Nama Barang</th>
                                                        <th>Total Qty</th>
                                                        <th>Total Nilai</th>
                                                        <th>Rata-rata Cost</th>
                                                        <th>Jumlah Batch</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($data['barang_details'] as $barang)
                                                    <tr>
                                                        <td>{{ $barang['kode_barang'] }}</td>
                                                        <td>{{ $barang['nama_barang'] }}</td>
                                                        <td class="text-right">{{ number_format($barang['total_qty'], 2) }}</td>
                                                        <td class="text-right">Rp {{ number_format($barang['total_value'], 0, ',', '.') }}</td>
                                                        <td class="text-right">Rp {{ number_format($barang['total_qty'] > 0 ? $barang['total_value'] / $barang['total_qty'] : 0, 0, ',', '.') }}</td>
                                                        <td class="text-center">{{ count($barang['batches']) }}</td>
                                                        <td>
                                                            <button class="btn btn-sm btn-info" onclick="showBatchDetail('{{ $barang['kode_barang'] }}', {{ json_encode($barang['batches']) }})">
                                                                <i class="fas fa-eye"></i> Lihat Batch
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
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Tidak ada data persediaan yang tersedia.
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
                                <th>Qty Sisa</th>
                                <th>Harga Beli</th>
                                <th>Total Nilai</th>
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
function showBatchDetail(kodeBarang, batches) {
    $('#batchKodeBarang').text(kodeBarang);
    
    let html = '';
    batches.forEach(function(batch) {
        const totalNilai = batch.qty_sisa * batch.harga_beli;
        html += `
            <tr>
                <td>${batch.batch_id}</td>
                <td class="text-right">${batch.qty_sisa}</td>
                <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(batch.harga_beli)}</td>
                <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(totalNilai)}</td>
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
