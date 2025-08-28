@extends('layout.Nav')

@section('title', 'Daftar Wilayah')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Daftar Wilayah
                        </h4>
                        @can('manage wilayah')
                        <a href="{{ route('wilayah.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Wilayah
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Wilayah</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Jumlah Pelanggan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wilayahs as $index => $wilayah)
                                <tr>
                                    <td>{{ $index + 1 + ($wilayahs->currentPage() - 1) * $wilayahs->perPage() }}</td>
                                    <td>
                                        <strong>{{ $wilayah->nama_wilayah }}</strong>
                                    </td>
                                    <td>{{ $wilayah->keterangan ?: '-' }}</td>
                                    <td>
                                        @if($wilayah->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $wilayah->customers->count() }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('view wilayah')
                                            <a href="{{ route('wilayah.show', $wilayah) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('edit wilayah')
                                            <a href="{{ route('wilayah.edit', $wilayah) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('wilayah.toggle-status', $wilayah) }}" 
                                                  method="POST" 
                                                  style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-secondary" 
                                                        title="{{ $wilayah->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                        onclick="return confirm('Apakah Anda yakin ingin {{ $wilayah->is_active ? 'menonaktifkan' : 'mengaktifkan' }} wilayah ini?')">
                                                    <i class="fas fa-{{ $wilayah->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            @endcan
                                            
                                            @can('delete wilayah')
                                            <form action="{{ route('wilayah.destroy', $wilayah) }}" 
                                                  method="POST" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus wilayah ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Hapus"
                                                        {{ $wilayah->customers->count() > 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle"></i> Belum ada data wilayah.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($wilayahs->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $wilayahs->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
