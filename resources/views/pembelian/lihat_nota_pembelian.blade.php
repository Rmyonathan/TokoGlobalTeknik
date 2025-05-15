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

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari No. Nota, Kode atau Nama Supplier">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="startDate">Dari Tanggal</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="endDate">Sampai Tanggal</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button id="applyFilter" class="btn btn-primary mr-2">Terapkan</button>
                    <button id="resetFilter" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Transaksi Pembelian</h5>
            <a href="{{ route('pembelian.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle"></i> Transaksi Baru
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="purchaseTable">
                    <thead>
                        <tr>
                            <th>No. Nota</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Cara Bayar</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->nota }}</td>
                            <td>{{ date('Y-m-d', strtotime($purchase->tanggal)) }}</td>
                            <td>{{ $purchase->kode_supplier }} - {{ $purchase->supplierRelation->nama ?? '' }}</td>
                            <td>{{ $purchase->cara_bayar }}</td>
                            <td class="text-right">Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</td>
                            <td>
                                @if(isset($purchase->status) && $purchase->status == 'canceled')
                                    <span class="badge badge-danger">Dibatalkan</span>
                                @else
                                    <span class="badge badge-success">Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('pembelian.nota.show', $purchase->id) }}" class="btn btn-sm btn-info" title="Lihat Nota">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if(!isset($purchase->status) || $purchase->status != 'canceled')
                                    <a href="{{ route('pembelian.edit', $purchase->id) }}" class="btn btn-sm btn-warning" title="Edit Nota">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <button type="button" class="btn btn-sm btn-danger cancel-btn" 
                                            data-toggle="modal" 
                                            data-target="#cancelModal" 
                                            data-id="{{ $purchase->id }}"
                                            data-nota="{{ $purchase->nota }}"
                                            title="Batalkan Nota">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    
                                   
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Dibatalkan oleh: {{ $purchase->canceled_by }} pada {{ date('d/m/Y H:i', strtotime($purchase->canceled_at)) }}">
                                        <i class="fas fa-info-circle"></i> Info
                                    </button>
                                @endif
                                
                                <a href="{{ route('pembelian.nota.show', $purchase->id) }}" class="btn btn-sm btn-primary" target="_blank" title="Cetak Nota">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data transaksi pembelian</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-4">
                    {{ $purchases->links() }}
                </div>
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

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Konfirmasi Pembatalan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan nota pembelian <strong id="cancel-nota-display"></strong>?</p>
                <p class="text-danger">Perhatian: Tindakan ini akan mengembalikan stok barang dan menandai transaksi sebagai batal!</p>
                
                <div class="form-group">
                    <label for="cancel_reason">Alasan Pembatalan:</label>
                    <textarea id="cancel_reason" class="form-control" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <form id="cancelForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="cancel_reason" id="cancel_reason_input">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Batalkan Transaksi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        function checkInDateRange(dateStr, start, end) {
            if (!start && !end) return true;
            if (!start && end) return dateStr <= end;
            if (start && !end) return dateStr >= start;
            return dateStr >= start && dateStr <= end;
        }

        function applyFilters() {
            const keyword = $('#searchInput').val().toLowerCase();
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();

            $('#purchaseTable tbody tr').each(function() {
                const noNota = $(this).find('td:nth-child(1)').text().toLowerCase();
                const supplierText = $(this).find('td:nth-child(3)').text().toLowerCase();
                const tanggal = $(this).find('td:nth-child(2)').text();

                const matchKeyword = noNota.includes(keyword) || supplierText.includes(keyword);
                const matchDate = checkInDateRange(tanggal, startDate, endDate);

                if (matchKeyword && matchDate) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        $('#applyFilter').on('click', applyFilters);

        $('#resetFilter').on('click', function() {
            $('#searchInput, #startDate, #endDate').val('');
            $('#purchaseTable tbody tr').show();
        });

        // Optional: filter langsung saat mengetik
        $('#searchInput').on('input', applyFilters);
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Cancel Modal handling
        $('.cancel-btn').click(function() {
            const id = $(this).data('id');
            const nota = $(this).data('nota');
            
            $('#cancel-nota-display').text(nota);
            $('#cancelForm').attr('action', `{{ url('/pembelian/cancel') }}/${id}`);
        });
        
        // Validate and submit the cancel form
        $('#cancelForm').on('submit', function() {
            const reason = $('#cancel_reason').val().trim();
            if (!reason) {
                alert('Alasan pembatalan harus diisi!');
                return false;
            }
            
            $('#cancel_reason_input').val(reason);
            return true;
        });
    });
</script>
@endsection