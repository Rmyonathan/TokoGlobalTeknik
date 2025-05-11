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
                        <div class="form-group">
                            <label for="cost"><i class="fas fa-ruler mr-1"></i> Cost (per meters)</label>
                            <input type="number" step="0.01" class="form-control @error('cost') is-invalid @enderror" id="cost" name="cost" value="{{ $panel->cost }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the cost.</small>
                        </div>
                        <div class="form-group">
                            <label for="price"><i class="fas fa-ruler mr-1"></i> Price (per meters)</label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ $panel->price }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the price.</small>
                        </div>
                        <div class="form-group">
                            <label for="length"><i class="fas fa-ruler mr-1"></i> Panel Length (meters)</label>
                            <input type="number" step="0.01" class="form-control @error('length') is-invalid @enderror" id="length" name="length" value="{{ $panel->length }}" required>
                            @error('length')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Enter the length of the aluminum panels in meters.</small>
                        </div>

                        <div class="form-group">
                            <label for="quantity"><i class="fas fa-layer-group mr-1"></i> Quantity</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ $quantity }}" min="1" required>
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

                        <div class="form-row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Add to Inventory
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('panels.inventory') }}" class="btn btn-secondary btn-block">
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
});
</script>
@endsection
