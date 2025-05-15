@extends('layout.Nav')

@section('content')
<section id="perusahaan">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Master Perusahaan</h2>
        <a href="{{ route('perusahaan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Perusahaan
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Daftar Perusahaan</h5>
        </div>
        <div class="card-body">
            @if(count($perusahaan) > 0)
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Logo</th>
                            <th>Nama</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perusahaan as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($item->logo)
                                <img src="{{ $item->logo }}" alt="Logo {{ $item->nama }}" width="50" class="img-thumbnail">
                                @else
                                <span class="text-muted">No Logo</span>
                                @endif
                            </td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->alamat }}, {{ $item->kota }} {{ $item->kode_pos }}</td>
                            <td>{{ $item->telepon }}</td>
                            <td>{{ $item->email }}</td>
                            <td>
                                @if($item->is_active)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('perusahaan.edit', $item->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    
                                    @if(!$item->is_default)
                                    <form action="{{ route('perusahaan.set-default', $item->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-check"></i> Set Default
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-star"></i> Default
                                    </button>
                                    @endif
                                    
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                                
                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $item->id }}">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus perusahaan <strong>{{ $item->nama }}</strong>? Tindakan ini tidak dapat dibatalkan.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('perusahaan.destroy', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i> Belum ada data perusahaan. Silakan tambahkan data perusahaan baru.
            </div>
            @endif
        </div>
    </div>
</section>

<style>
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }
    
    .table-bordered {
        border: 1px solid #dee2e6;
    }
</style>
@endsection