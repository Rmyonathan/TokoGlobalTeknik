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
                <a href="{{ route('kategori.index') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-tags mr-1"></i> Kelola Kategori
                </a>
            </div>

            <div class="d-flex">
                <!-- Server-side search form -->
                <form action="{{ route('master.barang') }}" method="GET" class="me-2 d-flex">
                    <div class="input-group me-2">
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="input-group">
                        <input type="text" name="search" id="searchInput" class="form-control form-control-sm" 
                            placeholder="Cari nama/kode barang..." value="{{ $search ?? '' }}">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(!empty($search) || !empty($categoryId))
                            <a href="{{ route('master.barang') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
                
                <a href="{{ route('code.view-code') }}" class="btn btn-sm" style="background-color: #3f8efc; color: white;">
                    <i class="fas fa-file-alt"></i> List Kode Barang
                </a>
            </div>
        </div>

        <div class="card-body">
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
                        {{ $inventory['paginator']->appends(['search' => $search ?? ''])->links() }}
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
<!-- We're removing the client-side search JavaScript since we're using server-side search now -->
@endsection