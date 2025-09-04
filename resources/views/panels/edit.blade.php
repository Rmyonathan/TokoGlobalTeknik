@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-plus-circle mr-2"></i>Add Panels to Inventory</h2>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">New Panel Stock Entry</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('panels.update-inventory') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name"><i class="fas fa-ruler mr-1"></i> Panel Name</label>
                            <input type="text" step="0.01" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ $panel->name }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the name of the aluminum panels.</small>
                        </div>
                        <div class="form-group">
                            <label for="group_id"><i class="fas fa-ruler mr-1"></i>Kode Barang</label>
                            <input type="text" step="0.01" class="form-control @error('group_id') is-invalid @enderror" id="group_id" name="group_id" value="{{ $panel->kode_barang }}" readonly>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the name of the item code.</small>
                        </div>


                        <!-- Field price sudah dihapus -->

                        <!-- Field length sudah dihapus -->


                        <div class="form-group">
                            <label for="quantity"><i class="fas fa-layer-group mr-1"></i> Quantity</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ $quantity }}" min="0" readonly>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the number of panels to add to inventory.</small>
                        </div>

                        <div class="form-group">
                            <label for="status"><i class="fas fa-layer-group mr-1"></i> Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="Active" {{ $panel->status == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $panel->status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select the status of the inventory.</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirmCheck" required>
                                <label class="custom-control-label" for="confirmCheck">I confirm that these panels are available in the warehouse</label>
                            </div>
                        </div>

                        <hr/>
                        <div class="form-group">
                            <label><i class="fas fa-cogs mr-1"></i> Konversi Satuan</label>
                            <div id="uc_list"></div>
                            <div class="d-flex mt-2">
                                <input type="text" id="uc_unit_add" class="form-control mr-2" placeholder="Satuan Turunan (mis. BOX)">
                                <input type="number" id="uc_value_add" class="form-control mr-2" placeholder="Nilai Konversi (ke kecil)">
                                <button type="button" id="uc_add_btn" class="btn btn-success">Tambah</button>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Add to Inventory
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('master.barang') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Common Panel Lengths</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-primary btn-block length-btn" data-length="4">
                                4 meters
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-primary btn-block length-btn" data-length="6">
                                6 meters
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-primary btn-block length-btn" data-length="8">
                                8 meters
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-primary btn-block length-btn" data-length="10">
                                10 meters
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-primary btn-block length-btn" data-length="12">
                                12 meters
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button class="btn btn-outline-primary btn-block length-btn" data-length="3.6">
                                3.6 meters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick length selection buttons
    const lengthButtons = document.querySelectorAll('.length-btn');
    const lengthInput = document.getElementById('length');

    lengthButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const length = this.getAttribute('data-length');
            lengthInput.value = length;
        });
    });
    // Inline Unit Conversion for panels/edit view (by kode_barang string)
    const ucList = document.getElementById('uc_list');
    const ucUnitAdd = document.getElementById('uc_unit_add');
    const ucValueAdd = document.getElementById('uc_value_add');
    const ucAddBtn = document.getElementById('uc_add_btn');
    const kodeBarangStr = document.getElementById('group_id').value; // field shows kode_barang

    function renderUc(items){
        if(!Array.isArray(items) || items.length===0){ ucList.innerHTML = '<div class="text-muted">Belum ada konversi.</div>'; return; }
        let html = '<table class="table table-sm"><thead><tr><th>Satuan</th><th>Nilai</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
        items.forEach(it => {
            html += `<tr id="uc-row-${it.id}">
                        <td>
                            <span class="uc-display" id="uc-unit-display-${it.id}">${it.unit_turunan}</span>
                            <input type="text" class="form-control form-control-sm uc-edit" id="uc-unit-edit-${it.id}" value="${it.unit_turunan}" style="display:none;">
                        </td>
                        <td>
                            <span class="uc-display" id="uc-value-display-${it.id}">${it.nilai_konversi}</span>
                            <input type="number" class="form-control form-control-sm uc-edit" id="uc-value-edit-${it.id}" value="${it.nilai_konversi}" style="display:none;">
                        </td>
                        <td>${it.is_active ? 'Aktif' : 'Nonaktif'}</td>
                        <td>
                            <div class="uc-display">
                                <button type="button" class="btn btn-sm btn-primary" data-id="${it.id}" data-action="edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" data-id="${it.id}" data-action="toggle" title="Toggle Status">
                                    <i class="fas fa-toggle-${it.is_active ? 'on' : 'off'}"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" data-id="${it.id}" data-action="delete" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="uc-edit" style="display:none;">
                                <button type="button" class="btn btn-sm btn-success" data-id="${it.id}" data-action="save" title="Simpan">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" data-id="${it.id}" data-action="cancel" title="Batal">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`
        });
        html += '</tbody></table>';
        ucList.innerHTML = html;
    }

    function loadUc(){
        fetch(`/unit-conversion/by-kode/${encodeURIComponent(kodeBarangStr)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => { 
                renderUc(data.items || []); 
            })
            .catch(error => {
                console.error('Load unit conversions failed:', error);
                ucList.innerHTML = '<div class="text-danger">Gagal memuat konversi satuan: ' + error.message + '</div>';
            });
    }
    loadUc();

    ucAddBtn.addEventListener('click', function(){
        const unit = (ucUnitAdd.value||'').trim();
        const val = parseInt(ucValueAdd.value||'0',10);
        if(!unit || val<1){ alert('Isi satuan dan nilai konversi >= 1'); return; }
        
        fetch(`/unit-conversion/by-kode/${encodeURIComponent(kodeBarangStr)}`, {
            method: 'POST',
            headers: { 
                'Content-Type':'application/json', 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ unit_turunan: unit, nilai_konversi: val })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Add unit conversion successful:', data);
                ucUnitAdd.value = '';
                ucValueAdd.value = '';
                loadUc();
            } else {
                // Handle validation errors
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat();
                    alert('Error: ' + errorMessages.join(', '));
                } else {
                    alert('Gagal menambah satuan konversi');
                }
            }
        })
        .catch(error => {
            console.error('Add unit conversion failed:', error);
            alert('Gagal menambah satuan konversi: ' + error.message);
        });
    });

    // Handle unit conversion actions (edit, save, cancel, toggle, delete)
    ucList.addEventListener('click', function(e){
        const btn = e.target.closest('button[data-id]');
        if(!btn) return;
        const id = btn.getAttribute('data-id');
        const action = btn.getAttribute('data-action');
        
        if(action === 'edit'){
            // Show edit mode
            document.querySelectorAll(`#uc-row-${id} .uc-display`).forEach(el => el.style.display = 'none');
            document.querySelectorAll(`#uc-row-${id} .uc-edit`).forEach(el => el.style.display = 'block');
        } else if(action === 'cancel'){
            // Cancel edit mode
            document.querySelectorAll(`#uc-row-${id} .uc-display`).forEach(el => el.style.display = 'block');
            document.querySelectorAll(`#uc-row-${id} .uc-edit`).forEach(el => el.style.display = 'none');
        } else if(action === 'save'){
            // Save changes
            const unit = document.getElementById(`uc-unit-edit-${id}`).value.trim();
            const value = parseInt(document.getElementById(`uc-value-edit-${id}`).value || '0', 10);
            
            if(!unit || value < 1){
                alert('Isi satuan dan nilai konversi >= 1');
                return;
            }
            
            fetch(`/unit-conversion/by-kode/${encodeURIComponent(kodeBarangStr)}/${id}`, {
                method: 'PUT',
                headers: { 
                    'Content-Type':'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ unit_turunan: unit, nilai_konversi: value })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('Update unit conversion successful:', data);
                    loadUc(); // Reload the list
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat();
                        alert('Error: ' + errorMessages.join(', '));
                    } else {
                        alert('Gagal mengupdate satuan konversi');
                    }
                }
            })
            .catch(error => {
                console.error('Update unit conversion failed:', error);
                alert('Gagal mengupdate satuan konversi: ' + error.message);
            });
        } else if(action === 'toggle'){
            fetch(`/unit-conversion/by-kode/${encodeURIComponent(kodeBarangStr)}/${id}/toggle`, { 
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Toggle successful:', data);
                loadUc();
            })
            .catch(error => {
                console.error('Toggle failed:', error);
                alert('Gagal mengubah status satuan konversi: ' + error.message);
            });
        } else if(action === 'delete'){
            if(!confirm('Hapus satuan ini?')) return;
            fetch(`/unit-conversion/by-kode/${encodeURIComponent(kodeBarangStr)}/${id}`, { 
                method: 'DELETE', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Delete successful:', data);
                loadUc();
            })
            .catch(error => {
                console.error('Delete failed:', error);
                alert('Gagal menghapus satuan konversi: ' + error.message);
            });
        }
    });
});
</script>
@endsection
