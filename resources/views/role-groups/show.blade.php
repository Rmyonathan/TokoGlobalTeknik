@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="{{ $roleGroup->formatted_icon }}" style="color: {{ $roleGroup->formatted_color }}; margin-right: 10px; font-size: 1.5rem;"></i>
                        <div>
                            <h3 class="card-title mb-0">{{ $roleGroup->display_name }}</h3>
                            <small class="text-muted">{{ $roleGroup->description }}</small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('role-groups.edit', $roleGroup) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('role-groups.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users"></i> Roles dalam Group
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($roleGroup->roles->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Role</th>
                                                        <th>Display Name</th>
                                                        <th>Users</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($roleGroup->roles as $role)
                                                        <tr>
                                                            <td>
                                                                <code>{{ $role->name }}</code>
                                                            </td>
                                                            <td>{{ $role->display_name ?: $role->name }}</td>
                                                            <td>
                                                                <span class="badge badge-info">{{ $role->users_count }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-{{ $role->is_active ? 'success' : 'secondary' }}">
                                                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <form action="{{ route('role-groups.remove-role', [$roleGroup, $role]) }}" 
                                                                      method="POST" class="d-inline" 
                                                                      onsubmit="return confirm('Yakin ingin menghapus role dari group?')">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-times"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                            <h6 class="text-muted">Belum ada roles dalam group ini</h6>
                                            <p class="text-muted">Tambahkan roles untuk mengorganisir permissions.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-plus"></i> Tambah Role
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('role-groups.assign-role', $roleGroup) }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="role_id">Pilih Role</label>
                                            <select class="form-control" id="role_id" name="role_id" required>
                                                <option value="">-- Pilih Role --</option>
                                                @php
                                                    $availableRoles = \Spatie\Permission\Models\Role::whereNull('group_id')
                                                        ->orWhere('group_id', '!=', $roleGroup->id)
                                                        ->orderBy('name')
                                                        ->get();
                                                @endphp
                                                @foreach($availableRoles as $role)
                                                    <option value="{{ $role->id }}">{{ $role->display_name ?: $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-plus"></i> Tambah ke Group
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> Informasi Group
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-primary">{{ $roleGroup->roles->count() }}</h4>
                                            <small class="text-muted">Total Roles</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $roleGroup->user_count }}</h4>
                                            <small class="text-muted">Total Users</small>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="mb-2">
                                        <strong>Nama:</strong> {{ $roleGroup->name }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Urutan:</strong> {{ $roleGroup->sort_order }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Status:</strong> 
                                        <span class="badge badge-{{ $roleGroup->is_active ? 'success' : 'secondary' }}">
                                            {{ $roleGroup->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Warna:</strong> 
                                        <span class="badge" style="background-color: {{ $roleGroup->formatted_color }}; color: white;">
                                            {{ $roleGroup->formatted_color }}
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Icon:</strong> 
                                        <i class="{{ $roleGroup->formatted_icon }}"></i> {{ $roleGroup->icon }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
