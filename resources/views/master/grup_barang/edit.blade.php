@extends('layout.Nav')

@section('content')
<section id="edit-grup-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Grup Barang</h2>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Edit Grup Barang: {{ $category->name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('grup_barang.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Grup Barang</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $category->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="Active" {{ old('status', $category->status) == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status', $category->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('grup_barang.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Grup Barang</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk assign items to this group --}}
    <div class="card mt-4">
        <div class="card-header">
			<h5 class="card-title d-flex justify-content-between align-items-center">
				<span>Kelola Barang dalam Grup Ini</span>
				<small class="text-muted">Pilih barang lalu simpan untuk mengikat ke grup</small>
			</h5>
        </div>
        <div class="card-body">
			<form action="{{ route('grup_barang.assign-items', $category->id) }}" method="POST">
                @csrf
				<div class="row g-3 align-items-end">
					<div class="col-md-8">
						<label for="item_ids" class="form-label">Pilih Barang</label>
						<select class="form-select form-select-sm select2" id="item_ids" name="item_ids[]" multiple size="8">
							@foreach(($allItems ?? []) as $it)
								<option 
									value="{{ $it->id }}" 
									data-kode="{{ $it->kode_barang }}"
									data-name="{{ $it->name }}"
									data-merek="{{ $it->merek }}"
									data-ukuran="{{ $it->ukuran }}"
									data-group="{{ $it->grup_barang_id == $category->id ? $category->name : ($it->grupBarang->name ?? $it->attribute) }}"
									{{ ($it->grup_barang_id == $category->id) ? 'selected' : '' }}
								>
									{{ $it->kode_barang }} - {{ $it->name }}
								</option>
							@endforeach
						</select>
						<small class="form-text text-muted">Gunakan pencarian untuk menemukan barang. Tahan Ctrl/Cmd untuk multi-pilih.</small>
					</div>
					<div class="col-md-4">
						<label class="form-label">Aksi Cepat</label>
						<div class="d-flex gap-2 mb-2">
							<button type="button" class="btn btn-outline-primary btn-sm" id="btnSelectAll">
								<i class="fas fa-check-double"></i> Pilih Semua
							</button>
							<button type="button" class="btn btn-outline-secondary btn-sm" id="btnClearSelection">
								<i class="fas fa-times"></i> Hapus Pilihan
							</button>
						</div>
						<div class="p-2 border rounded bg-light">
							<div class="d-flex justify-content-between">
								<span class="text-muted">Terpilih</span>
								<strong id="selectedCount">0</strong>
							</div>
						</div>
					</div>
				</div>
				<div class="mt-3 d-flex justify-content-between">
					<a href="{{ route('grup_barang.index') }}" class="btn btn-secondary">Batal</a>
					<button type="submit" class="btn btn-primary">
						<i class="fas fa-save"></i> Simpan Barang Grup
					</button>
				</div>
            </form>
        </div>
    </div>

    {{-- Bulk assign unit conversion to selected items --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">Terapkan Konversi Satuan ke Barang Terpilih</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="bulk_unit_turunan" class="form-label">Satuan Turunan</label>
                    <input type="text" id="bulk_unit_turunan" class="form-control" placeholder="mis. LUSIN">
                </div>
                <div class="col-md-4">
                    <label for="bulk_nilai_konversi" class="form-label">Nilai Konversi</label>
                    <input type="number" id="bulk_nilai_konversi" class="form-control" min="1" value="12">
                </div>
                <div class="col-md-4">
                    <button type="button" id="btnBulkAssignUC" class="btn btn-success">
                        Terapkan ke Barang Terpilih
                    </button>
                </div>
            </div>
            <small class="form-text text-muted d-block mt-2">Gunakan tombol di atas untuk menambahkan/memperbarui konversi satuan pada semua barang yang dipilih.</small>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            $('#item_ids').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih barang...',
                allowClear: true,
                closeOnSelect: false,
                selectionCssClass: 'select2-sm',
                dropdownCssClass: 'select2-sm',
                minimumInputLength: 2,
                language: {
                    inputTooShort: function () {
                        return 'Ketik minimal 2 karakter';
                    }
                },
                matcher: function(params, data) {
                    if ($.trim(params.term) === '') { return data; }
                    if (typeof data.text === 'undefined') { return null; }
                    const term = params.term.toLowerCase();
                    const el = $(data.element);
                    const haystack = (
                        (data.text || '') + ' ' +
                        (el.data('kode') || '') + ' ' +
                        (el.data('name') || '') + ' ' +
                        (el.data('merek') || '') + ' ' +
                        (el.data('ukuran') || '') + ' ' +
                        (el.data('group') || '')
                    ).toLowerCase();
                    return haystack.indexOf(term) > -1 ? data : null;
                },
                templateResult: function (data) {
                    if (!data.id) { return data.text; }
                    const el = $(data.element);
                    const kode = el.data('kode') || '';
                    const name = el.data('name') || data.text;
                    const merek = el.data('merek') || '';
                    const ukuran = el.data('ukuran') || '';
                    const group = el.data('group') || '';
                    const $container = $(
                        '<div class="d-flex flex-column">'
                        +   '<div><strong>' + kode + '</strong> - ' + name + '</div>'
                        +   '<small class="text-muted">' + [merek, ukuran, group].filter(Boolean).join(' • ') + '</small>'
                        + '</div>'
                    );
                    return $container;
                }
            }).on('change', function(){
                const count = ($(this).val() || []).length;
                document.getElementById('selectedCount').textContent = count;
            });
            // Set initial count
            document.getElementById('selectedCount').textContent = ($('#item_ids').val() || []).length;

            // Select All
            document.getElementById('btnSelectAll').addEventListener('click', function(){
                const allOptions = Array.from(document.querySelectorAll('#item_ids option')).map(o => o.value);
                $('#item_ids').val(allOptions).trigger('change');
            });
            // Clear Selection
            document.getElementById('btnClearSelection').addEventListener('click', function(){
                $('#item_ids').val(null).trigger('change');
            });
        }
    } catch (e) {
        console.error('Select2 init error:', e);
    }

    // Bulk assign unit conversion handler
    $('#btnBulkAssignUC').on('click', function() {
        const unit = ($('#bulk_unit_turunan').val() || '').trim();
        const nilai = parseInt($('#bulk_nilai_konversi').val(), 10);
        const selected = ($('#item_ids').val() || []).map(id => parseInt(id, 10));

        if (!unit) { alert('Satuan turunan wajib diisi'); return; }
        if (!nilai || nilai < 1) { alert('Nilai konversi harus >= 1'); return; }
        if (selected.length === 0) { alert('Pilih minimal satu barang'); return; }

        $.ajax({
            url: '{{ route('unit_conversion.bulk_assign') }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                unit_turunan: unit,
                nilai_konversi: nilai,
                item_ids: selected
            },
            success: function(res) {
                alert(res.message || 'Konversi satuan berhasil diterapkan.');
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errs = xhr.responseJSON.errors;
                    const msg = Object.keys(errs).map(k => errs[k]).join('\n');
                    alert(msg);
                } else {
                    alert('Gagal menerapkan konversi.');
                }
            }
        });
    });
});
</script>
<style>
.select2-container--bootstrap-5 .select2-selection--multiple {
	min-height: 32px;
	padding: 2px 4px;
}
.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
	display: none;
}
.select2-container--bootstrap-5 .select2-selection--multiple .select2-search--inline .select2-search__field {
	margin-top: 0;
	height: 26px;
	line-height: 26px;
}
.select2-sm .select2-results__option { padding: 4px 8px; font-size: .85rem; }
.select2-sm .select2-search__field { font-size: .85rem; }
</style>
@endsection