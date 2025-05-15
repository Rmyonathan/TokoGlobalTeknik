@extends('layout.Nav')

<style>
    .scroll-table {
        max-height: 300px;
        overflow-y: auto;
    }

    .scroll-table table {
        margin-bottom: 0;
    }

    .scroll-table thead th {
        position: sticky;
        top: 0;
        background-color: #fff;
        z-index: 10;
    }

    #tbodyCustomer tr {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    #tbodyCustomer tr:hover {
    background-color: #ffe8e8;
    }
</style>

@section('content')
<div class="container py-2">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Data Penjualan Per Customer</h2>
    </div>

    <!-- CUSTOMER TABLE -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">List Customer</h5>
        </div>
        <div class="card-body">
            <div class="col-md-4 mb-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari Nama atau Kode Customer">
            </div>

            <div class="table-responsive scroll-table">
                <table class="table table-bordered table-striped p-2">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode Customer</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>HP</th>
                            <th>Telepon</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCustomer">
                        @foreach ($customers as $index => $cust)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $cust->kode_customer }}</td>
                                <td>{{ $cust->nama }}</td>
                                <td>{{ $cust->alamat }}</td>
                                <td>{{ $cust->hp }}</td>
                                <td>{{ $cust->telepon }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TRANSAKSI TABLE -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">List Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <label for="startDate">Dari Tanggal</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="endDate">Sampai Tanggal</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end">
                    <button id="applyDateFilter" class="btn btn-primary mr-2">Terapkan</button>
                    <button id="resetFilterTanggal" class="btn btn-secondary">Reset</button>
                </div>
            </div>

            <div class="table-responsive scroll-table">
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
                    <tbody id="tbodyTransaksi">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->no_transaksi }}</td>
                                <td>{{ $transaction->tanggal }}</td>
                                <td>{{ $transaction->customer->nama ?? 'N/A' }}</td>
                                <td class="text-right">Rp {{ number_format($transaction->grand_total, 0, ' ,', '.') }}</td>
                                <td>
                                    <a href="{{ route('transaksi.nota', $transaction->id) }}" class="btn btn-primary btn-sm">Lihat Nota</a>
                                    @if($transaction->status !== 'cancelled')
                                        <form action="{{ route('transaksi.cancel', $transaction->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Batalkan transaksi ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    @else
                                        <span class="badge badge-danger">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- TOTAL PENJUALAN -->
            <div id="totalTransaksiCustomer" class="mt-3 text-right font-weight-bold text-danger">
                <!-- Total penjualan customer terpilih akan muncul di sini -->
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center">
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function () {
        let selectedCustomerName = '';
        
        // Format rupiah
        function formatRupiah(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // HIGHLIGHT SELECTED ROW
        $('<style>').prop('type', 'text/css').html(`
            #tbodyCustomer tr.selected {
                background-color: #f8d7da !important;
                font-weight: bold;
            }
        `).appendTo('head');

        // Filter customer by keyword
        $('#searchInput').on('input', function () {
            const keyword = $(this).val().toLowerCase();
            $('#tbodyCustomer tr').each(function () {
                const kode = $(this).find('td:nth-child(2)').text().toLowerCase();
                const nama = $(this).find('td:nth-child(3)').text().toLowerCase();
                $(this).toggle(kode.includes(keyword) || nama.includes(keyword));
            });
        });

        // Handle click customer
        $('#tbodyCustomer tr').on('click', function () {
            $('#tbodyCustomer tr').removeClass('selected');
            $(this).addClass('selected');
            selectedCustomerName = $(this).find('td:nth-child(3)').text().trim();
            applyFilters();
        });

        // Auto-select first customer & trigger click
        setTimeout(function () {
            const firstRow = $('#tbodyCustomer tr').first();
            if (firstRow.length > 0) {
                firstRow.trigger('click');
            }
        }, 100); // kasih delay sedikit buat jaga-jaga render DOM

        // Apply date filter
        $('#applyDateFilter').on('click', function () {
            applyFilters();
        });

        // Reset
        $('#resetFilterTanggal').click(function () {
            $('#startDate, #endDate, #searchInput').val('');
            selectedCustomerName = '';
            $('#tbodyCustomer tr, #tbodyTransaksi tr').show();
            $('#totalTransaksiCustomer').html('');
            $('#tbodyCustomer tr').removeClass('selected');
        });

        function applyFilters() {
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            let total = 0;

            $('#tbodyTransaksi tr').each(function () {
                const namaRow = $(this).find('td:nth-child(3)').text().trim();
                const tanggalRow = $(this).find('td:nth-child(2)').text().trim();
                const totalText = $(this).find('td:nth-child(4)').text().replace(/[^\d]/g, '');
                const totalValue = parseInt(totalText) || 0;

                const cocokCustomer = selectedCustomerName ? namaRow === selectedCustomerName : true;
                const cocokTanggal = checkInDateRange(tanggalRow, startDate, endDate);

                if (cocokCustomer && cocokTanggal) {
                    $(this).show();
                    total += totalValue;
                } else {
                    $(this).hide();
                }
            });

            if (selectedCustomerName) {
                $('#totalTransaksiCustomer').html(
                    `Total Penjualan <strong>${selectedCustomerName}</strong>: Rp ${formatRupiah(total)}`
                );
            } else {
                $('#totalTransaksiCustomer').html('');
            }
        }

        function checkInDateRange(dateStr, start, end) {
            if (!start && !end) return true;
            if (!start && end) return dateStr <= end;
            if (start && !end) return dateStr >= start;
            return dateStr >= start && dateStr <= end;
        }
    });
</script>

@endsection