@extends('layout.Nav')

@section('content')
<div class="container bg-light p-4 rounded">
    <h4>:: Detail Surat Jalan ::</h4>
    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>No Surat Jalan:</strong> {{ $suratJalan->no_suratjalan }}</p>
            <p><strong>Tanggal:</strong> {{ $suratJalan->tanggal }}</p>
            <p><strong>Customer:</strong> {{ $suratJalan->customer->nama }}</p>
            <p><strong>Alamat:</strong> {{ $suratJalan->alamat }}</p>
            <p><strong>Alamat di Surat Jalan:</strong> {{ $suratJalan->alamat_suratjalan }}</p>
            <p><strong>No Faktur:</strong> {{ $suratJalan->no_transaksi }}</p>
            <p><strong>Tanggal Transaksi:</strong> {{ $suratJalan->tanggal_transaksi }}</p>
            <p><strong>Status Barang:</strong> {{ $suratJalan->status_barang }}</p>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
    <h5>Detail Item</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Stock Owner</th>
                <th>Kode Barang</th>
                <th>Keterangan</th>
                <th>Panjang</th>
                <th>Lebar</th>
                <th>Qty Dibawa</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suratJalan->items as $item)
                <tr>
                    <td>{{ $item->transaksiItem->stock_owner }}</td>
                    <td>{{ $item->transaksiItem->kode_barang }}</td>
                    <td>{{ $item->transaksiItem->keterangan }}</td>
                    <td>{{ $item->transaksiItem->panjang }}</td>
                    <td>{{ $item->transaksiItem->lebar }}</td>
                    <td>{{ $item->qty_dibawa }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="row mt-3">
        <div class="col-md-12 text-right">
            <a href="{{ route('suratjalan.history') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .container, .container * {
            visibility: visible;
        }
        .container {
            position: absolute;
            top: 0;
            left: 0;
        }
        .btn {
            display: none;
        }
    }
</style>
@endsection