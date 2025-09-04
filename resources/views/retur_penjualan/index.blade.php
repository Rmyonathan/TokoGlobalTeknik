@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-undo mr-2"></i>Retur Penjualan
                    </h3>
                    <div class="card-tools">
                        @can('create retur penjualan')
                        <a href="{{ route('retur-penjualan.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i>Tambah Retur
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="processed">Processed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="dateFrom" placeholder="Dari Tanggal">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="dateTo" placeholder="Sampai Tanggal">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info" onclick="filterData()">
                                <i class="fas fa-filter mr-1"></i>Filter
                            </button>
                            <button class="btn btn-secondary" onclick="resetFilter()">
                                <i class="fas fa-undo mr-1"></i>Reset
                            </button>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="returTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No. Retur</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>No. Transaksi</th>
                                    <th>Total Retur</th>
                                    <th>Status</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returPenjualan as $index => $retur)
                                <tr>
                                    <td>{{ $returPenjualan->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $retur->no_retur }}</strong>
                                    </td>
                                    <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $retur->customer->nama_customer ?? 'N/A' }}
                                        <br><small class="text-muted">{{ $retur->kode_customer }}</small>
                                    </td>
                                    <td>{{ $retur->no_transaksi }}</td>
                                    <td class="text-right">
                                        <strong>Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        @switch($retur->status)
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
                                    <td>{{ $retur->createdBy->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('view retur penjualan')
                                            <a href="{{ route('retur-penjualan.show', $retur->id) }}" 
                                               class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan

                                            @if($retur->status === 'pending')
                                                @can('edit retur penjualan')
                                                <a href="{{ route('retur-penjualan.edit', $retur->id) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan

                                                @can('manage retur penjualan')
                                                <button class="btn btn-success btn-sm" 
                                                        onclick="approveRetur({{ $retur->id }})" 
                                                        title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="rejectRetur({{ $retur->id }})" 
                                                        title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                @endcan

                                                @can('edit retur penjualan')
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteRetur({{ $retur->id }})" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endcan
                                            @elseif($retur->status === 'approved')
                                                @can('manage retur penjualan')
                                                <button class="btn btn-primary btn-sm" 
                                                        onclick="processRetur({{ $retur->id }})" 
                                                        title="Process">
                                                    <i class="fas fa-cogs"></i>
                                                </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data retur penjualan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between">
                        <div>
                            Menampilkan {{ $returPenjualan->firstItem() }} sampai {{ $returPenjualan->lastItem() }} 
                            dari {{ $returPenjualan->total() }} data
                        </div>
                        <div>
                            {{ $returPenjualan->links() }}
                        </div>
                    </div>
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
function filterData() {
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    let url = new URL(window.location);
    url.searchParams.set('status', status);
    url.searchParams.set('date_from', dateFrom);
    url.searchParams.set('date_to', dateTo);
    
    window.location.href = url.toString();
}

function resetFilter() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    
    let url = new URL(window.location);
    url.searchParams.delete('status');
    url.searchParams.delete('date_from');
    url.searchParams.delete('date_to');
    
    window.location.href = url.toString();
}

function approveRetur(id) {
    if (confirm('Apakah Anda yakin ingin menyetujui retur penjualan ini?')) {
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
    if (confirm('Apakah Anda yakin ingin memproses retur penjualan ini? Stok akan disesuaikan.')) {
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

function deleteRetur(id) {
    if (confirm('Apakah Anda yakin ingin menghapus retur penjualan ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/retur-penjualan/${id}`;
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        form.appendChild(methodField);
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

// Set filter values from URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementById('statusFilter').value = urlParams.get('status') || '';
    document.getElementById('dateFrom').value = urlParams.get('date_from') || '';
    document.getElementById('dateTo').value = urlParams.get('date_to') || '';
});
</script>
@endpush
