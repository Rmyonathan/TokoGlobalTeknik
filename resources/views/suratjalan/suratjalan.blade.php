@extends('layout.Nav')

@section('content')
<div class="container bg-light p-4 rounded">
    <h4>:: Surat Jalan ::</h4>

    <div class="row mb-3">
        <div class="col-md-3">
            <label>No Surat Jalan</label>
            <input type="text" class="form-control bg-warning text-white font-weight-bold" value="SJ-0425-00140" readonly>
        </div>
        <div class="col-md-3">
            <label>Tanggal</label>
            <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label>Customer</label>
            <input type="text" class="form-control" placeholder="Nama Customer">
        </div>
        <div class="col-md-6">
            <label>Alamat Di Surat Jalan</label>
            <input type="text" class="form-control" placeholder="Alamat">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <label>No Faktur</label>
            <input type="text" class="form-control" placeholder="Nomor Faktur">
        </div>
        <div class="col-md-3">
            <label>Tanggal Transaksi</label>
            <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
        </div>
    </div>

    <h5 class="mt-4">Detail Item</h5>
    <div class="table-responsive mb-3">
        <table class="table table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th>Stock Owner</th>
                    <th>Kode Barang</th>
                    <th>Keterangan</th>
                    <th>P</th>
                    <th>L</th>
                    <th>Qty</th>
                    <th>Pernah Ambil</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                <!-- Baris kosong untuk diisi nanti -->
                <tr>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                </tr>
            </tbody>
        </table>
    </div>

    <h5>Item Yang Akan Dibawa</h5>
    <div class="table-responsive mb-3">
        <table class="table table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th>Stock Owner</th>
                    <th>Kode Barang</th>
                    <th>Keterangan</th>
                    <th>P</th>
                    <th>L</th>
                    <th>Qty Dibawa</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                    <td><input type="text" class="form-control" /></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="text-end">
        <button class="btn btn-primary">Simpan</button>
        <button class="btn btn-secondary">Tutup</button>
    </div>
</div>
@endsection