@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Detail Stock Adjustment</span>
                        <a href="{{ route('stock.adjustment.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informasi Adjustment</h5>
                            <table class="table table-bordered mt-3">
                                <tr>
                                    <th>Kode Adjustment</th>
                                    <td>#{{ $adjustment->id }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal</th>
                                    <td>{{ $adjustment->created_at->format('d-m-Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>{{ $adjustment->user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Keterangan</th>
                                    <td>{{ $adjustment->keterangan }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Informasi Barang</h5>
                            <table class="table table-bordered mt-3">
                                <tr>
                                    <th>Kode Barang</th>
                                    <td>{{ $adjustment->kode_barang }}</td>
                                </tr>
                                <tr>
                                    <th>Nama Barang</th>
                                    <td>{{ $adjustment->stock->nama_barang }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Detail Perubahan</h5>
                            <div class="card bg-light mt-3">
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6>Stok Awal</h6>
                                                    <h3>{{ $adjustment->quantity_before }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6>Selisih</h6>
                                                    <h3 class="{{ $adjustment->difference > 0 ? 'text-success' : ($adjustment->difference < 0 ? 'text-danger' : '') }}">
                                                        {{ $adjustment->difference > 0 ? '+' : '' }}{{ $adjustment->difference }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6>Stok Akhir</h6>
                                                    <h3>{{ $adjustment->quantity_after }}</h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection