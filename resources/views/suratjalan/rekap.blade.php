@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Rekap Surat Jalan
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('suratjalan.create-faktur') }}" class="btn btn-sm btn-success mr-2">
                            <i class="fas fa-file-invoice"></i> Buat Faktur dari Multiple SJ
                        </a>
                        <a href="{{ route('suratjalan.history') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-list"></i> Lihat History
                        </a>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="card-body">
                    <form method="GET" action="{{ route('suratjalan.rekap') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ request('end_date', now()->format('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <select class="form-control" id="customer_id" name="customer_id">
                                        <option value="">Semua Customer</option>
                                        @foreach($customers as $customer)
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
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="sudah_faktur" {{ request('status') == 'sudah_faktur' ? 'selected' : '' }}>Sudah Faktur</option>
                                        <option value="belum_faktur" {{ request('status') == 'belum_faktur' ? 'selected' : '' }}>Belum Faktur</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="search">Pencarian</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="No. Surat Jalan, No. PO, atau Nama Customer"
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sort_by">Urutkan Berdasarkan</label>
                                    <select class="form-control" id="sort_by" name="sort_by">
                                        <option value="tanggal" {{ request('sort_by') == 'tanggal' ? 'selected' : '' }}>Tanggal</option>
                                        <option value="no_suratjalan" {{ request('sort_by') == 'no_suratjalan' ? 'selected' : '' }}>No. Surat Jalan</option>
                                        <option value="kode_customer" {{ request('sort_by') == 'kode_customer' ? 'selected' : '' }}>Customer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sort_order">Urutan</label>
                                    <select class="form-control" id="sort_order" name="sort_order">
                                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Terlama</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('suratjalan.rekap') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($stats['total_surat_jalan']) }}</h3>
                                    <p>Total Surat Jalan</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ number_format($stats['sudah_faktur']) }}</h3>
                                    <p>Sudah Faktur</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($stats['belum_faktur']) }}</h3>
                                    <p>Belum Faktur</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3>{{ number_format($stats['total_qty']) }}</h3>
                                    <p>Total Quantity</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Surat Jalan per Hari (7 Hari Terakhir)</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="dailyChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Status Surat Jalan</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Top 5 Customers</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="customerChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Data Surat Jalan</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No. Surat Jalan</th>
                                            <th>Tanggal</th>
                                            <th>Customer</th>
                                            <th>No. PO</th>
                                            <th>Status</th>
                                            <th>Total Items</th>
                                            <th>Total Qty</th>
                                            <th>No. Transaksi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($suratJalan as $sj)
                                            <tr>
                                                <td>
                                                    <strong>{{ $sj->no_suratjalan }}</strong>
                                                </td>
                                                <td>{{ $sj->tanggal ? $sj->tanggal : '-' }}</td>
                                                <td>{{ $sj->customer->nama ?? '-' }}</td>
                                                <td>{{ $sj->no_po ?? '-' }}</td>
                                                <td>
                                                    @if($sj->no_transaksi)
                                                        <span class="badge badge-success">Sudah Faktur</span>
                                                    @else
                                                        <span class="badge badge-warning">Belum Faktur</span>
                                                    @endif
                                                </td>
                                                <td>{{ $sj->total_items ?? 0 }}</td>
                                                <td>{{ number_format($sj->total_qty ?? 0) }}</td>
                                                <td>{{ $sj->no_transaksi ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('suratjalan.detail', $sj->id) }}" 
                                                       class="btn btn-sm btn-info" title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">Tidak ada data surat jalan</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $suratJalan->links() }}
                            </div>
                        </div>
                    </div>
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
    // Chart 1: Daily Data
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    const dailyData = @json($chartData['daily']);
    const dailyLabels = Object.keys(dailyData);
    const dailyValues = Object.values(dailyData);
    
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Surat Jalan',
                data: dailyValues,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Chart 2: Status Data
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($chartData['status']);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Chart 3: Customer Data
    const customerCtx = document.getElementById('customerChart').getContext('2d');
    const customerData = @json($chartData['customers']);
    
    new Chart(customerCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(customerData),
            datasets: [{
                label: 'Jumlah Surat Jalan',
                data: Object.values(customerData),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush
