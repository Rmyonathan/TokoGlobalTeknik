@extends('layout.Nav')

@section('content')
<section id="barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Barang</h2>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('panels.create-inventory') }}" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-plus mr-1"></i> Tambah Barang
                </a>

                <a href="{{ route('code.create-code') }}" class="btn btn-sm me-2" style="background-color: #28a745; color: white;">
                    <i class="fas fa-upload"></i> Tambah Kode Barang
                </a>
            </div>

            <a href="{{ route('code.view-code') }}" class="btn btn-sm" style="background-color: #3f8efc; color: white;">
                <i class="fas fa-file-alt"></i> List Kode Barang
            </a>
        </div>

        <div class="card-body">
            @if(isset($inventory) && count($inventory['inventory_by_length']) > 0)

                {{-- Search Bar --}}
                <div class="mb-3 d-flex">
                    <div class="me-2" style="flex: 1;">
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari Nama atau Kode Barang" />
                    </div>
                
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode Barang</th>
                                <th>Name</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Length (meters)</th>
                                <th>Available Quantity</th>
                                <th>Total Length (meters)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyBarang">
                            @foreach($inventory['inventory_by_length'] as $item)
                                <tr>
                                    <td>{{ $item['id'] }}</td>
                                    <td>{{ $item['group_id'] }}</td>
                                    <td>{{ $item['name'] }}</td>
                                    <td>Rp. {{ number_format($item['cost'], 2) }}</td>
                                    <td>Rp. {{ number_format($item['price'], 2) }}</td>
                                    <td>{{ number_format($item['length'], 2) }}</td>
                                    <td>{{ $item['quantity'] }}</td>
                                    <td>{{ number_format($item['length'] * $item['quantity'], 2) }}</td>
                                    <td style="border: 1px solid #000;">
                                        <div class="btn-group" role="group">
                                            <form action="{{ route('panels.edit-inventory', ['id' => $item['group_id']]) }}" method="GET">
                                                @csrf
                                                <button class="btn btn-sm btn-success">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </form>
                                            <form action="{{ route('panels.delete-inventory', ['id' => $item['group_id']]) }}" method="POST">
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
                                <th></th><th></th><th></th><th></th><th></th><th></th>
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
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> No panels currently in inventory.
                </div>
                <a href="{{ route('panels.create-inventory') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add Panels to Inventory
                </a>
            @endif
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
    $(document).ready(function () {
        // Live search on input without button
        $('#searchInput').on('input', function () {
            const keyword = $(this).val().toLowerCase();
            $('#tbodyBarang tr').each(function () {
                const kode = $(this).find('td:nth-child(2)').text().toLowerCase();
                const nama = $(this).find('td:nth-child(3)').text().toLowerCase();
                $(this).toggle(kode.includes(keyword) || nama.includes(keyword));
            });
        });

        // Reset search functionality
        $('#resetButton').on('click', function () {
            $('#searchInput').val(''); // Clear the search input
            $('#tbodyBarang tr').show(); // Show all rows
        });
});
</script>
@endsection