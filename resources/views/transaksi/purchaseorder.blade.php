@extends('layout.Nav')

@section('content')
<div class="container mt-4">
    <div class="title-box mb-4">
        <h2><i class="fas fa-shopping-cart mr-2"></i> Purchase Order</h2>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Purchase Order</h5>
        </div>
        <div class="card-body p-0 d-flex justify-content-center">
            <table class="table table-striped mb-0" style="width: 95%;">

                <thead class="thead-dark">
                    <tr>
                        <th>No. PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data Dummy -->
                    <tr>
                        <td>PO-001-00001</td>
                        <td>2025-05-01</td>
                        <td>PT. Sumber Makmur</td>
                        <td>5</td>
                        <td>Rp 2.350.000</td>
                        <td><span class="badge badge-success">Selesai</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <td>PO-001-00002</td>
                        <td>2025-05-03</td>
                        <td>CV. Berkah Abadi</td>
                        <td>3</td>
                        <td>Rp 1.120.000</td>
                        <td><span class="badge badge-warning">Menunggu</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    <tr>
                        <td>PO-001-00003</td>
                        <td>2025-05-05</td>
                        <td>PT. Sinar Terang</td>
                        <td>8</td>
                        <td>Rp 3.800.000</td>
                        <td><span class="badge badge-danger">Dibatalkan</span></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            <a href="#" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                    <!-- Tambahkan data dummy lainnya sesuai kebutuhan -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
