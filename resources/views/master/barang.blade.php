@extends('layout.Nav')

@section('content')
<section id="barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Master Barang</h2>
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
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kode Barang</th>
                                    <th>Name</th>
                                    <th>Group</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Length (meters)</th>
                                    <th>Available Quantity</th>
                                    <th>Total Length (meters)</th>
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
                                        <td>Rp. {{ number_format($item['cost'], 2) }}</td>
                                        <td>Rp. {{ number_format($item['price'], 2) }}</td>
                                        <td>{{ number_format($item['length'], 2) }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>{{ number_format($item['length'] * $item['quantity'], 2) }}</td>
                                        <td>{{ $item['status'] }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <form action="{{ route('panels.edit-inventory', ['id' => $item['group_id']]) }}" method="GET" enctype="multipart/form-data">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </form>
                                                <form action="{{ route('panels.delete-inventory', ['id' => $item['group_id']]) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>Total</th>
                                    <th>{{ $inventory['total_panels'] }}</th>
                                    <th>
                                        @php
                                            $totalLength = 0;
                                            foreach($inventory['inventory_by_length'] as $item) {
                                                $totalLength += $item['length'] * $item['quantity'];
                                            }
                                            echo number_format($totalLength, 2);
                                        @endphp
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Pagination Links -->
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