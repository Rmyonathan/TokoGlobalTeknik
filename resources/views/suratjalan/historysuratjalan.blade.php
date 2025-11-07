@extends('layout.Nav')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">History Surat Jalan</h4>
        <a href="{{ route('suratjalan.create-faktur') }}" class="btn btn-success">
            <i class="fas fa-file-invoice mr-1"></i> Buat Faktur dari Multiple SJ
        </a>
    </div>
    <form method="GET" action="{{ route('suratjalan.history') }}" class="row mb-3">
        <div class="col-md-3 mb-2">
            <select name="search_by" id="search_by" class="form-control">
                <option selected disabled value=""> Cari Berdasarkan </option>
                <option value="no_suratjalan" {{ request('search_by') == 'no_suratjalan' ? 'selected' : '' }}>No Surat Jalan</option>
                <option value="customer" {{ request('search_by') == 'customer' ? 'selected' : '' }}>Customer</option>
                <option value="no_transaksi" {{ request('search_by') == 'no_transaksi' ? 'selected' : '' }}>No Faktur</option>
                <option value="alamat_suratjalan" {{ request('search_by') == 'alamat_suratjalan' ? 'selected' : '' }}>Alamat</option>
            </select>
        </div>
        <div class="col-md-3 mb-2">
            <input type="text" name="search" id="search_input" class="form-control" placeholder="Cari..." value="{{ request('search') }}" disabled>
        </div>
        <div class="col-md-2 mb-2">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-2 mb-2">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2 mb-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('suratjalan.history') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th width="50">No</th>
                <th>No Surat Jalan</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Alamat</th>
                <th>Total Item</th>
                <th>Status</th>
                <th>No Faktur</th>
                <th width="250">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suratJalan as $index => $sj)
                @php
                    $itemCount = $sj->items->count();
                    $hasFaktur = !empty($sj->no_transaksi);
                    $statusBadge = $hasFaktur 
                        ? '<span class="badge badge-success"><i class="fas fa-check"></i> Terfaktur</span>' 
                        : '<span class="badge badge-warning"><i class="fas fa-clock"></i> Belum Faktur</span>';
                @endphp
                <tr>
                    <td class="text-center">{{ $suratJalan->firstItem() + $index }}</td>
                    <td><strong>{{ $sj->no_suratjalan }}</strong></td>
                    <td>{{ date('d/m/Y', strtotime($sj->tanggal)) }}</td>
                    <td>{{ $sj->customer->nama }}</td>
                    <td>{{ $sj->alamat_suratjalan }}</td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $itemCount }} item</span>
                    </td>
                    <td>{!! $statusBadge !!}</td>
                    <td>
                        @if($hasFaktur)
                            <strong>{{ $sj->no_transaksi }}</strong>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('suratjalan.detail', $sj->id) }}" 
                               class="btn btn-info" 
                               title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('suratjalan.detail', $sj->id) }}?auto_print=1" 
                               class="btn btn-primary" 
                               target="_blank"
                               title="Print Surat Jalan">
                                <i class="fas fa-print"></i> SJ
                            </a>
                            @if($hasFaktur)
                                <a href="{{ route('transaksi.notasementara', $sj->no_transaksi) }}" 
                                   class="btn btn-warning" 
                                   target="_blank"
                                   title="Print Nota Sementara">
                                    <i class="fas fa-receipt"></i> Nota
                                </a>
                            @else
                                <a href="{{ route('suratjalan.print-nota-preview', $sj->id) }}" 
                                   class="btn btn-warning" 
                                   target="_blank"
                                   title="Preview Nota Sementara">
                                    <i class="fas fa-receipt"></i> Nota
                                </a>
                            @endif
                        </div>
                        @if(!$hasFaktur)
                            <a href="{{ route('transaksi.penjualan', ['no_suratjalan' => $sj->no_suratjalan]) }}" 
                               class="btn btn-success btn-sm ml-1"
                               title="Buat Faktur dari Surat Jalan ini">
                                <i class="fas fa-file-invoice"></i> Buat Faktur
                            </a>
                        @else
                            <span class="badge badge-secondary ml-1" title="Sudah terfaktur">
                                <i class="fas fa-check"></i> Done
                            </span>
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
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen select dan input
    const searchBySelect = document.getElementById('search_by');
    const searchInput = document.getElementById('search_input');

    // Fungsi untuk mengecek status dropdown dan mengatur disabled state pada input
    function updateSearchInputState() {
        // Cek apakah ada opsi yang dipilih dan bukan opsi default (disabled)
        if (searchBySelect.value !== "" && searchBySelect.selectedIndex !== 0) {
            searchInput.disabled = false;
        } else {
            searchInput.disabled = true;
            searchInput.value = ''; // Kosongkan input jika disabled
        }
    }

    // Panggil fungsi saat halaman dimuat untuk mengatur status awal
    updateSearchInputState();

    // Tambahkan event listener untuk dropdown
    searchBySelect.addEventListener('change', updateSearchInputState);
});
</script>