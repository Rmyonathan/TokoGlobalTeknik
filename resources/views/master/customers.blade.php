@extends('layout.Nav')

@section('content')
<section id="customers">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pelanggan</h2>
        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#addCustomerModal">Tambah Pelanggan</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <form method="GET" action="{{ route('customers.index') }}" class="mb-3 d-flex">
        <select name="search_by" id="search_by" class="form-control w-25 mr-2">
            <option selected disabled value="">Cari Berdasarkan</option>
            <option value="nama" {{ request('search_by') == 'nama' ? 'selected' : '' }}>Nama</option>
            <option value="kode_customer" {{ request('search_by') == 'kode_customer' ? 'selected' : '' }}>Kode Customer</option>
            <option value="alamat" {{ request('search_by') == 'alamat' ? 'selected' : '' }}>Alamat</option>
            <option value="hp" {{ request('search_by') == 'hp' ? 'selected' : '' }}>Nomor HP</option>
            <option value="telepon" {{ request('search_by') == 'telepon' ? 'selected' : '' }}>Telepon</option>
        </select>
        <input type="text" id="search_input" name="search" class="form-control w-50 mr-2" placeholder="Cari..." value="{{ request('search') }}" disabled>
        <button type="submit" class="btn btn-primary mr-2">Cari</button>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <table class="table table-bordered" style="border: 5px solid black; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Kode Customer</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>HP</th>
                <th>Telepon</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="customerTableBody">
        <tbody id="customerTableBody">
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->kode_customer }}</td>
                    <td>{{ $customer->nama }}</td>
                    <td>{{ $customer->alamat }}</td>
                    <td>{{ $customer->hp }}</td>
                    <td>{{ $customer->telepon }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCustomerModal-{{ $customer->id }}">Edit</button>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $customers->links() }}
    </div>
</section>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border: 3px solid black;">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Tambah Pelanggan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kode_customer">Kode Customer</label>
                        <input type="text" name="kode_customer" class="form-control" value="{{ $newKodeCustomer }}" disabled>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <input type="text" name="alamat" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="hp">HP</label>
                        <input type="text" name="hp" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telepon">Telepon</label>
                        <input type="text" name="telepon" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modals -->
@foreach($customers as $customer)
<div class="modal fade" id="editCustomerModal-{{ $customer->id }}" tabindex="-1" aria-labelledby="editCustomerModalLabel-{{ $customer->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content" style="border: 3px solid black;">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel-{{ $customer->id }}">Edit Pelanggan</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_customer" class="form-label">Kode Customer</label>
                        <input type="text" name="kode_customer" class="form-control" value="{{ $customer->kode_customer }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" value="{{ $customer->nama }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <input type="text" name="alamat" class="form-control" value="{{ $customer->alamat }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="hp" class="form-label">HP</label>
                        <input type="text" name="hp" class="form-control" value="{{ $customer->hp }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Telepon</label>
                        <input type="text" name="telepon" class="form-control" value="{{ $customer->telepon }}"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
    </div>
@endforeach
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen select dan input
    const searchBySelect = document.getElementById('search_by');
    const searchInput = document.getElementById('search_input');

    // Fungsi untuk mengecek status dropdown dan mengatur disabled state pada input
    function updateSearchInputState() {
        if (searchBySelect.value !== "" && searchBySelect.selectedIndex !== 0) {
            searchInput.disabled = false;
        } else {
            searchInput.disabled = true;
            searchInput.value = '';
        }
    }

    updateSearchInputState();
    searchBySelect.addEventListener('change', updateSearchInputState);
});
</script>
@endsection