@extends('layout.Nav')

@section('content')
<section id="stok-owner">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sales</h2>
        <button class="btn btn-success" data-toggle="modal" data-target="#addStokOwnerModal">Tambah Sales</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <form method="GET" action="{{ route('stok_owner.index') }}" class="mb-3 d-flex">
        <select name="search_by" id="search_by" class="form-control w-25 mr-2">
            <option selected disabled value="">Cari Berdasarkan</option>
            <option value="keterangan" {{ request('search_by') == 'keterangan' ? 'selected' : '' }}>Keterangan</option>
            <option value="kode_stok_owner" {{ request('search_by') == 'kode_stok_owner' ? 'selected' : '' }}>Kode Sales</option>
        </select>
        <input type="text" id="search_input" name="search" class="form-control w-50 mr-2" placeholder="Cari..." value="{{ request('search') }}" disabled>
        <button type="submit" class="btn btn-primary mr-2">Cari</button>
        <a href="{{ route('stok_owner.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Sales</th>
                <th>Keterangan</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stokOwners as $stokOwner)
                <tr>
                    <td>{{ $stokOwner->kode_stok_owner }}</td>
                    <td>{{ $stokOwner->keterangan }}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editSalesModal-{{ $stokOwner->id }}">Edit</button>
                        <form action="{{ route('stok_owner.destroy', $stokOwner) }}" method="POST" style="display:inline;">
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
        {{ $stokOwners->links() }}
    </div>
</section>

<!-- Add Stok Owner Modal -->
<div class="modal fade" id="addStokOwnerModal" tabindex="-1" role="dialog" aria-labelledby="addStokOwnerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('stok_owner.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addStokOwnerModalLabel">Tambah Sales</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kode_stok_owner">Kode Sales</label>
                        <input type="text" name="kode_stok_owner" class="form-control" maxlength="12" required>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Sales Modals -->
@foreach($stokOwners as $stokOwner)
<div class="modal fade" id="editSalesModal-{{ $stokOwner->id }}" tabindex="-1" aria-labelledby="editSalesModalLabel-{{ $stokOwner->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('stok_owner.update', $stokOwner) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content" style="border: 3px solid black;">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSalesModalLabel-{{ $stokOwner->id }}">Edit Sales</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_stok_owner" class="form-label">Kode</label>
                        <input type="text" name="kode_stok_owner" class="form-control" value="{{ $stokOwner->kode_kode_stok_owner }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" value="{{ $stokOwner->keterangan }}" required>
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