@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list mr-1"></i> Order #{{ $order->id }} Details</h5>
                    <div>
                        <a href="{{ route('panels.print-receipt', $order->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-print mr-1"></i> Print Receipt
                        </a>
                        <a href="{{ route('panels.repack') }}" class="btn btn-sm btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Order Information</h6>
                            <p><strong>Order ID:</strong> {{ $order->id }}</p>
                            <p><strong>Date:</strong> {{ date('d-m-Y H:i', strtotime($order->created_at)) }}</p>
                            <p><strong>Status:</strong> <span class="badge badge-success">{{ ucfirst($order->status) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Panel Summary</h6>
                            @if($order->notes && strpos($order->notes, 'Repack from') !== false)
                                @php
                                    preg_match('/Repack from (.*?) to (.*?)$/', $order->notes, $matches);
                                    $sourcePanel = $matches[1] ?? 'Unknown';
                                    $resultPanel = $matches[2] ?? 'Unknown';
                                @endphp
                                <p><strong>Operation Type:</strong> Repack / Conversion</p>
                                <p><strong>Source Panel:</strong> {{ $sourcePanel }}</p>
                                <p><strong>Result Panel:</strong> {{ $order->name }}</p>
                            @else
                                <p><strong>Operation Type:</strong> Panel Cutting</p>
                                <p><strong>Result Panel:</strong> {{ $order->name }}</p>
                            @endif
                            <p><strong>Quantity:</strong> {{ $order->total_quantity }}</p>
                            <p><strong>Total Length:</strong> {{ number_format($order->total_length, 2) }} meters</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted mb-3">Panel Details</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item ID</th>
                                    <th>Panel ID</th>
                                    <th>Original Panel</th>
                                    <th>Original Length</th>
                                    <th>Result Length</th>
                                    <th>Remaining Length</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->panel_id }}</td>
                                    <td>
                                        @if($item->panel)
                                            {{ $item->panel->name }} ({{ $item->panel->group_id }})
                                        @else
                                            Unknown
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->original_panel_length, 2) }} m</td>
                                    <td>{{ number_format($item->length, 2) }} m</td>
                                    <td>{{ number_format($item->remaining_length, 2) }} m</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($order->notes)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-2">Notes</h6>
                            <div class="p-3 bg-light">{{ $order->notes }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection