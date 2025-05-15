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
</section>

<!-- Add Stok Owner Modal -->
<div class="modal fade" id="addStokOwnerModal" tabindex="-1" role="dialog" aria-labelledby="addStokOwnerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('stok_owner.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addStokOwnerModalLabel">Tambah Stok Owner</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="kode_stok_owner">Kode Stok Owner</label>
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
@endsection