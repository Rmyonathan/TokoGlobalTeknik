@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-undo mr-2"></i>Return Barang
                    </h3>
                    @can('create return barang')
                    <a href="{{ route('return-barang.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>Tambah Return
                    </a>
                    @endcan
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('return-barang.index') }}" class="form-inline">
                                <div class="form-group mr-3">
                                    <label for="status" class="mr-2">Status:</label>
                                    <select name="status" id="status" class="form-control form-control-sm">
                                        <option value="">Semua Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="tipe_return" class="mr-2">Cara Bayar:</label>
                                    <select name="tipe_return" id="tipe_return" class="form-control form-control-sm">
                                        <option value="">Semua Tipe</option>
                                        <option value="penjualan" {{ request('tipe_return') == 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                                        <option value="pembelian" {{ request('tipe_return') == 'pembelian' ? 'selected' : '' }}>Pembelian</option>
                                    </select>
                                </div>
                                <div class="form-group mr-3">
                                    <label for="customer" class="mr-2">Customer:</label>
                                    <input type="text" name="customer" id="customer" class="form-control form-control-sm" 
                                           value="{{ request('customer') }}" placeholder="Kode/Nama Customer">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="start_date" class="mr-2">Dari:</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" 
                                           value="{{ request('start_date') }}">
                                </div>
                                <div class="form-group mr-3">
                                    <label for="end_date" class="mr-2">Sampai:</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" 
                                           value="{{ request('end_date') }}">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary mr-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('return-barang.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Return Barang Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No Return</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Transaksi Asal</th>
                                    <th>Tipe</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returns as $return)
                                <tr>
                                    <td>
                                        <strong>{{ $return->no_return }}</strong>
                                    </td>
                                    <td>{{ $return->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $return->customer->kode_customer ?? 'N/A' }}</strong>
                                        </div>
                                        <small class="text-muted">{{ $return->customer->nama ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('transaksi.show', $return->no_transaksi_asal) }}" 
                                           class="text-primary" target="_blank">
                                            {{ $return->no_transaksi_asal }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $return->tipe_return == 'penjualan' ? 'success' : 'info' }}">
                                            {{ ucfirst($return->tipe_return) }}
                                        </span>
                                    </td>
                                    <td>
                                        @switch($return->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="badge badge-success">Approved</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                                @break
                                            @case('processed')
                                                <span class="badge badge-primary">Processed</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <strong>Rp {{ number_format($return->total_return, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('return-barang.show', $return) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @can('edit return barang')
                                            @if($return->canBeApproved())
                                            <a href="{{ route('return-barang.edit', $return) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            @endcan

                                            @can('manage return barang')
                                            @if($return->isPending())
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="approveReturn({{ $return->id }})" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="rejectReturn({{ $return->id }})" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            @endif
                                            
                                            @if($return->isApproved())
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="processReturn({{ $return->id }})" title="Process">
                                                <i class="fas fa-cogs"></i>
                                            </button>
                                            @endif
                                            @endcan

                                            @can('delete return barang')
                                            @if($return->canBeApproved())
                                            <form method="POST" action="{{ route('return-barang.destroy', $return) }}" 
                                                  style="display: inline;" onsubmit="return confirm('Yakin hapus return barang ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada data return barang</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <p class="text-muted mb-0">
                                Menampilkan {{ $returns->firstItem() ?? 0 }} sampai {{ $returns->lastItem() ?? 0 }} 
                                dari {{ $returns->total() }} data
                            </p>
                        </div>
                        <div>
                            {{ $returns->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Return Barang</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatan_approval">Catatan Approval (Opsional)</label>
                        <textarea name="catatan_approval" id="catatan_approval" class="form-control" 
                                  rows="3" placeholder="Catatan approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Return Barang</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatan_reject">Alasan Reject <span class="text-danger">*</span></label>
                        <textarea name="catatan_approval" id="catatan_reject" class="form-control" 
                                  rows="3" placeholder="Alasan reject..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Process Modal -->
<div class="modal fade" id="processModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Return Barang</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin memproses return barang ini?</p>
                <p class="text-muted">Proses ini akan mengupdate stok barang sesuai dengan tipe return.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="processForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary">Process</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approveReturn(id) {
    $('#approveForm').attr('action', `/return-barang/${id}/approve`);
    $('#approveModal').modal('show');
}

function rejectReturn(id) {
    $('#rejectForm').attr('action', `/return-barang/${id}/reject`);
    $('#rejectModal').modal('show');
}

function processReturn(id) {
    $('#processForm').attr('action', `/return-barang/${id}/process`);
    $('#processModal').modal('show');
}

// Auto-submit filter form on select change
$('#status, #tipe_return').on('change', function() {
    $(this).closest('form').submit();
});
</script>
@endpush
