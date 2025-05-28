@extends('layout.Nav')

@section('content')
<section id="suppliers">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Supplier</h2>
        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#addSupplierModal">Tambah Supplier</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div>
        <form method="GET" action="{{ route('suppliers.index') }}" class="mb-3 d-flex">
            <select name="search_by" id="search_by" class="form-control w-25 mr-2">
            <option selected disabled value="">Cari Berdasarkan</option>
                <option value="kode_supplier" {{ request('search_by') == 'kode_supplier' ? 'selected' : '' }}>Kode Supplier</option>
                <option value="nama" {{ request('search_by') == 'nama' ? 'selected' : '' }}>Nama</option>
                <option value="alamat" {{ request('search_by') == 'alamat' ? 'selected' : '' }}>Alamat</option>
                <option value="pemilik" {{ request('search_by') == 'pemilik' ? 'selected' : '' }}>Pemilik</option>
                <option value="telepon_fax" {{ request('search_by') == 'telepon_fax' ? 'selected' : '' }}>Telepon/Fax</option>
                <option value="contact_person" {{ request('search_by') == 'contact_person' ? 'selected' : '' }}>Contact Person</option>
                <option value="hp_contact_person" {{ request('search_by') == 'hp_contact_person' ? 'selected' : '' }}>HP Contact Person</option>
                <option value="kode_kategori" {{ request('search_by') == 'kode_kategori' ? 'selected' : '' }}>Kode Kategori</option>
            </select>
            <input type="text" id="search_input" name="search" class="form-control w-50 mr-2" placeholder="Cari..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary mr-2">Cari</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
        
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Supplier</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Pemilik</th>
                <th>Telepon/Fax</th>
                <th>Contact Person</th>
                <th>HP Contact Person</th>
                <th>Kode Kategori</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->kode_supplier }}</td>
                    <td>{{ $supplier->nama }}</td>
                    <td>{{ $supplier->alamat }}</td>
                    <td>{{ $supplier->pemilik }}</td>
                    <td>{{ $supplier->telepon_fax }}</td>
                    <td>{{ $supplier->contact_person }}</td>
                    <td>{{ $supplier->hp_contact_person }}</td>
                    <td>{{ $supplier->kode_kategori }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editSupplierModal-{{ $supplier->id }}">Edit</button>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" style="display:inline;">
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
        {{ $suppliers->links() }}
    </div>
</section>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">Tambah Supplier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kode_supplier">Kode Supplier</label>
                        <input type="text" name="kode_supplier" class="form-control" value="{{ $newKodeSupplier }}" disabled>
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
                        <label for="pemilik">Pemilik</label>
                        <input type="text" name="pemilik" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="telepon_fax">Telepon/Fax</label>
                        <input type="text" name="telepon_fax" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="hp_contact_person">HP Contact Person</label>
                        <input type="text" name="hp_contact_person" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="kode_kategori">Kode Kategori</label>
                        <input type="text" name="kode_kategori" class="form-control" required>
                        @error('kode_kategori')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<!-- Edit Supplier Modals -->
@foreach($suppliers as $supplier)
<div class="modal fade" id="editSupplierModal-{{ $supplier->id }}" tabindex="-1" aria-labelledby="editSupplierModalLabel-{{ $supplier->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content" style="border: 3px solid black;">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSupplierModalLabel-{{ $supplier->id }}">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_supplier" class="form-label">Kode Supplier</label>
                        <input type="text" name="kode_supplier" class="form-control" value="{{ $supplier->kode_supplier }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" value="{{ $supplier->nama }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <input type="text" name="alamat" class="form-control" value="{{ $supplier->alamat }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="pemilik" class="form-label">Pemilik</label>
                        <input type="text" name="pemilik" class="form-control" value="{{ $supplier->pemilik }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon_fax" class="form-label">Telepon/Fax</label>
                        <input type="text" name="telepon_fax" class="form-control" value="{{ $supplier->telepon_fax }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_person" class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="{{ $supplier->contact_person }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="hp_contact_person" class="form-label">HP Contact Person</label>
                        <input type="text" name="hp_contact_person" class="form-control" value="{{ $supplier->hp_contact_person }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="kode_kategori" class="form-label">Kode Kategori</label>
                        <input type="text" name="kode_kategori" class="form-control" value="{{ $supplier->kode_kategori }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
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