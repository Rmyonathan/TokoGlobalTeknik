@extends('layout.Nav')

@section('content')
<div class="container">
    <h2 class="mb-4">Data Penjualan Per Customer</h2>

    <!-- Filter Customer -->
    <div class="card mb-4 p-3">
        <form method="GET" action="{{ route('transaksi.datapenjualanpercustomer') }}">
            <div class="row">
                <div class="col-md-4">
                    <label for="kode_customer" class="form-label">Pilih Customer</label>
                    <select id="kode_customer" name="kode_customer" class="form-select">
                        <option value="">-- Pilih Customer --</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->kode_customer }}" {{ request('kode_customer') == $customer->kode_customer ? 'selected' : '' }}>
                                {{ $customer->nama }} ({{ $customer->total_transaksi }} transaksi)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Transaksi -->
    @if (!empty($transaksi))
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No Transaksi</th>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th>Subtotal</th>
                        <th>Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksi as $item)
                        <tr>
                            <td>{{ $item->no_transaksi }}</td>
                            <td>{{ $item->tanggal }}</td>
                            <td>{{ $item->lokasi }}</td>
                            <td>{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td>{{ number_format($item->grand_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-center">Pilih customer untuk melihat transaksi.</p>
    @endif
</div>
@endsection