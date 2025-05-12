@extends('layout.Nav')

@section('content')
<div class="container py-2">
    <h3>Daftar Transaksi</h3>

    <!-- Search and Date Filters -->
    <form action="{{ route('transaksi.index') }}" method="GET">
        <div class="row mb-3">
            <div class="col-md-4 mb-2">
                <input type="text" name="search" id="searchInput" class="form-control" 
                       placeholder="Cari No Transaksi atau Nama Customer" 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label for="start_date">Dari Tanggal</label>
                <input type="date" name="start_date" id="start_date" class="form-control" 
                       value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3 mb-2">
                <label for="end_date">Sampai Tanggal</label>
                <input type="date" name="end_date" id="end_date" class="form-control" 
                       value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered" id="transactionTable" style="border: 1px">
        <thead>
            <tr>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Alamat</th>
                <th>No HP</th>
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
                    <td>{{ $transaction->customer->alamat ?? 'N/A' }}</td>
                    <td>{{ $transaction->customer->hp }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('transaksi.shownota', $transaction->id) }}" class="btn btn-primary btn-sm">Lihat Nota</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $transactions->links() }}
    </div>
</div>
@endsection