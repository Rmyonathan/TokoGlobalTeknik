<!-- resources/views/datapenjualanpercustomer.blade.php -->
@extends('layout.Nav')

@section('content')
<div class="container">
    <h1 class="mb-4">Display Data Penjualan Per Customer</h1>

    <!-- Filter Customers -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="">
                <div class="col-md-3">
                    <label for="kolom" class="form-label">Kolom</label>
                    <select class="form-select" id="kolom" name="kolom">
                        <option value="nama">Nama</option>
                        <option value="kode_customer">Kode Customer</option>
                        <option value="alamat">Alamat</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="value" class="form-label">Value</label>
                    <input type="text" class="form-control" id="value" name="value" placeholder="Cari...">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Cari</button>
                    <a href="{{ route('transaksi.datapenjualanpercustomer') }}" class="btn btn-secondary">Tampil Semua</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Customers -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Kategori</th>
                    <th>Kode Customer</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th>HP</th>
                    <th>Fax</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dummy Data -->
                <tr>
                    <td>UMUM</td>
                    <td>1</td>
                    <td>CASH</td>
                    <td>PALEMBANG</td>
                    <td>-</td>
                    <td>082114953334</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>UMUM</td>
                    <td>10</td>
                    <td>ADITYA KERTANUGRAHA</td>
                    <td>JL. DEMANG LEBAR DAUN NO.3 SEBELAH KANTOR. BPK</td>
                    <td>-</td>
                    <td>082177680509</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>UMUM</td>
                    <td>100</td>
                    <td>FEBRI</td>
                    <td>PALEMBANG</td>
                    <td>-</td>
                    <td>081379221117</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>UMUM</td>
                    <td>1000</td>
                    <td>IBU DIAH</td>
                    <td>BUKIT KENCANA</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>UMUM</td>
                    <td>1001</td>
                    <td>CV BERMAKARYA</td>
                    <td>JL. SELINCAH RAYA 2 NO 335 RT 19 RW 13 SIALANG, SAKO</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>UMUM</td>
                    <td>1002</td>
                    <td>Purwanto</td>
                    <td>Korpri Sukarame / Bengkel Hanif Jaya Jl. Pramuka</td>
                    <td>-</td>
                    <td>081510067125</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>UMUM</td>
                    <td>1003</td>
                    <td>SUPONO</td>
                    <td>JL. H. KOMARUDIN GG. ABADI NO RAJA BASA</td>
                    <td>-</td>
                    <td>085218857451</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td>UMUM</td>
                    <td>1004</td>
                    <td>KABA</td>
                    <td>-</td>
                    <td>-</td>
                    <td>081273184889</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Filter Tanggal -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET" action="">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Transaksi</label>
                    <input type="date" class="form-control" name="start_date" value="2025-04-14">
                </div>
                <div class="col-md-3">
                    <label class="form-label">s/d</label>
                    <input type="date" class="form-control" name="end_date" value="2025-04-14">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Penjualan -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>No Transaksi</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Harga</th>
                    <th>P</th>
                    <th>L</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Disc(%)</th>
                    <th>Disc(Rp.)</th>
                    <th>Sub Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dummy Data Kosong -->
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data transaksi</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
