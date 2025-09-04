@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="accountTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="users-tab" data-toggle="tab" href="#users" role="tab">
                <i class="fas fa-users"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="role-groups-tab" data-toggle="tab" href="#role-groups" role="tab">
                <i class="fas fa-layer-group"></i> Role Groups
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="roles-tab" data-toggle="tab" href="#roles" role="tab">
                <i class="fas fa-user-tag"></i> Roles
            </a>
        </li>
    </ul>

    <div class="tab-content" id="accountTabsContent">
        <!-- Users Tab -->
        <div class="tab-pane fade show active" id="users" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users"></i> User Management
                    </h5>
                    <a href="{{ route('createAccount') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create User
                    </a>
                </div>
                <div class="card-body">
                    @if($users->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No users found</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->roles->count() > 0)
                                                    @foreach($user->roles as $role)
                                                        <span class="badge badge-secondary mr-1">{{ $role->display_name ?: $role->name }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No roles assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="/editAccount" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="users_id" value="{{ $user->id }}">
                                                    <button class="btn btn-sm btn-primary" type="submit">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Role Groups Tab -->
        <div class="tab-pane fade" id="role-groups" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-layer-group"></i> Role Groups Management
                    </h5>
                    <div>
                        <a href="{{ route('role-groups.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Role Group
                        </a>
                        <a href="{{ route('role-groups.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-external-link-alt"></i> Full View
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($roleGroups->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No role groups found</h5>
                            <p class="text-muted">Create role groups to organize your roles better.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($roleGroups as $group)
                                <div class="col-md-6 col-lg-4 mb-3">
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
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                    <a class="dropdown-item" href="{{ route('role-groups.edit', $group) }}">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="card-text text-muted small">{{ $group->description }}</p>
                                            
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <div class="border-right">
                                                        <h6 class="mb-0 text-primary">{{ $group->role_count }}</h6>
                                                        <small class="text-muted">Roles</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <h6 class="mb-0 text-success">{{ $group->user_count }}</h6>
                                                    <small class="text-muted">Users</small>
                                                </div>
                                            </div>

                                            @if($group->roles->count() > 0)
                                                <div class="mt-2">
                                                    <small class="text-muted">Roles:</small>
                                                    <div class="mt-1">
                                                        @foreach($group->roles->take(2) as $role)
                                                            <span class="badge badge-secondary mr-1">{{ $role->display_name ?: $role->name }}</span>
                                                        @endforeach
                                                        @if($group->roles->count() > 2)
                                                            <span class="badge badge-light">+{{ $group->roles->count() - 2 }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Roles Tab -->
        <div class="tab-pane fade" id="roles" role="tabpanel">
            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tag"></i> Roles Management
                    </h5>
                    <a href="{{ route('createRole') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create Role
                    </a>
                </div>
                <div class="card-body">
                    @if($roles->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-user-tag fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No roles found</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Display Name</th>
                                        <th>Group</th>
                                        <th>Permissions</th>
                                        <th>Users</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        <tr>
                                            <td>
                                                <code>{{ $role->name }}</code>
                                            </td>
                                            <td>{{ $role->display_name ?: $role->name }}</td>
                                            <td>
                                                @if($role->group_id)
                                                    @php
                                                        $group = $roleGroups->find($role->group_id);
                                                    @endphp
                                                    @if($group)
                                                        <span class="badge" style="background-color: {{ $group->formatted_color }}; color: white;">
                                                            <i class="{{ $group->formatted_icon }}"></i> {{ $group->display_name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Unknown Group</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No Group</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $role->permissions->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">{{ $role->users->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $role->is_active ? 'success' : 'secondary' }}">
                                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('viewRole', $role->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('editRole', $role->id) }}" class="btn btn-sm btn-primary" title="Edit Role">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($role->users->count() == 0)
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="confirmDeleteRole({{ $role->id }}, '{{ $role->name }}')" title="Delete Role">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-secondary" disabled 
                                                                title="Cannot delete role with assigned users">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tabs
    $('#accountTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
});

function confirmDeleteRole(roleId, roleName) {
    if (confirm(`Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/roles/delete/${roleId}`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection