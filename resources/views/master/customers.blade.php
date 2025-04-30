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
        <tbody>
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
