@extends('layout.Nav')

@section('content')
<div class="container">
    <h2 class="mb-3">Detail Purchase Order</h2>

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>No. PO:</strong> {{ $po->no_po }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($po->tanggal)->format('d-m-Y') }}</p>
            <p><strong>Customer:</strong> {{ $po->kode_customer }} - {{ $po->customer->nama ?? 'N/A' }}</p>
            <p><strong>Sales:</strong> {{ $po->sales }}</p>
            <p><strong>Status:</strong>
                @if($po->status === 'pending')
                    <span class="badge badge-warning">Pending</span>
                @elseif($po->status === 'completed')
                    <span class="badge badge-success">Completed</span>
                @elseif($po->status === 'cancelled')
                    <span class="badge badge-danger">Batal</span>
                @endif
            </p>
            @if($po->tanggal_jadi)
                <p><strong>Tanggal Jadi:</strong> {{ \Carbon\Carbon::parse($po->tanggal_jadi)->format('d-m-Y') }}</p>
            @endif
        </div>
    </div>

    <h4>Daftar Item</h4>
    <table class="table table-bordered table-striped mt-2">
        <thead class="thead-dark">
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Keterangan</th>
                <th>Harga</th>
                <th>Length</th>
                <th>Qty</th>
                <th>Diskon</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $item)
                <tr>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>{{ $item->length }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ $item->diskon }}%</td>
                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right mt-3">
        <h5><strong>Grand Total:</strong> Rp {{ number_format($po->grand_total, 0, ',', '.') }}</h5>
    </div>
    
    <div class="d-flex justify-content-around mt-5">
        <a href="{{ route('transaksi.purchaseorder') }}" class="btn btn-secondary">Kembali ke Daftar PO</a>
        @if($po->status === 'pending')
        <form action="{{ route('purchase-order.complete', $po->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Yakin ingin selesaikan transaksi ini?');">
            @csrf
            <button type="submit" class="btn btn-success">Selesaikan Transaksi</button>
        </form>
        
        <form action="{{ route('purchase-order.cancel', $po->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Yakin ingin membatalkan PO ini?');">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-danger">Batalkan PO</button>
        </form>
    </div>
    @endif
</div>
@endsection
