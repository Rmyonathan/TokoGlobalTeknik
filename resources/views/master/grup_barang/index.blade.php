@extends('layout.Nav')

@section('content')
<section id="grup-barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Master Grup Barang</h2>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('grup_barang.create') }}" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-plus mr-1"></i> Tambah Grup Barang
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(count($categories) > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description ?: 'N/A' }}</td>
                                    <td>
                                        @if($category->status === 'Active')
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $category->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('grup_barang.edit', $category->id) }}" class="btn btn-sm btn-success" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('grup_barang.toggle-status', $category->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $category->status === 'Active' ? 'btn-warning' : 'btn-info' }}" 
                                                        title="{{ $category->status === 'Active' ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                        onclick="return confirm('Apakah Anda yakin ingin {{ $category->status === 'Active' ? 'menonaktifkan' : 'mengaktifkan' }} grup barang ini?')">
                                                    <i class="fas fa-{{ $category->status === 'Active' ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('grup_barang.destroy', $category->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus grup barang ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $categories->links() }}
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> No categories found.
                </div>
            @endif
        </div>
    </div>
</section>
@endsection