@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-cut mr-2"></i>Repack / Potong Aluminium Panel</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    <!-- History Pemotongan Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history mr-1"></i> History Pemotongan</h5>
                    <div class="d-flex align-items-center">
                        <form class="form-inline mr-2" method="GET">
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control form-control-sm" name="date_filter" value="{{ request('date_filter', date('Y-m-d')) }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-filter"></i></button>
                                </div>
                            </div>
                        </form>
                        <a href="{{ route('panels.create-order') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-cut mr-1"></i> Potong
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($cuttingHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID Order</th>
                                        <th>Tanggal</th>
                                        <th>Source Panel</th>
                                        <th>Result Panel</th>
                                        <th>Jumlah</th>
                                        <th>Total Panjang (m)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cuttingHistory as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ date('d-m-Y H:i', strtotime($order->created_at)) }}</td>
                                            <td>
                                                @php
                                                    $sourcePanel = '(Unknown)';
                                                    $orderItem = $order->orderItems->first(); // Get the first order item
                                                    if ($orderItem && $orderItem->panel) {
                                                        $sourcePanel = $orderItem->panel->name ?? '(Unknown)';
                                                        if ($orderItem->panel->group_id) {
                                                            $sourcePanel .= ' (' . $orderItem->panel->group_id . ')';
                                                        }
                                                    }
                                                @endphp
                                                {{ $sourcePanel }}
                                            </td>
                                            <td>{{ $order->name }}</td>
                                            <td>{{ $order->total_quantity }}</td>
                                            <td>{{ number_format($order->total_length, 2) }}</td>
                                            <td>
                                                <a href="{{ route('panels.print-receipt', $order->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="{{ route('panels.view-order', $order->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- After the table, add this: -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $cuttingHistory->links() }}
                    </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> Belum ada history pemotongan untuk ditampilkan.
                        </div>
                        <a href="{{ route('panels.create-order') }}" class="btn btn-primary">
                            <i class="fas fa-cut mr-1"></i> Potong Panel
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-pie mr-1"></i> Inventory Overview</h5>
                </div>
                <div class="card-body">
                    @if(isset($inventory) && count($inventory['inventory_by_length']) > 0)
                        <div class="chart-container" style="position: relative; height:250px;">
                            <canvas id="inventoryChart"></canvas>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i> No data available for chart.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cut mr-1"></i> Repack Management</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="fas fa-cut mr-2 text-primary"></i> <strong>Potong Panel:</strong> Cut panels according to customer requirements
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-history mr-2 text-info"></i> <strong>Track History:</strong> View complete cutting history with date filter
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-print mr-2 text-success"></i> <strong>Print Receipt:</strong> Generate receipts for customers
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-recycle mr-2 text-warning"></i> <strong>Manage Remnants:</strong> System automatically tracks and reuses remnants
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($inventory) && count($inventory['inventory_by_length']) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('inventoryChart').getContext('2d');

    // Prepare data for chart
    const lengths = [];
    const quantities = [];
    const backgroundColors = [];

    @foreach($inventory['inventory_by_length'] as $item)
        lengths.push('{{ number_format($item['length'], 2) }}m');
        quantities.push({{ $item['quantity'] }});
        // Generate different colors
        backgroundColors.push(
            'rgb(' + Math.floor(Math.random() * 200) + ','
            + Math.floor(Math.random() * 200) + ','
            + Math.floor(Math.random() * 200) + ')'
        );
    @endforeach

    const inventoryChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: lengths,
            datasets: [{
                label: 'Panel Quantity',
                data: quantities,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' panels';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection