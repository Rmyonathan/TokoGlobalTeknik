@extends('layout.Nav')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Invoice Transaksi</h3>
        </div>
        <div class="card-body">
            <!-- Informasi Transaksi -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>No Transaksi:</strong> {{ $transaction->no_transaksi }}</p>
                    <p><strong>Tanggal:</strong> {{ $transaction->tanggal }}</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <p><strong>Customer:</strong> {{ $transaction->customer->nama ?? 'N/A' }}</p>
                    <p><strong>Total:</strong> <span class="text-success">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span></p>
                </div>
            </div>

            <!-- Detail Barang -->
            <h5 class="mb-3">Detail Barang</h5>
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->items as $item)
                        <tr>
                            <td>{{ $item->kode_barang }}</td>
                            <td>{{ $item->nama_barang }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->diskon, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Rincian Transaksi -->
            <h5 class="mt-4">Rincian Transaksi</h5>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th>Subtotal</th>
                        <td>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Diskon Transaksi</th>
                        <td>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>PPN (11%)</th>
                        <td>Rp {{ number_format($transaction->ppn, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>DP</th>
                        <td>Rp {{ number_format($transaction->dp, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Grand Total</th>
                        <td><strong>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-center">
            <p class="mb-0">Terima kasih telah bertransaksi dengan kami!</p>
        </div>
    </div>
</div>
@endsection