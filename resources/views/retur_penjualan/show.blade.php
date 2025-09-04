@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>Detail Retur Penjualan
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('retur-penjualan.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Kembali
                        </a>
                        @if($returPenjualan->status === 'pending')
                            @can('edit retur penjualan')
                            <a href="{{ route('retur-penjualan.edit', $returPenjualan->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            @endcan
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Header Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Informasi Retur</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>No. Retur:</strong></td>
                                            <td>{{ $returPenjualan->no_retur }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal:</strong></td>
                                            <td>{{ $returPenjualan->tanggal->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                @switch($returPenjualan->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge badge-info">Approved</span>
                                                        @break
                                                    @case('processed')
                                                        <span class="badge badge-success">Processed</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                        @break
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dibuat Oleh:</strong></td>
                                            <td>{{ $returPenjualan->createdBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        @if($returPenjualan->approvedBy)
                                        <tr>
                                            <td><strong>Disetujui Oleh:</strong></td>
                                            <td>{{ $returPenjualan->approvedBy->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Disetujui:</strong></td>
                                            <td>{{ $returPenjualan->approved_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Informasi Customer & Transaksi</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Customer:</strong></td>
                                            <td>
                                                {{ $returPenjualan->customer->nama_customer ?? 'N/A' }}
                                                <br><small class="text-muted">{{ $returPenjualan->kode_customer }}</small>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. Transaksi:</strong></td>
                                            <td>{{ $returPenjualan->no_transaksi }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Retur:</strong></td>
                                            <td><strong>Rp {{ number_format($returPenjualan->total_retur, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alasan Retur -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Alasan Retur</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $returPenjualan->alasan_retur }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Item Retur</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Qty Asli</th>
                                                    <th>Qty Retur</th>
                                                    <th>Satuan</th>
                                                    <th>Harga</th>
                                                    <th>Total</th>
                                                    <th>Alasan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($returPenjualan->items as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $item->kode_barang }}</td>
                                                    <td>{{ $item->nama_barang }}</td>
                                                    <td class="text-right">{{ number_format($item->transaksiItem->qty ?? 0, 2) }}</td>
                                                    <td class="text-right">{{ number_format($item->qty_retur, 2) }}</td>
                                                    <td>{{ $item->satuan }}</td>
                                                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                                    <td class="text-right"><strong>Rp {{ number_format($item->total, 0, ',', '.') }}</strong></td>
                                                    <td>{{ $item->alasan ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-info">
                                                    <th colspan="7" class="text-right">Total Retur:</th>
                                                    <th class="text-right">Rp {{ number_format($returPenjualan->total_retur, 0, ',', '.') }}</th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nota Kredit Information -->
                    @if($returPenjualan->notaKredit->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Nota Kredit</h6>
                                </div>
                                <div class="card-body">
                                    @foreach($returPenjualan->notaKredit as $nota)
                                    <div class="alert alert-info">
                                        <strong>No. Nota Kredit:</strong> {{ $nota->no_nota_kredit }}<br>
                                        <strong>Tanggal:</strong> {{ $nota->tanggal->format('d/m/Y') }}<br>
                                        <strong>Total Kredit:</strong> Rp {{ number_format($nota->total_kredit, 0, ',', '.') }}<br>
                                        <strong>Keterangan:</strong> {{ $nota->keterangan }}
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    @if($returPenjualan->status === 'pending')
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    @can('manage retur penjualan')
                                    <button class="btn btn-success btn-lg mr-2" onclick="approveRetur({{ $returPenjualan->id }})">
                                        <i class="fas fa-check mr-2"></i>Approve Retur
                                    </button>
                                    <button class="btn btn-danger btn-lg mr-2" onclick="rejectRetur({{ $returPenjualan->id }})">
                                        <i class="fas fa-times mr-2"></i>Reject Retur
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($returPenjualan->status === 'approved')
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    @can('manage retur penjualan')
                                    <button class="btn btn-primary btn-lg" onclick="processRetur({{ $returPenjualan->id }})">
                                        <i class="fas fa-cogs mr-2"></i>Process Retur (Adjust Stock)
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tolak Retur Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="alasan_reject">Alasan Penolakan</label>
                        <textarea class="form-control" id="alasan_reject" name="alasan_reject" 
                                  rows="4" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function approveRetur(id) {
    if (confirm('Apakah Anda yakin ingin menyetujui retur penjualan ini? Nota kredit akan dibuat dan piutang customer akan dikurangi.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/retur-penjualan/${id}/approve`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectRetur(id) {
    document.getElementById('rejectForm').action = `/retur-penjualan/${id}/reject`;
    $('#rejectModal').modal('show');
}

function processRetur(id) {
    if (confirm('Apakah Anda yakin ingin memproses retur penjualan ini? Stok akan ditambahkan kembali.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/retur-penjualan/${id}/process`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
