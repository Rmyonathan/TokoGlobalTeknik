@extends('layout.Nav')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="title-box">
        <h2><i class="fas fa-eye mr-2"></i>Detail Transfer Stok</h2>
    </div>

    <!-- Transfer Information -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Informasi Transfer</h5>
            <div>
                @switch($stockTransfer->status)
                    @case('pending')
                        <span class="badge badge-warning badge-lg">Pending</span>
                        @break
                    @case('approved')
                        <span class="badge badge-info badge-lg">Approved</span>
                        @break
                    @case('completed')
                        <span class="badge badge-success badge-lg">Completed</span>
                        @break
                    @case('cancelled')
                        <span class="badge badge-danger badge-lg">Cancelled</span>
                        @break
                @endswitch
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>No Transfer:</strong><br>
                    <span class="text-primary">{{ $stockTransfer->no_transfer }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Tanggal Transfer:</strong><br>
                    {{ $stockTransfer->tanggal_transfer->format('d/m/Y') }}
                </div>
                <div class="col-md-3">
                    <strong>Dari Database:</strong><br>
                    <span class="badge badge-info">
                        {{ $stockTransfer->from_database == 'primary' ? 'Database Utama' : 'Database Kedua' }}
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>Ke Database:</strong><br>
                    <span class="badge badge-success">
                        {{ $stockTransfer->to_database == 'primary' ? 'Database Utama' : 'Database Kedua' }}
                    </span>
                </div>
            </div>
            
            @if($stockTransfer->keterangan)
                <div class="row mt-3">
                    <div class="col-md-12">
                        <strong>Keterangan:</strong><br>
                        {{ $stockTransfer->keterangan }}
                    </div>
                </div>
            @endif
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <strong>Dibuat Oleh:</strong> {{ $stockTransfer->creator->name ?? 'N/A' }}<br>
                    <strong>Tanggal Dibuat:</strong> {{ $stockTransfer->created_at->format('d/m/Y H:i') }}
                </div>
                @if($stockTransfer->approved_by)
                    <div class="col-md-6">
                        <strong>Disetujui Oleh:</strong> {{ $stockTransfer->approver->name ?? 'N/A' }}<br>
                        <strong>Tanggal Disetujui:</strong> {{ $stockTransfer->approved_at->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Transfer Items -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Detail Item Transfer</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty Transfer</th>
                            <th>Satuan</th>
                            <th>Harga/Unit</th>
                            <th>Total Value</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockTransfer->items as $item)
                            <tr>
                                <td><strong>{{ $item->kode_barang }}</strong></td>
                                <td>{{ $item->nama_barang }}</td>
                                <td class="text-right">{{ number_format($item->qty_transfer, 2) }}</td>
                                <td>{{ $item->satuan }}</td>
                                <td class="text-right">Rp {{ number_format($item->harga_per_unit, 0, ',', '.') }}</td>
                                <td class="text-right"><strong>Rp {{ number_format($item->total_value, 0, ',', '.') }}</strong></td>
                                <td>{{ $item->keterangan ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <td colspan="5" class="text-right"><strong>Total:</strong></td>
                            <td class="text-right">
                                <strong>Rp {{ number_format($stockTransfer->items->sum('total_value'), 0, ',', '.') }}</strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Stock Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Informasi Stok</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($stockTransfer->items as $item)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ $item->kode_barang }} - {{ $item->nama_barang }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Stok di {{ $stockTransfer->from_database == 'primary' ? 'Database Utama' : 'Database Kedua' }}:</strong><br>
                                        <span id="stock-from-{{ $item->kode_barang }}" class="text-danger">Loading...</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Stok di {{ $stockTransfer->to_database == 'primary' ? 'Database Utama' : 'Database Kedua' }}:</strong><br>
                                        <span id="stock-to-{{ $item->kode_barang }}" class="text-success">Loading...</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <strong>Global Stock:</strong><br>
                                        <span id="global-stock-{{ $item->kode_barang }}" class="text-info">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="form-group text-right">
        <a href="{{ route('stock-transfer.index') }}" class="btn btn-secondary mr-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
        
        @if($stockTransfer->status == 'pending')
            @can('approve stock transfer')
                <button type="button" class="btn btn-success mr-2" onclick="approveTransfer({{ $stockTransfer->id }})">
                    <i class="fas fa-check"></i> Approve Transfer
                </button>
            @endcan
            
            @can('cancel stock transfer')
                <button type="button" class="btn btn-danger" onclick="cancelTransfer({{ $stockTransfer->id }})">
                    <i class="fas fa-times"></i> Cancel Transfer
                </button>
            @endcan
        @endif
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
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Setelah disetujui, stok akan ditransfer antar database dan jurnal accounting akan dibuat. Tindakan ini tidak dapat dibatalkan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="approveForm" method="POST" style="display: inline;">
                    @csrf
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
                        <label for="keterangan_cancel">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="keterangan_cancel" 
                                  name="keterangan_cancel" 
                                  rows="3" 
                                  placeholder="Masukkan alasan pembatalan..." 
                                  required></textarea>
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
// Load stock information for each item
$(document).ready(function() {
    @foreach($stockTransfer->items as $item)
        loadStockInfo('{{ $item->kode_barang }}');
    @endforeach
});

function loadStockInfo(kodeBarang) {
    // Load stock breakdown
    $.ajax({
        url: '{{ route("api.stock-transfer.stock-breakdown") }}',
        method: 'GET',
        data: { kode_barang: kodeBarang },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                const fromDb = '{{ $stockTransfer->from_database }}';
                const toDb = '{{ $stockTransfer->to_database }}';
                
                // Update stock display
                $(`#stock-from-${kodeBarang}`).text(
                    `${data[fromDb]?.good_stock || 0} ${data[fromDb]?.satuan || 'PCS'}`
                );
                $(`#stock-to-${kodeBarang}`).text(
                    `${data[toDb]?.good_stock || 0} ${data[toDb]?.satuan || 'PCS'}`
                );
            }
        },
        error: function() {
            $(`#stock-from-${kodeBarang}`).text('Error loading');
            $(`#stock-to-${kodeBarang}`).text('Error loading');
        }
    });
    
    // Load global stock
    $.ajax({
        url: '{{ route("api.stock-transfer.global-stock") }}',
        method: 'GET',
        data: { kode_barang: kodeBarang },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $(`#global-stock-${kodeBarang}`).text(
                    `${data.good_stock} ${data.satuan || 'PCS'} (Total: ${data.total_stock})`
                );
            }
        },
        error: function() {
            $(`#global-stock-${kodeBarang}`).text('Error loading');
        }
    });
}

function approveTransfer(transferId) {
    $('#approveForm').attr('action', '{{ route("stock-transfer.approve", ":id") }}'.replace(':id', transferId));
    $('#approveModal').modal('show');
}

function cancelTransfer(transferId) {
    $('#cancelForm').attr('action', '{{ route("stock-transfer.cancel", ":id") }}'.replace(':id', transferId));
    $('#cancelModal').modal('show');
}
</script>
@endsection
