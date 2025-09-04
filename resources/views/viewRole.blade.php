@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        @if($role->group)
                            <i class="{{ $role->group->formatted_icon }}" style="color: {{ $role->group->formatted_color }}; margin-right: 10px; font-size: 1.5rem;"></i>
                        @else
                            <i class="fas fa-user-tag" style="color: #6c757d; margin-right: 10px; font-size: 1.5rem;"></i>
                        @endif
                        <div>
                            <h3 class="card-title mb-0">{{ $role->display_name ?: $role->name }}</h3>
                            <small class="text-muted">{{ $role->description ?: 'No description provided' }}</small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('editRole', $role->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Role
                        </a>
                        <a href="{{ route('accounts.maintenance') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Management
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <!-- Role Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle"></i> Role Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Role Name:</strong>
                                                <br><code>{{ $role->name }}</code>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Display Name:</strong>
                                                <br>{{ $role->display_name ?: $role->name }}
                                            </div>
                                            <div class="mb-3">
                                                <strong>Status:</strong>
                                                <br>
                                                <span class="badge badge-{{ $role->is_active ? 'success' : 'secondary' }}">
                                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <strong>Role Group:</strong>
                                                <br>
                                                @if($role->group)
                                                    <span class="badge" style="background-color: {{ $role->group->formatted_color }}; color: white;">
                                                        <i class="{{ $role->group->formatted_icon }}"></i> {{ $role->group->display_name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">No Group Assigned</span>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <strong>Created:</strong>
                                                <br>{{ $role->created_at->format('d M Y, H:i') }}
                                            </div>
                                            <div class="mb-3">
                                                <strong>Last Updated:</strong>
                                                <br>{{ $role->updated_at->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($role->description)
                                        <div class="mt-3">
                                            <strong>Description:</strong>
                                            <p class="mt-2">{{ $role->description }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Permissions -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-key"></i> Permissions ({{ $role->permissions->count() }})
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($role->permissions->count() > 0)
                                        @php
                                            $permissionsByModule = $role->permissions->groupBy(function($permission) {
                                                $parts = explode(' ', $permission->name);
                                                return ucfirst($parts[0]);
                                            });
                                        @endphp
                                        
                                        @foreach($permissionsByModule as $module => $modulePermissions)
                                            <div class="mb-3">
                                                <h6 class="text-primary">
                                                    <i class="fas fa-folder"></i> {{ $module }}
                                                </h6>
                                                <div class="row">
                                                    @foreach($modulePermissions as $permission)
                                                        <div class="col-md-6 col-lg-4">
                                                            <span class="badge badge-light mr-1 mb-1">
                                                                {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-key fa-2x text-muted mb-3"></i>
                                            <h6 class="text-muted">No permissions assigned</h6>
                                            <p class="text-muted">This role doesn't have any permissions yet.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <!-- Statistics -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-bar"></i> Statistics
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <h4 class="text-primary">{{ $role->permissions->count() }}</h4>
                                            <small class="text-muted">Permissions</small>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $role->users->count() }}</h4>
                                            <small class="text-muted">Users</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assigned Users -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users"></i> Assigned Users ({{ $role->users->count() }})
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($role->users->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($role->users as $user)
                                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <div>
                                                        <strong>{{ $user->name }}</strong>
                                                        <br><small class="text-muted">{{ $user->email }}</small>
                                                    </div>
                                                    <span class="badge badge-info">{{ $user->created_at->format('M Y') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                            <h6 class="text-muted">No users assigned</h6>
                                            <p class="text-muted">This role is not assigned to any users yet.</p>
                                        </div>
                                    @endif
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
