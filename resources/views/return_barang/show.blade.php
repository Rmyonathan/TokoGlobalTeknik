@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>Detail Return Barang
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('return-barang.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                        @can('edit return barang')
                        @if($returnBarang->canBeApproved())
                        <a href="{{ route('return-barang.edit', $returnBarang) }}" class="btn btn-warning">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        @endif
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Header Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Return</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>No Return:</strong></td>
                                            <td>{{ $returnBarang->no_return }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal:</strong></td>
                                            <td>{{ $returnBarang->tanggal->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipe Return:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $returnBarang->tipe_return == 'penjualan' ? 'success' : 'info' }}">
                                                    {{ ucfirst($returnBarang->tipe_return) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @switch($returnBarang->status)
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
                                        </tr>
                                        <tr>
                                            <td><strong>Total Return:</strong></td>
                                            <td><strong class="text-primary">Rp {{ number_format($returnBarang->total_return, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Customer & Transaksi</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Customer:</strong></td>
                                            <td>
                                                <div>
                                                    <strong>{{ $returnBarang->customer->kode_customer ?? 'N/A' }}</strong>
                                                </div>
                                                <small class="text-muted">{{ $returnBarang->customer->nama ?? 'N/A' }}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Transaksi Asal:</strong></td>
                                            <td>
                                                <a href="{{ route('transaksi.show', $returnBarang->no_transaksi_asal) }}" 
                                                   class="text-primary" target="_blank">
                                                    {{ $returnBarang->no_transaksi_asal }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dibuat Oleh:</strong></td>
                                            <td>{{ $returnBarang->created_by ?? 'System' }}</td>
                                        </tr>
                                        @if($returnBarang->approved_by)
                                        <tr>
                                            <td><strong>Approved Oleh:</strong></td>
                                            <td>{{ $returnBarang->approved_by }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Approval:</strong></td>
                                            <td>{{ $returnBarang->approved_at ? $returnBarang->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Alasan Return</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $returnBarang->alasan_return }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Notes -->
                    @if($returnBarang->catatan_approval)
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Catatan Approval</h6>
                                </div>
                                <div class="card-body">
                                    <p>{{ $returnBarang->catatan_approval }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Items -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Rincian Barang Return</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Qty Return</th>
                                                    <th>Satuan</th>
                                                    <th>Harga</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($returnBarang->items as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $item->kode_barang }}</strong>
                                                    </td>
                                                    <td>{{ $item->nama_barang }}</td>
                                                    <td class="text-right">{{ number_format($item->qty_return, 2) }}</td>
                                                    <td>{{ $item->satuan }}</td>
                                                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                    <td class="text-right">
                                                        <strong>Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
                                                    </td>
                                                    <td>
                                                        @switch($item->status_item)
                                                            @case('pending')
                                                                <span class="badge badge-warning">Pending</span>
                                                                @break
                                                            @case('approved')
                                                                <span class="badge badge-success">Approved</span>
                                                                @break
                                                            @case('rejected')
                                                                <span class="badge badge-danger">Rejected</span>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-light">
                                                <tr>
                                                    <th colspan="6" class="text-right">Total Return:</th>
                                                    <th class="text-right">
                                                        <strong>Rp {{ number_format($returnBarang->total_return, 0, ',', '.') }}</strong>
                                                    </th>
                                                    <th colspan="2"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    @can('manage return barang')
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    @if($returnBarang->isPending())
                                    <button type="button" class="btn btn-success mr-2" onclick="approveReturn({{ $returnBarang->id }})">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-danger mr-2" onclick="rejectReturn({{ $returnBarang->id }})">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </button>
                                    @endif
                                    
                                    @if($returnBarang->isApproved())
                                    <button type="button" class="btn btn-primary" onclick="processReturn({{ $returnBarang->id }})">
                                        <i class="fas fa-cogs mr-1"></i>Process Return
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endcan
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
                    <p>Apakah Anda yakin ingin approve return barang <strong>{{ $returnBarang->no_return }}</strong>?</p>
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
                    <p>Apakah Anda yakin ingin reject return barang <strong>{{ $returnBarang->no_return }}</strong>?</p>
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
                <p>Apakah Anda yakin ingin memproses return barang <strong>{{ $returnBarang->no_return }}</strong>?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i>
                    Proses ini akan mengupdate stok barang sesuai dengan tipe return:
                    <ul class="mb-0 mt-2">
                        <li><strong>Return Penjualan:</strong> Menambah stok barang</li>
                        <li><strong>Return Pembelian:</strong> Mengurangi stok barang</li>
                    </ul>
                </div>
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
</script>
@endpush
