@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Daftar Nota Pembelian</h2>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Transaksi Pembelian</h5>
            <a href="{{ route('pembelian.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle"></i> Transaksi Baru
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No. Nota</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Cara Bayar</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->nota }}</td>
                            <td>{{ date('d-m-Y', strtotime($purchase->tanggal)) }}</td>
                            <td>{{ $purchase->kode_supplier }} - {{ $purchase->supplierRelation->nama ?? '' }}</td>
                            <td>{{ $purchase->cara_bayar }}</td>
                            <td class="text-right">Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('pembelian.nota.show', $purchase->id) }}" class="btn btn-sm btn-info" title="Lihat Nota">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('pembelian.edit', $purchase->id) }}" class="btn btn-sm btn-warning" title="Edit Nota">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('pembelian.delete', $purchase->id) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus nota pembelian ini?');">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <a href="{{ route('pembelian.nota.show', $purchase->id) }}" class="btn btn-sm btn-primary" target="_blank" title="Cetak Nota">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data transaksi pembelian</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus nota pembelian ini?</p>
                <p class="text-danger">Perhatian: Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Setup delete confirmation
        $('.delete-purchase').click(function() {
            const id = $(this).data('id');
            $('#deleteForm').attr('action', `{{ url('pembelian/delete') }}/${id}`);
            $('#deleteModal').modal('show');
        });
    });
</script>
@endsection