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
                    <form action="{{ route('panels.store-inventory') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name"><i class="fas fa-ruler mr-1"></i> Panel Name</label>
                            <input type="text" step="0.01" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the name of the aluminum panels.</small>
                        </div>
                        
                        <!-- Kode Barang Search Input -->
                        <div class="form-group position-relative">
                            <label for="kode_barang_search"><i class="fas fa-ruler mr-1"></i>Kode Barang</label>
                            <input type="text" id="kode_barang_search" class="form-control @error('group_id') is-invalid @enderror" 
                                placeholder="Cari kode barang" autocomplete="off">
                            <input type="hidden" id="group_id" name="group_id" value="{{ old('group_id') }}" required>
                            <div id="kodeBarangDropdown" class="dropdown-menu w-100" style="max-height: 300px; overflow-y: auto;"></div>
                            @error('group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Cari dan pilih kode barang.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="cost"><i class="fas fa-ruler mr-1"></i> Harga Beli (per meters)</label>
                            <input type="number" step="0.01" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ old('cost') }}" required>
                            @error('cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the price.</small>
                        </div>
                        <div class="form-group">
                            <label for="price"><i class="fas fa-ruler mr-1"></i> Harga Jual (per meters)</label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the price.</small>
                        </div>

                        <div class="form-group">
                            <label for="quantity"><i class="fas fa-layer-group mr-1"></i> Quantity</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the number of panels to add to inventory.</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="confirmCheck" required>
                                <label class="custom-control-label" for="confirmCheck">I confirm that these panels are available in the warehouse</label>
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

<style>
#kodeBarangDropdown {
    display: none;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    background: white;
    z-index: 1050;
}

#kodeBarangDropdown .dropdown-item {
    padding: 8px 12px;
    cursor: pointer;
}

#kodeBarangDropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.position-relative {
    position: relative;
}
</style>

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
});

$(document).ready(function() {
    // Kode Barang Search
    const kodeBarangSearch = $('#kode_barang_search');
    const kodeBarangDropdown = $('#kodeBarangDropdown');
    const groupIdInput = $('#group_id');
    
    console.log('Search elements initialized');

    // Function to search for kode barang
    function searchKodeBarang(keyword) {
        console.log('Searching for:', keyword);
        
        $.ajax({
            url: "{{ route('api.kode-barang.search') }}",
            method: "GET",
            data: { keyword: keyword },
            success: function(data) {
                console.log('Search results:', data);
                
                let dropdownHtml = '';
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        dropdownHtml += `<a class="dropdown-item kode-barang-item" 
                                        data-kode="${item.kode_barang}" 
                                        data-attribute="${item.attribute}" 
                                        data-length="${item.length}">
                                        ${item.kode_barang} - ${item.attribute} - ${item.length}
                                        </a>`;
                    });
                } else {
                    dropdownHtml = '<a class="dropdown-item disabled">Tidak ada kode barang ditemukan</a>';
                }
                
                kodeBarangDropdown.html(dropdownHtml);
                kodeBarangDropdown.show();
            },
            error: function(xhr, status, error) {
                console.error('Error searching kode barang:', error);
                console.log('XHR status:', status);
                console.log('XHR response:', xhr.responseText);
                kodeBarangDropdown.html('<a class="dropdown-item disabled">Error: Tidak dapat mencari kode barang</a>');
                kodeBarangDropdown.show();
            }
        });
    }

    // Search on input with debounce (prevents too many requests)
    let searchTimeout;
    kodeBarangSearch.on('input', function() {
        const keyword = $(this).val().trim();
        console.log('Input detected:', keyword);
        
        clearTimeout(searchTimeout);
        
        if (keyword.length > 0) {
            searchTimeout = setTimeout(function() {
                searchKodeBarang(keyword);
            }, 300); // Wait 300ms before searching
        } else {
            kodeBarangDropdown.hide();
        }
    });

    // Handle click on search result
    $(document).on('click', '.kode-barang-item', function() {
        const kode = $(this).data('kode');
        const attribute = $(this).data('attribute');
        const length = $(this).data('length');
        
        console.log('Selected item:', { kode, attribute, length });
        
        groupIdInput.val(kode);
        kodeBarangSearch.val(`${kode} - ${attribute} - ${length}`);
        kodeBarangDropdown.hide();
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#kode_barang_search, #kodeBarangDropdown').length) {
            kodeBarangDropdown.hide();
        }
    });
    
    console.log('Kode barang search initialization complete');
});
</script>
@endsection