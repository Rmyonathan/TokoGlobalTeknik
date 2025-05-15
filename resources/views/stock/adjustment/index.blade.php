@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <span>Stock Inventory</span>
                <a href="{{ route('stock.adjustment.history') }}" class="btn btn-info">
                    <i class="fas fa-history"></i> View Adjustment History
                </a>
            </div>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-3">
                <form action="{{ route('stock.adjustment.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search..." value="{{ $search ?? '' }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>No.</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Current Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($inventory) && count($inventory['inventory_by_length']) > 0)
                            @foreach($inventory['inventory_by_length'] as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item['group_id'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>
                                    <a href="{{ route('stock.adjustment.adjust', $item['group_id']) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Adjust Stock
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">No items found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(isset($inventory['paginator']))
                <div class="d-flex justify-content-center mt-3">
                    {{ $inventory['paginator']->appends(['search' => $search ?? ''])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection