@extends('layout.Nav')

@section('content')
<div class="container py-2">
    <h3>Daftar Transaksi</h3>

    <!-- Search and Date Filters -->
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari No Transaksi atau Nama Customer">
        </div>
        <div class="col-md-3 mb-2">
            <label for="startDate">Dari Tanggal</label>
            <input type="date" id="startDate" class="form-control">
        </div>
        <div class="col-md-3 mb-2">
            <label for="endDate">Sampai Tanggal</label>
            <input type="date" id="endDate" class="form-control">
        </div>
        <div class="col-md-2 mb-2 d-flex align-items-end">
            <button id="applyFilter" class="btn btn-primary mr-2">Terapkan</button>
            <button id="resetFilter" class="btn btn-secondary">Reset</button>
        </div>
    </div>

    <table class="table table-bordered" id="transactionTable">
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

@section('scripts')
<script>
    $(document).ready(function () {
        // Format rupiah helper (optional, if you want to reformat totals)
        function formatRupiah(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Check if date is in range
        function checkInDateRange(dateStr, start, end) {
            if (!start && !end) return true;
            if (!start && end) return dateStr <= end;
            if (start && !end) return dateStr >= start;
            return dateStr >= start && dateStr <= end;
        }

        // Apply filters function
        function applyFilters() {
            const keyword = $('#searchInput').val().toLowerCase();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            $('#transactionTable tbody tr').each(function () {
                const noTransaksi = $(this).find('td:nth-child(1)').text().toLowerCase();
                const tanggal = $(this).find('td:nth-child(2)').text();
                const customer = $(this).find('td:nth-child(3)').text().toLowerCase();

                // Filter by keyword in noTransaksi or customer name
                const matchKeyword = noTransaksi.includes(keyword) || customer.includes(keyword);

                // Filter by date range
                const matchDate = checkInDateRange(tanggal, startDate, endDate);

                if (matchKeyword && matchDate) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Event listeners
        $('#applyFilter').on('click', applyFilters);

        $('#resetFilter').on('click', function () {
            $('#searchInput, #startDate, #endDate').val('');
            $('#transactionTable tbody tr').show();
        });

        // Optional: live search as you type
        $('#searchInput').on('input', applyFilters);
    });
</script>
@endsection
