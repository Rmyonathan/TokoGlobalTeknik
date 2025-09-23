@extends('layout.Nav')

@section('content')
<div class="card">
    <div class="card-header">
        Detail Faktur Komisi - {{ $salesNama }} ({{ $salesCode }})
    </div>
    <div class="card-body">
        <div class="mb-3">
            Periode: {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>No Faktur</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th class="text-right">Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $t)
                    <tr>
                        <td>{{ $t->no_transaksi }}</td>
                        <td>{{ optional($t->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ optional($t->customer)->nama ?? '-' }}</td>
                        <td class="text-right">{{ number_format($t->grand_total, 0, ',', '.') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="showItemDetail('{{ $t->id }}', '{{ $t->no_transaksi }}')">
                                <i class="fas fa-list"></i> Item
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('laporan.komisi-sales', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn-secondary">
            Kembali
        </a>
    </div>
</div>

<!-- Modal untuk menampilkan detail item -->
<div class="modal fade" id="itemDetailModal" tabindex="-1" role="dialog" aria-labelledby="itemDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemDetailModalLabel">Detail Item - <span id="modalNoTransaksi"></span></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="itemDetailContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showItemDetail(transaksiId, noTransaksi) {
    $('#modalNoTransaksi').text(noTransaksi);
    $('#itemDetailModal').modal('show');
    
    // Reset content
    $('#itemDetailContent').html(`
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `);
    
    // Fetch item details via AJAX
    $.ajax({
        url: '/api/transaksi/' + transaksiId + '/items',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                let html = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Qty</th>
                                    <th>Satuan</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                response.data.forEach(function(item) {
                    // Parse dan format angka dengan aman
                    const qty = parseFloat(item.qty) || 0;
                    const harga = parseFloat(item.harga) || 0;
                    const subtotal = parseFloat(item.subtotal) || 0;
                    
                    html += `
                        <tr>
                            <td>${item.kode_barang || '-'}</td>
                            <td>${item.nama_barang || '-'}</td>
                            <td class="text-right">${qty.toLocaleString('id-ID')}</td>
                            <td>${item.satuan || '-'}</td>
                            <td class="text-right">Rp ${harga.toLocaleString('id-ID')}</td>
                            <td class="text-right">Rp ${subtotal.toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                $('#itemDetailContent').html(html);
            } else {
                $('#itemDetailContent').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> ${response.message || 'Gagal memuat data item'}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            $('#itemDetailContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Terjadi kesalahan saat memuat data item
                </div>
            `);
        }
    });
}
// Bootstrap 4/5 close fallback
document.addEventListener('click', function(e){
    const btn = e.target.closest('[data-dismiss="modal"], [data-bs-dismiss="modal"]');
    if (!btn) return;
    const modalEl = btn.closest('.modal');
    if (!modalEl) return;
    // Try BS5 way
    if (window.bootstrap && window.bootstrap.Modal) {
        const inst = bootstrap.Modal.getOrCreateInstance(modalEl);
        inst.hide();
        return;
    }
    // Fallback to BS4/jQuery
    if (window.$) {
        $(modalEl).modal('hide');
    }
});
</script>
@endsection


