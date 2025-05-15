@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Stock Adjustment History</span>
                        <a href="{{ route('stock.adjustment.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Inventory
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

                    <div class="mb-3 d-flex justify-content-between">
                        <form action="{{ route('stock.adjustment.history') }}" method="GET" class="form-inline">
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
                                    <th>Tanggal</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Before</th>
                                    <th>After</th>
                                    <th>Diff</th>
                                    <th>Keterangan</th>
                                    <th>User</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adjustments as $key => $adjustment)
                                <tr>
                                    <td>{{ ($adjustments->currentPage() - 1) * $adjustments->perPage() + $key + 1 }}</td>
                                    <td>{{ $adjustment->created_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ $adjustment->kode_barang }}</td>
                                    <td>{{ $adjustment->stock->nama_barang }}</td>
                                    <td>{{ $adjustment->quantity_before }}</td>
                                    <td>{{ $adjustment->quantity_after }}</td>
                                    <td class="{{ $adjustment->difference > 0 ? 'text-success' : ($adjustment->difference < 0 ? 'text-danger' : '') }}">
                                        {{ $adjustment->difference > 0 ? '+' : '' }}{{ $adjustment->difference }}
                                    </td>
                                    <td>{{ $adjustment->keterangan }}</td>
                                    <td>{{ $adjustment->user->name }}</td>
                                    <td>
                                        <a href="{{ route('stock.adjustment.show', $adjustment->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No adjustment history available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $adjustments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection