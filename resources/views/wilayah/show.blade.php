@extends('layout.Nav')

@section('title', 'Detail Wilayah')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-map-marker-alt"></i> Detail Wilayah: {{ $wilayah->nama_wilayah }}
                        </h4>
                        <div>
                            @can('edit wilayah')
                            <a href="{{ route('wilayah.edit', $wilayah) }}" class="btn btn-warning mr-2">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @endcan
                            <a href="{{ route('wilayah.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Nama Wilayah</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <strong>{{ $wilayah->nama_wilayah }}</strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Status</label>
                                        <div class="form-control-plaintext border rounded p-2">
                                            @if($wilayah->is_active)
                                                <span class="badge badge-success badge-lg">
                                                    <i class="fas fa-check-circle"></i> Aktif
                                                </span>
                                            @else
                                                <span class="badge badge-secondary badge-lg">
                                                    <i class="fas fa-pause-circle"></i> Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold text-primary">Keterangan</label>
                                <div class="form-control-plaintext border rounded p-2 bg-light">
                                    {{ $wilayah->keterangan ?: 'Tidak ada keterangan' }}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">ID Wilayah</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <code>{{ $wilayah->id }}</code>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Jumlah Pelanggan</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <span class="badge badge-info badge-lg">
                                                {{ $wilayah->customers->count() }} Pelanggan
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Tanggal Dibuat</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <i class="fas fa-calendar-plus"></i> 
                                            {{ $wilayah->created_at->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-primary">Terakhir Update</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <i class="fas fa-calendar-edit"></i> 
                                            {{ $wilayah->updated_at->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle"></i> Informasi Tambahan
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            <strong>Durasi Aktif:</strong><br>
                                            @php
                                                $duration = $wilayah->created_at->diffForHumans();
                                            @endphp
                                            {{ $duration }}
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-users"></i> 
                                            <strong>Status Pelanggan:</strong><br>
                                            @if($wilayah->customers->count() > 0)
                                                <span class="text-success">Memiliki pelanggan</span>
                                            @else
                                                <span class="text-warning">Belum ada pelanggan</span>
                                            @endif
                                        </small>
                                    </div>

                                    @if($wilayah->customers->count() > 0)
                                    <div class="alert alert-info">
                                        <small>
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Catatan:</strong> Wilayah ini tidak dapat dihapus karena masih memiliki pelanggan.
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($wilayah->customers->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-users"></i> Daftar Pelanggan di Wilayah Ini
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Pelanggan</th>
                                                    <th>Alamat</th>
                                                    <th>Telepon</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($wilayah->customers as $index => $customer)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $customer->nama_customer }}</strong>
                                                    </td>
                                                    <td>{{ $customer->alamat ?: '-' }}</td>
                                                    <td>{{ $customer->telepon ?: '-' }}</td>
                                                    <td>
                                                        @if($customer->is_active)
                                                            <span class="badge badge-success">Aktif</span>
                                                        @else
                                                            <span class="badge badge-secondary">Nonaktif</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    // Add any additional JavaScript functionality here
});
</script>
@endpush
