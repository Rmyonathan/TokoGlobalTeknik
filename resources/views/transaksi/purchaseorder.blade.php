@extends('layout.Nav')

@section('content')
<div class="container mt-4">
    <div class="title-box mb-4">
        <h2><i class="fas fa-shopping-cart mr-2"></i> Purchase Order</h2>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Filter & Pencarian</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transaksi.purchaseorder') }}" class="row">
                <!-- Search By Dropdown -->
                <div class="col-md-3 mb-3">
                    <label for="search_by">Cari Berdasarkan</label>
                    <select name="search_by" id="search_by" class="form-control">
                        <option value="">Semua</option>
                        <option value="no_po" {{ request('search_by') == 'no_po' ? 'selected' : '' }}>No. PO</option>
                        <option value="kode_customer" {{ request('search_by') == 'kode_customer' ? 'selected' : '' }}>Customer</option>
                        <option value="sales" {{ request('search_by') == 'sales' ? 'selected' : '' }}>Sales</option>
                    </select>
                </div>

                <!-- Keywords -->
                <div class="col-md-3 mb-3">
                    <label for="search">Kata Kunci</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Ketik untuk mencari...">
                </div>

                <!-- Date Range -->
                <div class="col-md-2 mb-3">
                    <label for="date_from">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="date_to">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-2 mb-3">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Cari
                    </button>
                    <a href="{{ route('transaksi.purchaseorder') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-redo mr-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Purchase Order</h5>
            <span class="badge badge-light">Total: {{ $purchaseOrders->total() }}</span>
        </div>
        
        <div class="card-body">
            @if($purchaseOrders->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> Tidak ada Purchase Order yang sesuai dengan kriteria pencarian.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>No. PO</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Sales</th>
                                <th>Total Item</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrders as $po)
                                <tr>
                                    <td>{{ $po->no_po }}</td>
                                    <td>{{ \Carbon\Carbon::parse($po->tanggal)->format('d-m-Y') }}</td>
                                    <td>
                                        <span class="d-block">{{ $po->customer->nama ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $po->kode_customer }}</small>
                                    </td>
                                    <td>{{ $po->sales }}</td>
                                    <td>{{ $po->items->sum('qty') }}</td>
                                    <td>Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($po->status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($po->status === 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif(str_contains($po->status, 'cancelled'))
                                            <span class="badge badge-danger">Cancelled</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($po->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('purchase-order.show', $po->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($po->status === 'pending')
                                                <a href="#" 
                                                   class="btn btn-sm btn-success complete-po" 
                                                   data-id="{{ $po->id }}"
                                                   title="Selesaikan PO">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="#" 
                                                   class="btn btn-sm btn-danger cancel-po" 
                                                   data-id="{{ $po->id }}"
                                                   title="Batalkan PO">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Menampilkan {{ $purchaseOrders->firstItem() ?? 0 }} - {{ $purchaseOrders->lastItem() ?? 0 }} 
                        dari {{ $purchaseOrders->total() }} data
                    </div>
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Confirmation Modals -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Konfirmasi Selesaikan PO</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menyelesaikan PO ini?
            </div>
            <div class="modal-footer">
                <form id="completePOForm" action="" method="POST">
                    @csrf
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Ya, Selesaikan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Pembatalan</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin membatalkan PO ini?
            </div>
            <div class="modal-footer">
                <form id="cancelPOForm" action="" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                    <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Complete PO
    $('.complete-po').click(function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#completePOForm').attr('action', `/purchase-order/${id}/complete`);
        $('#completeModal').modal('show');
    });

    // Cancel PO
    $('.cancel-po').click(function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#cancelPOForm').attr('action', `/purchase-order/${id}/cancel`);
        $('#cancelModal').modal('show');
    });

    // Date range validation
    $('#date_to').change(function() {
        const dateFrom = $('#date_from').val();
        const dateTo = $(this).val();
        
        if(dateFrom && dateTo && dateFrom > dateTo) {
            alert('Tanggal akhir tidak boleh lebih awal dari tanggal awal');
            $(this).val('');
        }
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endsection