@extends('layout.Nav')

@section('content')
<section id="kategori">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Master Kategori Barang</h2>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('kategori.create') }}" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-plus mr-1"></i> Tambah Kategori
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
                                    <td>{{ $category->status }}</td>
                                    <td>{{ $category->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('kategori.edit', $category->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('kategori.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
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