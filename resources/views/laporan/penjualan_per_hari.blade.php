@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Laporan Penjualan per Hari
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('laporan.penjualan-per-hari') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate->format('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <select class="form-control" id="customer_id" name="customer_id">
                                        <option value="">Semua Customer</option>
                                        @foreach(\App\Models\Customer::orderBy('nama')->get() as $customer)
                                            <option value="{{ $customer->kode_customer }}" 
                                                    {{ request('customer_id') == $customer->kode_customer ? 'selected' : '' }}>
                                                {{ $customer->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                        <a href="{{ route('laporan.penjualan-per-hari') }}" class="btn btn-secondary">
                                            <i class="fas fa-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($summary['total_hari'], 0, ',', '.') }}</h3>
                                    <p>Total Hari</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ number_format($summary['total_faktur'], 0, ',', '.') }}</h3>
                                    <p>Total Faktur</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>Rp {{ number_format($summary['total_omset'], 0, ',', '.') }}</h3>
                                    <p>Total Omset</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ number_format($summary['total_qty'], 0, ',', '.') }}</h3>
                                    <p>Total Qty</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Grafik Penjualan per Hari</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="salesChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Detail Penjualan per Hari</h3>
                                </div>
                                <div class="card-body">
                                    @if(count($laporanData) > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Tanggal</th>
                                                        <th>Hari</th>
                                                        <th>Jumlah Faktur</th>
                                                        <th>Total Omset</th>
                                                        <th>Total Qty</th>
                                                        <th>Rata-rata Omset/Faktur</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($laporanData as $data)
                                                        <tr>
                                                            <td>{{ $data['tanggal_formatted'] }}</td>
                                                            <td>
                                                                <span class="badge badge-info">{{ $data['hari'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-success">{{ $data['faktur_count'] }}</span>
                                                            </td>
                                                            <td class="text-right">
                                                                <strong>Rp {{ number_format($data['omset'], 0, ',', '.') }}</strong>
                                                            </td>
                                                            <td class="text-right">
                                                                {{ number_format($data['qty_total'], 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-right">
                                                                Rp {{ number_format($data['rata_omset_per_faktur'], 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-info" 
                                                                        onclick="showDetail('{{ $data['tanggal'] }}', {{ $data['transaksi']->count() }})">
                                                                    <i class="fas fa-eye"></i> Detail
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-info">
                                                        <th colspan="2">TOTAL</th>
                                                        <th class="text-center">{{ number_format($summary['total_faktur'], 0, ',', '.') }}</th>
                                                        <th class="text-right">Rp {{ number_format($summary['total_omset'], 0, ',', '.') }}</th>
                                                        <th class="text-right">{{ number_format($summary['total_qty'], 0, ',', '.') }}</th>
                                                        <th class="text-right">
                                                            Rp {{ number_format($summary['total_faktur'] > 0 ? $summary['total_omset'] / $summary['total_faktur'] : 0, 0, ',', '.') }}
                                                        </th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Tidak ada data penjualan untuk periode yang dipilih.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.tanggal),
            datasets: [{
                label: 'Omset (Rp)',
                data: chartData.map(item => item.omset),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Jumlah Faktur',
                data: chartData.map(item => item.faktur),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});

function showDetail(tanggal, count) {
    // console.log('showDetail called with tanggal:', tanggal, 'count:', count);
    
    $('#detailContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin"></i> Loading...
        </div>
    `);
    
    $('#detailModal').modal('show');
    
    // Load detail via AJAX
    const ajaxData = {
        start_date: tanggal,
        end_date: tanggal,
        customer_id: '{{ request("customer_id") ?: "" }}'
    };
    
    // console.log('AJAX Data:', ajaxData);
    
    $.ajax({
        url: '{{ route("laporan.penjualan-per-hari") }}',
        method: 'GET',
        data: ajaxData,
        success: function(response) {
            
            if (response.success) {
                let html = `
                    <h6>Detail Penjualan - ${tanggal}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>No Faktur</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                // Handle the data structure correctly
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(dayData) {
                        if (dayData.transaksi && dayData.transaksi.length > 0) {
                            dayData.transaksi.forEach(function(transaksi) {
                                html += `
                                    <tr>
                                        <td>${transaksi.no_faktur || '-'}</td>
                                        <td>${transaksi.customer ? transaksi.customer.nama : '-'}</td>
                                        <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(transaksi.grand_total || 0)}</td>
                                        <td>
                                            <span class="badge badge-${transaksi.status === 'completed' ? 'success' : 'warning'}">
                                                ${transaksi.status || '-'}
                                            </span>
                                        </td>
                                    </tr>
                                `;
                            });
                        }
                    });
                } else {
                    html += `
                        <tr>
                            <td colspan="4" class="text-center text-muted">Tidak ada data penjualan untuk tanggal ini</td>
                        </tr>
                    `;
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                $('#detailContent').html(html);
            } else {
                $('#detailContent').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${response.message || 'Tidak ada data yang ditemukan.'}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            // console.error('AJAX Error:', xhr, status, error);
            $('#detailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error loading detail data: ${error}
                </div>
            `);
        }
    });
}
</script>
@endpush
