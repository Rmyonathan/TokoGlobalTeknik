@extends('layout.Nav')

@section('content')
<section id="barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Master Barang</h2>
        <div>
            <a href="{{ route('code.import.form') }}" class="btn btn-outline-primary btn-sm me-2">
                <i class="fas fa-file-upload"></i> Import CSV
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('code.create-code') }}" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-plus mr-1"></i> Tambah Barang
                </a>
            </div>

            <div>
                <a href="{{ route('code.view-code') }}" class="btn btn-sm" style="background-color: #3f8efc; color: white;">
                    <i class="fas fa-file-alt"></i> List Kode Barang
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Enhanced search panel similar to History Surat Jalan -->
            <form method="GET" action="{{ route('master.barang') }}" class="row mb-3">
                <div class="col-md-3 mb-2">
                    <select name="search_by" id="search_by" class="form-control">
                        <option selected disabled value=""> Cari Berdasarkan </option>
                        <option value="group_id" {{ request('search_by') == 'group_id' ? 'selected' : '' }}>Kode Barang</option>
                        <option value="name" {{ request('search_by') == 'name' ? 'selected' : '' }}>Nama Barang</option>
                        <option value="group" {{ request('search_by') == 'group' ? 'selected' : '' }}>Group</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" id="search_input" class="form-control" placeholder="Cari..." value="{{ request('search') }}" disabled>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="status_filter" id="status_filter" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="Active" {{ request('status_filter') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status_filter') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('master.barang') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <div class="card-body">
                @if(isset($inventory) && count($inventory['inventory_by_length']) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kode Barang</th>
                                        <th>Nama</th>
                                        <th>Group</th>
                                        <th>Harga Beli</th>
                                        <th>Harga Jual per Satuan Dasar</th>
                                        <th>Merek</th>
                                        <th>Ukuran</th>
                                        <th>Stok DB 1</th>
                                        <th>Stok DB 2</th>
                                        <th>Total Stok</th>
                                        <th>Satuan Dasar</th>
                                        <th>Satuan Besar</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                            <tbody>
                                @foreach($inventory['inventory_by_length'] as $item)
                                    <tr>
                                        <td>{{ $item['id'] }}</td>
                                        <td>{{ $item['group_id'] }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td>{{ $item['group'] }}</td>
                                        <td>Rp. {{ number_format($item['cost']) }}</td>
                                        <td>
                                            <strong>Rp. {{ number_format($item['harga_per_satuan_dasar'] ?? $item['price']) }}</strong>
                                            <br>
                                            <small class="text-muted">per {{ $item['unit_dasar'] ?? 'PCS' }}</small>
                                        </td>
                                        <td>{{ $item['merek'] }}</td>
                                        <td>{{ $item['ukuran'] }}</td>
                                        <td>
                                            <span class="badge {{ ($item['stock_db1'] ?? 0) > 0 ? 'bg-white' : 'bg-white' }}">
                                                {{ number_format($item['stock_db1'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ ($item['stock_db2'] ?? 0) > 0 ? 'bg-white' : 'bg-white' }}">
                                                {{ number_format($item['stock_db2'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ ($item['quantity'] ?? 0) > 0 ? 'bg-white' : 'bg-white' }}">
                                                {{ number_format($item['quantity'] ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-white">{{ $item['unit_dasar'] ?? 'PCS' }}</span>
                                        </td>
                                        <td>
                                            @if(isset($item['satuan_besar']) && count($item['satuan_besar']) > 0)
                                                @foreach($item['satuan_besar'] as $satuan)
                                                    <span class="badge bg-white me-1">
                                                        {{ $satuan['unit'] }} ({{ $satuan['konversi'] }})
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $item['status'] === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $item['status'] }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <form action="{{ route('panels.edit-inventory', ['id' => $item['group_id']]) }}" method="GET">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success"><i class="fas fa-edit"></i> Edit</button>
                                                </form>
                                                <form action="{{ route('panels.delete-inventory', ['id' => $item['group_id']]) }}" method="POST">
                                                    @csrf
                                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                                                </form>
                                                <form action="{{ route('code.toggle-status', ['id' => $item['id'] ?? null]) }}" method="POST" onsubmit="return confirm('Ubah status barang ini?');">
                                                    @csrf
                                                    <button class="btn btn-sm {{ ($item['status'] === 'Active') ? 'btn-warning' : 'btn-primary' }}">
                                                        {{ ($item['status'] === 'Active') ? 'Nonaktifkan' : 'Aktifkan' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                            <tr class="table-primary">
                                <th colspan="4" class="text-end"><strong>Total</strong></th>
                                <th>
                                    @php
                                        $totalCost = 0;
                                        foreach($inventory['inventory_by_length'] as $item) {
                                            $totalCost += $item['cost'] * $item['quantity'];
                                        }
                                        echo 'Rp. ' . number_format($totalCost);
                                    @endphp
                                </th>
                                <th>
                                    @php
                                        $totalPrice = 0;
                                        foreach($inventory['inventory_by_length'] as $item) {
                                            $totalPrice += ($item['harga_per_satuan_dasar'] ?? $item['price']) * $item['quantity'];
                                        }
                                        echo 'Rp. ' . number_format($totalPrice);
                                    @endphp
                                </th>
                                <th>-</th> <!-- Merek -->
                                <th>-</th> <!-- Ukuran -->
                                <th>
                                    @php
                                        $totalStockDb1 = 0;
                                        foreach($inventory['inventory_by_length'] as $item) {
                                            $totalStockDb1 += $item['stock_db1'] ?? 0;
                                        }
                                        echo number_format($totalStockDb1, 0, ',', '.');
                                    @endphp
                                </th>
                                <th>
                                    @php
                                        $totalStockDb2 = 0;
                                        foreach($inventory['inventory_by_length'] as $item) {
                                            $totalStockDb2 += $item['stock_db2'] ?? 0;
                                        }
                                        echo number_format($totalStockDb2, 0, ',', '.');
                                    @endphp
                                </th>
                                <th>
                                    @php
                                        $totalQuantity = 0;
                                        foreach($inventory['inventory_by_length'] as $item) {
                                            $totalQuantity += $item['quantity'];
                                        }
                                        echo number_format($totalQuantity, 0, ',', '.');
                                    @endphp
                                </th>
                                <th>-</th> <!-- Satuan Dasar -->
                                <th>-</th> <!-- Satuan Besar -->
                                <th>-</th> <!-- Status -->
                                <th>-</th> <!-- Aksi -->
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $inventory['paginator']->appends(request()->except('page'))->links() }}
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i> No panels currently in inventory.
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000 !important;
    }

    .table-bordered {
        border: 2px solid #000;
    }
</style>
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
@endsection