@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-cut mr-2"></i>Order Aluminum Panel</h2>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">New Panel Order</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('panels.store-order') }}" method="POST">
                        @csrf
                        <div id="panel-orders">
                            <div class="panel-order border rounded p-3 mb-3">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-panel" style="z-index: 1;">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="form-group">
                                    <label for="name"><i class="fas fa-ruler mr-1"></i> Panel Name</label>
                                    <input type="text" name="panels[0][name]" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="length"><i class="fas fa-ruler mr-1"></i> Panel Length (meters)</label>
                                    <input type="number" name="panels[0][length]" class="form-control" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="quantity"><i class="fas fa-layer-group mr-1"></i> Quantity</label>
                                    <input type="number" name="panels[0][quantity]" class="form-control" min="1" required>
                                </div>
                            </div>
                        </div>


                        <button type="button" id="add-panel" class="btn btn-outline-primary mb-3">
                            <i class="fas fa-plus-circle mr-1"></i> Order Another Panel
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shopping-cart mr-1"></i> Process Order(s)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Current Inventory</h5>
                </div>
                <div class="card-body">
                    @if(isset($inventory) && count($inventory['inventory_by_length']) > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Length (m)</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inventory['inventory_by_length'] as $item)
                                        <tr>
                                            <td>{{ number_format($item['length'], 2) }}</td>
                                            <td>{{ $item['quantity'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-boxes mr-1"></i> Total Available Panels: <strong>{{ $inventory['total_panels'] }}</strong>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i> No panels currently in inventory.
                        </div>
                        <a href="{{ route('panels.create-inventory') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Add Panels to Inventory
                        </a>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb mr-1"></i> How It Works</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <i class="fas fa-check text-success mr-2"></i> The system first uses panels with exact requested length
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check text-success mr-2"></i> If needed, longer panels will be cut to the requested size
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-check text-success mr-2"></i> Remaining cut pieces are added back to inventory
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let panelIndex = 1;

    const container = document.getElementById('panel-orders');
    const addButton = document.getElementById('add-panel');

    addButton.addEventListener('click', function () {
        const newPanel = document.createElement('div');
        newPanel.classList.add('panel-order', 'border', 'rounded', 'p-3', 'mb-3', 'position-relative');

        newPanel.innerHTML = `
            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 remove-panel" style="z-index: 1;">
                <i class="fas fa-times"></i>
            </button>
            <div class="form-group">
                <label>Panel Name</label>
                <input type="text" name="panels[${panelIndex}][name]" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Panel Length (meters)</label>
                <input type="number" name="panels[${panelIndex}][length]" class="form-control" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="panels[${panelIndex}][quantity]" class="form-control" min="1" required>
            </div>
        `;

        container.appendChild(newPanel);
        panelIndex++;
    });

    // Delegate remove button click to container
    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-panel')) {
            e.target.closest('.panel-order').remove();
            renumberInputs();
        }
    });

    // Renumber name attributes
    function renumberInputs() {
        const panelForms = container.querySelectorAll('.panel-order');
        panelForms.forEach((panel, index) => {
            panel.querySelectorAll('input').forEach(input => {
                if (input.name.includes('[name]')) {
                    input.name = `panels[${index}][name]`;
                } else if (input.name.includes('[length]')) {
                    input.name = `panels[${index}][length]`;
                } else if (input.name.includes('[quantity]')) {
                    input.name = `panels[${index}][quantity]`;
                }
            });
        });
        panelIndex = panelForms.length;
    }
</script>
@endsection
