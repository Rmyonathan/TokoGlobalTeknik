@extends('layout.Nav')

@section('content')
<div class="container">
    <h3>Daftar Transaksi</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->no_transaksi }}</td>
                    <td>{{ $transaction->tanggal }}</td>
                    <td>{{ $transaction->customer->nama ?? 'N/A' }}</td>
                    <td>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('transaksi.nota', $transaction->id) }}" class="btn btn-primary btn-sm">Lihat Nota</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection