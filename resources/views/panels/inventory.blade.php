@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-warehouse mr-2"></i>Aluminum Panel Inventory</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-boxes mr-1"></i> Current Inventory</h5>
                    <div>
                        <a href="{{ route('panels.create-inventory') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Add Panels
                        </a>
                        <a href="{{ route('panels.create-order') }}" class="btn btn-secondary btn-sm ml-2">
                            <i class="fas fa-cut mr-1"></i> Potong
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($inventory) && count($inventory['inventory_by_length']) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kode Barang</th>
                                        <th>Name</th>
                                        <th>Harga Beli</th>
                                        <th>Harga Jual</th>
                                        <th>Length (meters)</th>
                                        <th>Available Quantity</th>
                                        <th>Total Length (meters)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventory['inventory_by_length'] as $item)
                                        <tr>
                                            <td>{{ $item['id'] }}</td>
                                            <td>{{ $item['group_id'] }}</td>
                                            <td>{{ $item['name'] }}</td>
                                            <td>Rp. {{ number_format($item['cost'], 2) }}</td>
                                            <td>Rp. {{ number_format($item['price'], 2) }}</td>
                                            <td>{{ number_format($item['length'], 2) }}</td>
                                            <td>{{ $item['quantity'] }}</td>
                                            <td>{{ number_format($item['length'] * $item['quantity'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>Total</th>
                                        <th>{{ $inventory['total_panels'] }}</th>
                                        <th>
                                            @php
                                                $totalLength = 0;
                                                foreach($inventory['inventory_by_length'] as $item) {
                                                    $totalLength += $item['length'] * $item['quantity'];
                                                }
                                                echo number_format($totalLength, 2);
                                            @endphp
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i> No panels currently in inventory.
                        </div>
                        <a href="{{ route('panels.create-inventory') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Add Panels to Inventory
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
                    <h5 class="mb-0"><i class="fas fa-chart-pie mr-1"></i> Inventory Distribution</h5>
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
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Inventory Management</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="fas fa-search mr-2 text-primary"></i> <strong>View Inventory:</strong> See all available panels by length
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-plus mr-2 text-success"></i> <strong>Add Panels:</strong> Add new panels to inventory
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-shopping-cart mr-2 text-danger"></i> <strong>Process Orders:</strong> Fulfill customer orders with optimal panel usage
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-recycle mr-2 text-warning"></i> <strong>Auto-Reuse:</strong> The system automatically tracks remnants from cutting
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

        // Generate different colors
        backgroundColors.push(
            'rgb(' + Math.floor(Math.random() * 200) + ','
            + Math.floor(Math.random() * 200) + ','
            + Math.floor(Math.random() * 200) + ')'
        );

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
