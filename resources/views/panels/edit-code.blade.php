@extends('layout.Nav')

@section('content')
<section id="edit-kode-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Kode Barang</h2>
        <a href="{{ route('code.view-code') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('code.update', $code->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group row">
                    <label for="name" class="col-sm-3 col-form-label">Nama Barang</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $code->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group row">
                    <label for="grup_barang_id" class="col-sm-3 col-form-label">Grup Barang</label>
                    <div class="col-sm-9">
                        <select class="form-control @error('grup_barang_id') is-invalid @enderror" id="grup_barang_id" name="grup_barang_id" required>
                            <option value="">Pilih Grup Barang</option>
                            @foreach($group_names ?? [] as $group_name)
                                <option value="{{ $group_name }}" {{ old('grup_barang_id', $code->attribute) == $group_name ? 'selected' : '' }}>
                                    {{ $group_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('grup_barang_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Pilih grup barang yang sesuai.</small>
                    </div>
                </div>

                <div class="form-group row" style="display: none;">
                    <label for="attribute" class="col-sm-3 col-form-label">Nama Panel</label>
                    <div class="col-sm-9">
                        <input type="hidden" class="form-control" id="attribute" name="attribute" value="{{ old('attribute', $code->attribute) }}">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="kode_barang" class="col-sm-3 col-form-label">Kode Barang</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input type="text" class="form-control" id="kode_barang" name="kode_barang" value="{{ old('kode_barang', $code->kode_barang) }}" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="regenerate_kode_btn">
                                    <i class="fas fa-sync-alt"></i> Generate Ulang
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Kode barang bisa di-generate ulang berdasarkan grup barang yang dipilih.</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="merek" class="col-sm-3 col-form-label">Merek</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control @error('merek') is-invalid @enderror" id="merek" name="merek" value="{{ old('merek', $code->merek) }}">
                        @error('merek')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="ukuran" class="col-sm-3 col-form-label">Ukuran</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control @error('ukuran') is-invalid @enderror" id="ukuran" name="ukuran" value="{{ old('ukuran', $code->ukuran) }}">
                        @error('ukuran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="unit_dasar" class="col-sm-3 col-form-label">Satuan Kecil</label>
                    <div class="col-sm-9">
                        <select class="form-control @error('unit_dasar') is-invalid @enderror" id="unit_dasar" name="unit_dasar" required>
                            <option value="">Pilih Satuan Kecil</option>
                            <option value="LBR" {{ old('unit_dasar', $code->unit_dasar) == 'LBR' ? 'selected' : '' }}>LBR (Lembar)</option>
                            <option value="KG" {{ old('unit_dasar', $code->unit_dasar) == 'KG' ? 'selected' : '' }}>KG (Kilogram)</option>
                            <option value="M" {{ old('unit_dasar', $code->unit_dasar) == 'M' ? 'selected' : '' }}>M (Meter)</option>
                            <option value="PCS" {{ old('unit_dasar', $code->unit_dasar) == 'PCS' ? 'selected' : '' }}>PCS (Pieces)</option>
                            <option value="PAK" {{ old('unit_dasar', $code->unit_dasar) == 'PAK' ? 'selected' : '' }}>PAK (Pack)</option>
                        </select>
                        @error('unit_dasar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Konversi Default</label>
                    <div class="col-sm-9">
                        <div class="form-row">
                            <div class="col-md-4">
                                <input type="text" class="form-control mb-2" id="satuan_dasar" name="satuan_dasar" placeholder="Satuan Dasar" value="{{ old('satuan_dasar', $code->satuan_dasar) }}">
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control mb-2" id="satuan_besar" name="satuan_besar" placeholder="Satuan Besar" value="{{ old('satuan_besar', $code->satuan_besar) }}">
                            </div>
                            <div class="col-md-4">
                                <input type="number" class="form-control mb-2" id="nilai_konversi" name="nilai_konversi" placeholder="Nilai" min="1" value="{{ old('nilai_konversi', $code->nilai_konversi) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="cost" class="col-sm-3 col-form-label">Harga Beli (per satuan dasar)</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" step="0.01" value="{{ old('cost', $code->cost) }}">
                        @error('cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="harga_jual" class="col-sm-3 col-form-label">Harga Jual per Satuan Dasar</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control @error('harga_jual') is-invalid @enderror" id="harga_jual" name="harga_jual" step="0.01" 
                            value="{{ old('harga_jual', $code->harga_jual) }}" required>
                        @error('harga_jual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="ongkos_kuli_default" class="col-sm-3 col-form-label">Ongkos Kuli Default</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control @error('ongkos_kuli_default') is-invalid @enderror" id="ongkos_kuli_default" name="ongkos_kuli_default" step="0.01" 
                            value="{{ old('ongkos_kuli_default', $code->ongkos_kuli_default) }}">
                        @error('ongkos_kuli_default')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row">
                    <label for="min_stock" class="col-sm-3 col-form-label">Minimum Stok (opsional)</label>
                    <div class="col-sm-9">
                        <input type="number" class="form-control @error('min_stock') is-invalid @enderror" id="min_stock" name="min_stock" min="0" 
                            value="{{ old('min_stock', $code->min_stock) }}" placeholder="Biarkan kosong jika tidak memakai minimum">
                        @error('min_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Jika stok total <= minimum, item akan ditandai kuning di daftar.</small>
                    </div>
                </div>

                <hr>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label">Konversi Satuan</label>
                    <div class="col-sm-9">
                        <div id="unit-conversions"></div>
                        <div class="d-flex mt-2">
                            <input type="text" id="uc_unit" class="form-control mr-2" placeholder="Satuan Turunan (mis. BOX)">
                            <input type="number" id="uc_value" class="form-control mr-2" placeholder="Nilai Konversi (ke kecil)">
                            <button type="button" id="uc_add" class="btn btn-success">Tambah</button>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill attribute field when grup barang is selected
    const grupBarangSelect = document.getElementById('grup_barang_id');
    const attributeInput = document.getElementById('attribute');
    const kodeBarangInput = document.getElementById('kode_barang');
    const regenerateBtn = document.getElementById('regenerate_kode_btn');

    // Update attribute when grup barang changes
    grupBarangSelect.addEventListener('change', function() {
        const selectedGrupBarang = this.value;
        if (selectedGrupBarang) {
            attributeInput.value = selectedGrupBarang;
            
            // Auto-generate kode barang
            generateNextKodeBarang(selectedGrupBarang);
        } else {
            attributeInput.value = '';
            kodeBarangInput.value = '';
        }
    });

    // Regenerate kode barang button
    regenerateBtn.addEventListener('click', function() {
        const selectedGrupBarang = grupBarangSelect.value;
        if (selectedGrupBarang) {
            generateNextKodeBarang(selectedGrupBarang);
        } else {
            alert('Pilih grup barang terlebih dahulu!');
        }
    });

    // Function to generate next kode barang
    function generateNextKodeBarang(selectedGrupBarang) {
        // Show loading state
        kodeBarangInput.value = 'Generating...';
        kodeBarangInput.disabled = true;
        regenerateBtn.disabled = true;

        // Call API to get next kode barang
        fetch(`/kode_barang/get-next-code?grup_barang_name=${encodeURIComponent(selectedGrupBarang)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    kodeBarangInput.value = data.next_code;
                    kodeBarangInput.disabled = false;
                    regenerateBtn.disabled = false;
                    
                    // Show success message
                    showMessage('Kode barang berhasil di-generate: ' + data.next_code, 'success');
                } else {
                    kodeBarangInput.value = '';
                    kodeBarangInput.disabled = false;
                    regenerateBtn.disabled = false;
                    showMessage('Error: ' + (data.error || 'Gagal generate kode barang'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                kodeBarangInput.value = '';
                kodeBarangInput.disabled = false;
                regenerateBtn.disabled = false;
                showMessage('Error: Gagal menghubungi server', 'error');
            });
    }

    // Function to show messages
    function showMessage(message, type) {
        // Remove existing messages
        const existingMessage = document.querySelector('.alert');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Create new message
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert message before the form
        const form = document.querySelector('form');
        form.parentNode.insertBefore(alertDiv, form);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    // Unit conversion inline management
    const ucContainer = document.getElementById('unit-conversions');
    const ucUnit = document.getElementById('uc_unit');
    const ucValue = document.getElementById('uc_value');
    const ucAdd = document.getElementById('uc_add');
    const kodeBarangId = {{ $code->id }};

    function renderConversions(items){
        if(!Array.isArray(items)) return;
        let html = '<table class="table table-sm"><thead><tr><th>Satuan</th><th>Nilai (ke kecil)</th><th>Status</th><th></th></tr></thead><tbody>';
        items.forEach(it => {
            html += `<tr>
                        <td>${it.unit_turunan}</td>
                        <td>${it.nilai_konversi}</td>
                        <td>${it.is_active ? 'Aktif' : 'Nonaktif'}</td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm mr-1" data-id="${it.id}" data-action="toggle">Toggle</button>
                            <button type="button" class="btn btn-danger btn-sm" data-id="${it.id}" data-action="delete">Hapus</button>
                        </td>
                    </tr>`
        });
        html += '</tbody></table>';
        ucContainer.innerHTML = html;
    }

    function loadConversions(){
        fetch(`/unit-conversion/${kodeBarangId}/list`)
            .then(r => r.json())
            .then(d => renderConversions(d));
    }
    loadConversions();

    ucAdd.addEventListener('click', function(){
        const unit = (ucUnit.value || '').trim();
        const value = parseInt(ucValue.value || '0', 10);
        if(!unit || value < 1) return alert('Isi satuan dan nilai konversi minimal 1');
        
        fetch(`/unit-conversion/${kodeBarangId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ unit_turunan: unit.toUpperCase(), nilai_konversi: value })
        }).then(()=> loadConversions());
        ucUnit.value=''; ucValue.value='';
    });

    ucContainer.addEventListener('click', function(e){
        const btnToggle = e.target.closest('button[data-action="toggle"]');
        const btnDelete = e.target.closest('button[data-action="delete"]');
        const id = (btnToggle || btnDelete)?.getAttribute('data-id');
        if(!id) return;
        if(btnToggle){
            fetch(`/unit-conversion/${kodeBarangId}/${id}/toggle`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
                .then(()=> loadConversions());
        }
        if(btnDelete){
            fetch(`/unit-conversion/${kodeBarangId}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
                .then(()=> loadConversions());
        }
    });
});
</script>
@endsection