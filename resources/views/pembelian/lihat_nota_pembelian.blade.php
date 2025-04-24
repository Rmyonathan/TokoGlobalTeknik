@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Daftar Nota Pembelian</h2>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Transaksi Pembelian</h5>
            <a href="{{ route('pembelian.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle"></i> Transaksi Baru
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No. Nota</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Cara Bayar</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->nota }}</td>
                            <td>{{ date('d-m-Y', strtotime($purchase->tanggal)) }}</td>
                            <td>{{ $purchase->supplierRelation->nama ?? $purchase->kode_supplier }}</td>
                            <td>{{ $purchase->cara_bayar }}</td>
                            <td class="text-right">Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('pembelian.nota.show', $purchase->id) }}" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                                <a href="{{ route('pembelian.nota.show', $purchase->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-print"></i> Cetak
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data transaksi pembelian</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection