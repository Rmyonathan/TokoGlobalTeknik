@extends('layout.Nav')

@section('content')
<div class="container">
    <h3>Daftar Transaksi</h3>

    {{-- Filter Section --}}
    <div class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" id="searchKeyword" class="form-control" placeholder="Cari No Transaksi">
            </div>
            <div class="col-md-4">
                <input type="date" id="startDate" class="form-control">
            </div>
            <div class="col-md-4">
                <input type="date" id="endDate" class="form-control">
            </div>
        </div>
        <button class="btn btn-primary mt-3" id="applyFilter">Terapkan Filter</button>
    </div>

    {{-- Transaksi Table --}}
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
        <tbody id="transactionTableBody">
            {{-- Data Dummy --}}
            <tr>
                <td>TRX001</td>
                <td>2025-04-25</td>
                <td>John Doe</td>
                <td>Rp 1,000,000</td>
                <td>
                    <a href="#" class="btn btn-primary btn-sm">Lihat Nota</a>
                </td>
            </tr>
            <tr>
                <td>TRX002</td>
                <td>2025-04-20</td>
                <td>Jane Smith</td>
                <td>Rp 500,000</td>
                <td>
                    <a href="#" class="btn btn-primary btn-sm">Lihat Nota</a>
                </td>
            </tr>
            <tr>
                <td>TRX003</td>
                <td>2025-04-15</td>
                <td>Michael Brown</td>
                <td>Rp 2,000,000</td>
                <td>
                    <a href="#" class="btn btn-primary btn-sm">Lihat Nota</a>
                </td>
            </tr>
            <tr>
                <td>TRX004</td>
                <td>2025-04-10</td>
                <td>Sarah Lee</td>
                <td>Rp 750,000</td>
                <td>
                    <a href="#" class="btn btn-primary btn-sm">Lihat Nota</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('applyFilter').addEventListener('click', function() {
        // Ambil nilai dari input
        let keyword = document.getElementById('searchKeyword').value.toLowerCase();
        let startDate = document.getElementById('startDate').value;
        let endDate = document.getElementById('endDate').value;

        // Ambil semua row transaksi
        let rows = document.querySelectorAll('#transactionTableBody tr');

        rows.forEach(function(row) {
            let noTransaksi = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            let transactionDate = row.querySelector('td:nth-child(2)').textContent;
            let isMatch = true;

            // Filter berdasarkan keyword
            if (keyword && !noTransaksi.includes(keyword)) {
                isMatch = false;
            }

            // Filter berdasarkan tanggal
            if (startDate || endDate) {
                let transactionDateObj = new Date(transactionDate);
                let isDateInRange = true;

                if (startDate) {
                    let start = new Date(startDate);
                    isDateInRange = transactionDateObj >= start;
                }

                if (endDate) {
                    let end = new Date(endDate);
                    isDateInRange = isDateInRange && transactionDateObj <= end;
                }

                if (!isDateInRange) {
                    isMatch = false;
                }
            }

            // Menampilkan atau menyembunyikan row berdasarkan filter
            row.style.display = isMatch ? '' : 'none';
        });
    });
</script>
@endsection
