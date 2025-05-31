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
                    <form action="{{ route('code.store-code') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name"><i class="fas fa-ruler mr-1"></i> Nama Barang</label>
                            <input type="text" step="0.01" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan Nama Barang.</small>
                        </div>
                        <div class="form-group">
                            <label for="searchPenambah"><i class="fas fa-search mr-1"></i> Masukkan Nama Grup Barang:</label>
                            <div class="input-group">
                                <input type="text" name="attribute" id="searchPenambah" class="form-control" placeholder="Cari kode barang...">
                                <div class="input-group-append">
                                    <button type="button" id="searchPenambahBtn" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="penambahDropdownContainer" class="position-relative">
                                <div id="penambahDropdown" class="dropdown-menu w-100" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="kode_barang"><i class="fas fa-ruler mr-1"></i>Kode</label>
                            <input type="text" step="0.01" class="form-control @error('kode_barang') is-invalid @enderror" id="kode_barang" name="kode_barang" value="{{ old('kode_barang') }}" required>
                            @error('kode_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan Kode Barang.</small>
                        </div>
                        <div class="form-group">
                            <label for="length"><i class="fas fa-ruler mr-1"></i> Panjang Barang (meters)</label>
                            <input type="number" step="0.01" class="form-control @error('panjang') is-invalid @enderror" id="length" name="length" value="{{ old('length') }}" required>
                            @error('length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan panjang barang dalam satuan meter.</small>
                        </div>
                        <div class="form-group">
                            <label for="cost"><i class="fas fa-ruler mr-1"></i> Harga Beli (per meters)</label>
                            <input type="number" step="0.01" class="form-control @error('Harga Beli') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost') }}" required>
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan Harga Beli.</small>
                        </div>
                        <div class="form-group">
                            <label for="price"><i class="fas fa-ruler mr-1"></i> Harga Jual (per meters)</label>
                            <input type="number" step="0.01" class="form-control @error('Harga Jual') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Masukkan Harga Jual.</small>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Tambahkan Barang
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
// Input search handlers
// Open search modals

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

    const searchPenambahInput = document.getElementById('searchPenambah');

    document.getElementById('searchPenambahBtn').addEventListener('click', function () {
        // Handle modal opening if needed
    });

    searchPenambahInput.addEventListener('input', function () {
        const keyword = this.value.trim(); // Trim whitespace
        
        if (keyword.length >= 1) {
            searchCodesDropdown(keyword, 'penambah');
        } else {
            // Hide dropdown when input is less than 1 characters or empty
            document.getElementById('penambahDropdown').style.display = 'none';
        }
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('penambahDropdown');
        const input = document.getElementById('searchPenambah');
        
        if (!dropdown.contains(e.target) && !input.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    function searchCodesDropdown(keyword, type) {
        const panels = @json($group_names ?? []);
        const dropdown = document.getElementById(`${type}Dropdown`);
        
        // Filter panels that match the keyword
        const matchingPanels = panels.filter(panel =>
            panel.toLowerCase().includes(keyword.toLowerCase())
        );

        if (matchingPanels.length > 0) {
            let html = '';
            matchingPanels.forEach(panel => {
                html += `<a class="dropdown-item ${type}-dropdown-item" data-kode="${panel}">
                            ${panel}
                         </a>`;
            });
            dropdown.innerHTML = html;
            dropdown.style.display = 'block';

            // Add click event listeners to the dropdown items
            document.querySelectorAll(`.${type}-dropdown-item`).forEach(item => {
                item.addEventListener('click', function () {
                    const kode = this.getAttribute('data-kode');
                    searchPenambahInput.value = kode;
                    dropdown.style.display = 'none';
                });
            });
        } else {
            // Only show "No matching codes found" if there was actually a search attempt
            if (keyword.length >= 2) {
                dropdown.innerHTML = '<div class="dropdown-item text-muted">No matching codes found</div>';
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }
    }
});
</script>
@endsection