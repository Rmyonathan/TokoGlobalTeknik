@extends('layout.Nav')

@section('content')
<div class="container mt-4">
    <div class="title-box mb-4">
        <h2><i class="fas fa-shopping-cart mr-2"></i> Purchase Order</h2>
    </div>

    <div class="card">
        <div class="card-header mb-4 bg-dark text-white">
            <h5 class="mb-0">Daftar Purchase Order</h5>
        </div>
        <div class="card-body p-0 d-flex justify-content-center">
            @if($purchaseOrders->isEmpty())
                <div class="alert alert-warning">
                    💤 Belum ada Purchase Order yang tercatat.
                </div>
            @else
                <table class="table table-striped mb-0" style="width: 95%;">
                    <thead class="thead-dark">
                        <tr>
                            <th>No. PO</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Sales</th>
                            <th>Total Item</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrders as $po)
                            <tr>
                                <td>{{ $po->no_po }}</td>
                                <td>{{ \Carbon\Carbon::parse($po->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $po->kode_customer }}</td>
                                <td>{{ $po->sales }}</td>
                                <td>{{ $po->items->sum('qty') }}</td>
                                <td>Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
                                <td>
                                    @if($po->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($po->status === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($po->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('purchase-order.show', $po->id) }}" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
