@extends('layout.Nav')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="title-box">
        <h2><i class="fas fa-exchange-alt mr-2"></i>Transfer Stok Antar Database</h2>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Data</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('stock-transfer.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">-- Semua Status --</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_awal">Tanggal Awal</label>
                            <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" value="{{ request('tanggal_awal') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_akhir">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('stock-transfer.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-md-6">
            @can('create stock transfer')
                <a href="{{ route('stock-transfer.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Buat Transfer Stok
                </a>
            @endcan
        </div>
        <div class="col-md-6 text-right">
            <button type="button" class="btn btn-info" onclick="refreshGlobalStock()">
                <i class="fas fa-sync"></i> Refresh Global Stock
            </button>
        </div>
    </div>

    <!-- Stock Transfer List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Transfer Stok</h5>
        </div>
        <div class="card-body">
            @if($transfers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No Transfer</th>
                                <th>Tanggal</th>
                                <th>Dari Database</th>
                                <th>Ke Database</th>
                                <th>Jumlah Item</th>
                                <th>Total Value</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $transfer)
                                <tr>
                                    <td>
                                        <strong>{{ $transfer->no_transfer }}</strong>
                                    </td>
                                    <td>{{ $transfer->tanggal_transfer->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $transfer->from_database == 'primary' ? 'Database Utama' : 'Database Kedua' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ $transfer->to_database == 'primary' ? 'Database Utama' : 'Database Kedua' }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $transfer->items->count() }}</td>
                                    <td class="text-right">
                                        Rp {{ number_format($transfer->items->sum('total_value'), 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @switch($transfer->status)
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('approved')
                                                <span class="badge badge-info">Approved</span>
                                                @break
                                            @case('completed')
                                                <span class="badge badge-success">Completed</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-danger">Cancelled</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $transfer->creator->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('stock-transfer.show', $transfer) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($transfer->status == 'pending')
                                                @can('approve stock transfer')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success" 
                                                            onclick="approveTransfer({{ $transfer->id }})"
                                                            title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endcan
                                                
                                                @can('cancel stock transfer')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="cancelTransfer({{ $transfer->id }})"
                                                            title="Cancel">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $transfers->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data transfer stok</h5>
                    <p class="text-muted">Klik tombol "Buat Transfer Stok" untuk membuat transfer baru</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Transfer Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Transfer Stok</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menyetujui transfer stok ini?</p>
                <p class="text-muted">Setelah disetujui, stok akan ditransfer antar database dan jurnal accounting akan dibuat.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="approveForm" method="POST" style="display: inline;">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-success">Ya, Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Transfer Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Transfer Stok</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cancelForm" method="POST">
                    @csrf
                    @method('POST')
                    <div class="form-group">
                        <label for="keterangan_cancel">Alasan Pembatalan</label>
                        <textarea class="form-control" 
                                  id="keterangan_cancel" 
                                  name="keterangan_cancel" 
                                  rows="3" 
                                  placeholder="Masukkan alasan pembatalan..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" form="cancelForm" class="btn btn-danger">Ya, Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function approveTransfer(transferId) {
    $('#approveForm').attr('action', '{{ route("stock-transfer.approve", ":id") }}'.replace(':id', transferId));
    $('#approveModal').modal('show');
}

function cancelTransfer(transferId) {
    $('#cancelForm').attr('action', '{{ route("stock-transfer.cancel", ":id") }}'.replace(':id', transferId));
    $('#cancelModal').modal('show');
}

function refreshGlobalStock() {
    // This would typically make an AJAX call to refresh global stock data
    location.reload();
}
</script>
@endsection
