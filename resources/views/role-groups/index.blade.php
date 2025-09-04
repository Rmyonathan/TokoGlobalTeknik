@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i> Role Groups Management
                    </h3>
                    <div>
                        <a href="{{ route('role-groups.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Role Group
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

                    @if($roleGroups->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada Role Groups</h5>
                            <p class="text-muted">Klik tombol "Tambah Role Group" untuk membuat group pertama.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($roleGroups as $group)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-left" style="border-left-color: {{ $group->formatted_color }} !important; border-left-width: 4px !important;">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $group->formatted_icon }}" style="color: {{ $group->formatted_color }}; margin-right: 8px;"></i>
                                                <h6 class="mb-0">{{ $group->display_name }}</h6>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('role-groups.show', $group) }}">
                                                        <i class="fas fa-eye"></i> Lihat Detail
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('role-groups.edit', $group) }}">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <form action="{{ route('role-groups.toggle-status', $group) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-{{ $group->is_active ? 'pause' : 'play' }}"></i>
                                                            {{ $group->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                        </button>
                                                    </form>
                                                    @if($group->roles->count() == 0)
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('role-groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus role group ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text text-muted small">{{ $group->description }}</p>
                                            
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <div class="border-right">
                                                        <h5 class="mb-0 text-primary">{{ $group->role_count }}</h5>
                                                        <small class="text-muted">Roles</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <h5 class="mb-0 text-success">{{ $group->user_count }}</h5>
                                                    <small class="text-muted">Users</small>
                                                </div>
                                            </div>

                                            @if($group->roles->count() > 0)
                                                <div class="mt-3">
                                                    <small class="text-muted">Roles:</small>
                                                    <div class="mt-1">
                                                        @foreach($group->roles->take(3) as $role)
                                                            <span class="badge badge-secondary mr-1">{{ $role->display_name ?: $role->name }}</span>
                                                        @endforeach
                                                        @if($group->roles->count() > 3)
                                                            <span class="badge badge-light">+{{ $group->roles->count() - 3 }} more</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-footer">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-sort-numeric-up"></i> Order: {{ $group->sort_order }}
                                                </small>
                                                <span class="badge badge-{{ $group->is_active ? 'success' : 'secondary' }}">
                                                    {{ $group->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
