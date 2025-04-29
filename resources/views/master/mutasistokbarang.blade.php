@extends('layout.Nav')

@section('content')
<div class="container">
    <h2 class="text-center mb-4">Mutasi Stock Barang</h2>

    <!-- Display Barang -->
    <div class="card mb-4">
        <div class="card-header">Display Barang</div>
        <div class="card-body">

            @php
                $barangs = [
                    ['so' => 'ALUMKA', 'kode_barang' => 'ID4W', 'nama' => 'ALDERON ID 860 WHITE 400 CM', 'good_stock' => 1.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'LAMPUNG', 'kode_barang' => 'ID4W', 'nama' => 'ALDERON ID 860 WHITE 400 CM', 'good_stock' => 8.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'ALUMKA', 'kode_barang' => 'ID5,5W', 'nama' => 'ALDERON ID 860 WHITE 550 CM', 'good_stock' => 0.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'ALUMKA', 'kode_barang' => 'ID5B', 'nama' => 'ALDERON ID 860 BLUE 500 CM', 'good_stock' => 30.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'LAMPUNG', 'kode_barang' => 'ID5B', 'nama' => 'ALDERON ID 860 BLUE 500 CM', 'good_stock' => 13.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'ALUMKA', 'kode_barang' => 'ID5G', 'nama' => 'ALDERON ID 860 GREY 500 CM', 'good_stock' => 127.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'ALUMKA', 'kode_barang' => 'ID5M', 'nama' => 'ALDERON ID 830 ASA MAROON 530 CM', 'good_stock' => 0.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'ALUMKA', 'kode_barang' => 'ID5W', 'nama' => 'ALDERON ID 860 WHITE 500 CM', 'good_stock' => 5.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                    ['so' => 'LAMPUNG', 'kode_barang' => 'ID5W', 'nama' => 'ALDERON ID 860 WHITE 500 CM', 'good_stock' => 2.00, 'satuan' => 'LBR', 'bad_stock' => 0],
                ];
            @endphp

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>SO</th>
                        <th>Kode Barang</th>
                        <th>Nama</th>
                        <th>Good Stock</th>
                        <th>Satuan</th>
                        <th>Bad Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($barangs as $barang)
                        <tr>
                            <td>{{ $barang['so'] }}</td>
                            <td>{{ $barang['kode_barang'] }}</td>
                            <td>{{ $barang['nama'] }}</td>
                            <td>{{ number_format($barang['good_stock'], 2) }}</td>
                            <td>{{ $barang['satuan'] }}</td>
                            <td>{{ $barang['bad_stock'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-inline" method="GET" action="#">
                <div class="form-group mr-2">
                    <label>Kolom</label>
                    <select class="form-control ml-2" name="kolom">
                        <option value="kode_barang">Kode Barang</option>
                        <option value="nama">Nama Barang</option>
                    </select>
                </div>
                <div class="form-group mx-2">
                    <label>Value</label>
                    <input type="text" name="value" class="form-control ml-2" placeholder="Value">
                </div>
                <div class="form-group mx-2">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal_awal" class="form-control ml-2">
                    <label class="ml-2">s/d</label>
                    <input type="date" name="tanggal_akhir" class="form-control ml-2">
                </div>
                <button type="submit" class="btn btn-primary mx-2">Cari</button>
                <button type="reset" class="btn btn-secondary mx-2">Refresh</button>
                <button type="button" class="btn btn-danger mx-2">Exit</button>
                <button type="button" class="btn btn-success mx-2">Cetak Good Stock</button>
            </form>
        </div>
    </div>

    <!-- Mutasi Stock -->
    <div class="card">
        <div class="card-header">Mutasi Stock</div>
        <div class="card-body">

            @php
                $mutasis = [
                    ['no_transaksi' => 'BL-04/25-0010 (YUNI)', 'tanggal' => '2025-04-17 13:55', 'no_nota' => '01250596', 'supplier_customer' => 'PT ALDERON PRATAMA INDONESIA (02)', 'plus' => 30.00, 'minus' => 0.00, 'total' => 41.00],
                    ['no_transaksi' => 'ACP 0425-00275 (YUNI)', 'tanggal' => '2025-04-19 11:53', 'no_nota' => '-', 'supplier_customer' => 'SUMBER SUKSES (ASIP WIJAYA) (2984)', 'plus' => 0.00, 'minus' => 6.00, 'total' => 35.00],
                    ['no_transaksi' => 'ACP 0425-00409 (ADMIN)', 'tanggal' => '2025-04-24 14:12', 'no_nota' => '-', 'supplier_customer' => 'INDOSTEEL SUMBER BERKAT (3394)', 'plus' => 0.00, 'minus' => 5.00, 'total' => 30.00],
                ];
            @endphp

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>No Transaksi</th>
                        <th>Tanggal</th>
                        <th>No Nota/No Order</th>
                        <th>Supp./Cust.</th>
                        <th>+</th>
                        <th>-</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-danger font-weight-bold">
                        <td colspan="7">Saldo Awal</td>
                        <td class="text-right">11.00</td>
                    </tr>
                    @foreach($mutasis as $mutasi)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $mutasi['no_transaksi'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($mutasi['tanggal'])->format('d M Y H:i') }}</td>
                            <td>{{ $mutasi['no_nota'] }}</td>
                            <td>{{ $mutasi['supplier_customer'] }}</td>
                            <td>{{ number_format($mutasi['plus'], 2) }}</td>
                            <td>{{ number_format($mutasi['minus'], 2) }}</td>
                            <td>{{ number_format($mutasi['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
