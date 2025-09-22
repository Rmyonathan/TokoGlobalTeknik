@extends('layout.Nav')

@section('content')
<section id="kode-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>List Kode Barang</h2>
        <div>
            <a href="{{ route('code.create-code') }}" class="btn btn-primary mr-2">
                <i class="fas fa-plus"></i> Tambah Kode Barang
            </a>
            
            <button type="button" class="btn btn-outline-secondary ml-2" data-toggle="modal" data-target="#manageUnitModal">
                <i class="fas fa-ruler-combined"></i> Kelola Satuan Besar
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if(isset($codes) && count($codes) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th style="border: 1px solid #000;">Kode Barang</th>
                                <th style="border: 1px solid #000;">Nama Barang</th>
                                <th style="border: 1px solid #000;">Satuan Kecil</th>
                                <th style="border: 1px solid #000;">Satuan Besar</th>
                                <th style="border: 1px solid #000;">Stok Tersedia</th>
                                <th style="border: 1px solid #000;">Status</th>
                                <th style="border: 1px solid #000;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($codes as $code)
                                <tr>
                                    <td style="border: 1px solid #000;">{{ $code->kode_barang }}</td>
                                    <td style="border: 1px solid #000;">{{ $code->name }}</td>
                                    <td style="border: 1px solid #000;">{{ $code->unit_dasar ?? '-' }}</td>
                                    <td style="border: 1px solid #000; white-space: nowrap;">
                                        @php
                                            $convs = \App\Models\UnitConversion::where('kode_barang_id', $code->id)->orderBy('unit_turunan')->get();
                                        @endphp
                                        @if($convs->count() === 0)
                                            <span class="badge badge-light">-</span>
                                        @else
                                            @foreach($convs as $uc)
                                                <span class="badge {{ $uc->is_active ? 'badge-success' : 'badge-secondary' }} mr-2 mb-1">
                                                    {{ $uc->unit_turunan }} = {{ $uc->nilai_konversi }} {{ $code->unit_dasar }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td style="border: 1px solid #000; text-align: center;">
                                        <span class="badge {{ ($code->available_stock ?? 0) > 0 ? 'badge-success' : 'badge-warning' }}">
                                            {{ number_format($code->available_stock ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td style="border: 1px solid #000;">{{ $code->status }}</td>
                                    <td style="border: 1px solid #000;">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('code.edit', $code->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('code.delete', $code->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kode ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $codes->links() }}
                </div>

            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada Kode Barang.
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Modal: Manage Unit Assignment -->
<div class="modal fade" id="manageUnitModal" tabindex="-1" role="dialog" aria-labelledby="manageUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageUnitModalLabel">Kelola Satuan Besar ke Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Satuan Besar</label>
                        <input type="text" id="unitFilter" class="form-control" placeholder="mis. LUSIN">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nilai Konversi</label>
                        <input type="number" id="unitValue" class="form-control" min="1" value="12">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-info w-100" id="btnLoadUnitItems">Muat Barang</button>
                    </div>
                </div>
                <div class="mt-3">
                    <label>Pilih Barang</label>
                    <select class="form-control select2" id="unitItems" multiple size="10" style="width:100%"></select>
                    <small class="form-text text-muted">Tandai barang yang memakai satuan besar ini.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="btnSyncUnit">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function ensureSelect2(){
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            $('#unitItems').select2({ theme: 'bootstrap-5', width: '100%', placeholder: 'Pilih barang...', allowClear: true, closeOnSelect: false });
        }
    }
    $('#manageUnitModal').on('shown.bs.modal', function(){ ensureSelect2(); });
    $('#btnLoadUnitItems').on('click', function(){
        const unit = ($('#unitFilter').val() || '').trim().toUpperCase();
        if (!unit) { alert('Isi Satuan Besar terlebih dahulu'); return; }
        $.get('{{ route('unit_conversion.items_by_unit') }}', { unit }, function(res){
            if (!res.success) { alert('Gagal memuat data'); return; }
            const $sel = $('#unitItems');
            $sel.empty();
            res.data.forEach(function(it){
                // Build neat label: KODE - Nama [Unit Kecil] {Satuan Besar: U1=V1, U2=V2}
                let bigUnits = '';
                if (Array.isArray(it.conversions) && it.conversions.length) {
                    const pairs = it.conversions.map(function(c){ return c.unit + '=' + c.nilai; }).join(', ');
                    bigUnits = ' {Satuan Besar: ' + pairs + '}';
                }
                const smallUnit = it.unit_dasar ? ' [' + it.unit_dasar + ']' : '';
                const label = it.name + smallUnit + bigUnits;
                const opt = new Option(label, it.id, it.selected, it.selected);
                $sel.append(opt);
            });
            $sel.trigger('change');
        }).fail(function(xhr){
            alert('Gagal memuat data: ' + (xhr.responseJSON?.message || xhr.status + ' ' + xhr.statusText));
        });
    });
    $('#btnSyncUnit').on('click', function(){
        const unit = ($('#unitFilter').val() || '').trim().toUpperCase();
        const nilai = parseInt($('#unitValue').val(), 10);
        const itemIds = ($('#unitItems').val() || []).map(v => parseInt(v, 10));
        if (!unit) { alert('Satuan Besar wajib diisi'); return; }
        if (!nilai || nilai < 1) { alert('Nilai Konversi harus >= 1'); return; }
        $.ajax({
            url: '{{ route('unit_conversion.bulk_sync_by_unit') }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                unit_turunan: unit,
                nilai_konversi: nilai,
                item_ids: itemIds
            }
        }).done(function(res){
            alert(res.message || 'Berhasil menyimpan');
            window.location.reload();
        }).fail(function(xhr){
            let msg = 'Gagal menyimpan';
            if (xhr.status === 419) msg += ' (CSRF)';
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) msg += ': ' + xhr.responseJSON.message;
                if (xhr.responseJSON.errors) {
                    msg += '\n' + Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
            }
            alert(msg);
        });
    });
})();
</script>

<style>
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000 !important;
    }

    .table-bordered {
        border: 2px solid #000;
    }
    
    /* Fix button group alignment */
    .btn-group form {
        display: inline-block;
        margin-left: 5px;
    }
</style>
@endsection