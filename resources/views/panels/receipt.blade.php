@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-receipt mr-1"></i> Receipt for Order #{{ $order->id }}</h5>
                    <div>
                        <button class="btn btn-sm btn-primary" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                        <a href="{{ route('panels.repack') }}" class="btn btn-sm btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Order Details</h6>
                            <p><strong>Order ID:</strong> {{ $order->id }}</p>
                            <p><strong>Date:</strong> {{ date('d-m-Y H:i', strtotime($order->created_at)) }}</p>
                            <p><strong>Status:</strong> <span class="badge badge-success">{{ ucfirst($order->status) }}</span></p>
                        </div>
                    </div>

                    <hr>
                    
                    <h6 class="text-muted mb-3">Panel Cutting Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Source Panel</th>
                                    <th>Source Length</th>
                                    <th>Result Panel</th>
                                    <th>Result Length</th>
                                    <th>Result Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>
                                        @if($item->panel)
                                            {{ $item->panel->name }} ({{ $item->panel->group_id }})
                                        @else
                                            Unknown
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->original_panel_length, 2) }} m</td>
                                    <td>{{ $order->name }}</td>
                                    <td>{{ number_format($item->length, 2) }} m</td>
                                    <td>
                                        @php
                                            // Calculate how many result panels were created from this source panel
                                            $resultCount = 0;
                                            if ($item->length > 0) {
                                                $resultCount = floor(($item->original_panel_length - $item->remaining_length) / $item->length);
                                            }
                                            echo $resultCount;
                                        @endphp
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total</th>
                                    <th>{{ number_format($order->total_length, 2) }} m</th>
                                    <th>{{ $order->total_quantity }}</th>
                                </tr>
                            </tfoot>
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
                <div class="card-footer text-center">
                    <p class="mb-0 text-muted small">Receipt generated on {{ date('d-m-Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    .card, .card * {
        visibility: visible;
    }
    .card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn {
        display: none;
    }
}
</style>
@endsection