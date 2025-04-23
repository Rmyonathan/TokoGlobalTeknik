@extends('layout.Nav')

@section('content')
<div class="container py-4">
    <h4>:: History Surat Jalan ::</h4>
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari No Surat Jalan atau Customer">
        </div>
        <div class="col-md-4">
            <input type="date" id="filterDate" class="form-control" placeholder="Filter Tanggal">
        </div>
        <div class="col-md-4">
            <button id="resetFilter" class="btn btn-secondary">Reset Filter</button>
        </div>
    </div>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>No</th>
                <th>No Surat Jalan</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Alamat</th>
                <th>No Faktur</th>
                <th>Status Barang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suratJalan as $index => $sj)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sj->no_suratjalan }}</td>
                    <td>{{ $sj->tanggal }}</td>
                    <td>{{ $sj->customer->nama }}</td>
                    <td>{{ $sj->alamat }}</td>
                    <td>{{ $sj->no_transaksi }}</td>
                    <td>
                        @if ($sj->status_barang === 'Selesai')
                            <span class="badge bg-success">Selesai</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Selesai</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('suratjalan.detail', $sj->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <a href="{{ route('suratjalan.detail', $sj->id) }}" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-print"></i> Print
                        </a>
                        @if ($sj->items->sum('qty_dibawa') < $sj->transaksi->items->sum('qty'))
                            <a href="{{ route('suratjalan.create') }}?no_transaksi={{ $sj->no_transaksi }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-truck"></i> Bawa Barang
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $suratJalan->links() }}
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        // Filter berdasarkan input pencarian
        $('#searchInput').on('input', function() {
            const searchValue = $(this).val().toLowerCase();
            $('tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1);
            });
        });

        // Filter berdasarkan tanggal
        $('#filterDate').on('change', function() {
            const filterDate = $(this).val();
            $('tbody tr').filter(function() {
                const rowDate = $(this).find('td:nth-child(3)').text(); // Kolom tanggal
                $(this).toggle(rowDate === filterDate);
            });
        });

        // Reset filter
        $('#resetFilter').click(function() {
            $('#searchInput').val('');
            $('#filterDate').val('');
            $('tbody tr').show();
        });
    });
</script>
@endsection