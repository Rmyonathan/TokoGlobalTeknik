@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-cogs text-primary"></i> Account Management
                    </h2>
                    <p class="text-muted mb-0">Manage users, roles, and permissions</p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <span class="badge badge-info">
                            <i class="fas fa-users"></i> {{ $users->count() }} Users
                        </span>
                        <span class="badge badge-success ml-2">
                            <i class="fas fa-user-tag"></i> {{ $roles->count() }} Roles
                        </span>
                        <span class="badge badge-warning ml-2">
                            <i class="fas fa-layer-group"></i> {{ $roleGroups->count() }} Groups
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Error:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Enhanced Navigation Tabs -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-0 p-0">
            <ul class="nav nav-tabs nav-fill" id="accountTabs" role="tablist">
        <li class="nav-item">
                    <a class="nav-link active d-flex align-items-center justify-content-center py-3" 
                       id="users-tab" data-toggle="tab" href="#users" role="tab">
                        <i class="fas fa-users mr-2"></i> 
                        <span>Users</span>
                        <span class="badge badge-primary ml-2">{{ $users->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-center py-3" 
                       id="role-groups-tab" data-toggle="tab" href="#role-groups" role="tab">
                        <i class="fas fa-layer-group mr-2"></i> 
                        <span>Role Groups</span>
                        <span class="badge badge-warning ml-2">{{ $roleGroups->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
                    <a class="nav-link d-flex align-items-center justify-content-center py-3" 
                       id="roles-tab" data-toggle="tab" href="#roles" role="tab">
                        <i class="fas fa-user-tag mr-2"></i> 
                        <span>Roles</span>
                        <span class="badge badge-success ml-2">{{ $roles->count() }}</span>
            </a>
        </li>
    </ul>
        </div>

    <div class="tab-content" id="accountTabsContent">
        <!-- Users Tab -->
        <div class="tab-pane fade show active" id="users" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="fas fa-users mr-2"></i> User Management
                    </h5>
                                <p class="mb-0 opacity-75">Manage user accounts and permissions</p>
                            </div>
                            <a href="{{ route('createAccount') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus mr-1"></i> Create User
                    </a>
                </div>
                    </div>
                    <div class="card-body p-0">
                    @if($users->isEmpty())
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-users fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-2">No users found</h5>
                                <p class="text-muted mb-4">Get started by creating your first user account</p>
                                <a href="{{ route('createAccount') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-1"></i> Create First User
                                </a>
                        </div>
                    @else
                        <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="border-0">
                                                <i class="fas fa-user mr-1"></i> Name
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-envelope mr-1"></i> Email
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-user-tag mr-1"></i> Roles
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-cog mr-1"></i> Actions
                                            </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                            <tr class="border-bottom">
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center mr-3"
                                                             style="background-color: {{ $user->avatar_color }}; color: #ffffff;">
                                                            {{ $user->initials }}
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                                            <small class="text-muted">ID: {{ $user->id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-primary">{{ $user->email }}</span>
                                                </td>
                                                <td class="align-middle">
                                                @if($user->roles->count() > 0)
                                                        <div class="d-flex flex-wrap">
                                                    @foreach($user->roles as $role)
                                                                <span class="badge badge-info mr-1 mb-1">
                                                                    <i class="fas fa-user-tag mr-1"></i>
                                                                    {{ $role->display_name ?: $role->name }}
                                                                </span>
                                                    @endforeach
                                                        </div>
                                                @else
                                                        <span class="text-muted">
                                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                                            No roles assigned
                                                        </span>
                                                @endif
                                            </td>
                                                <td class="align-middle">
                                                    <div class="btn-group" role="group">
                                                <form action="/editAccount" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="users_id" value="{{ $user->id }}">
                                                            <button class="btn btn-sm btn-outline-primary" type="submit" title="Edit User">
                                                                <i class="fas fa-edit"></i>
                                                    </button>
                                                </form>
                                                        <button class="btn btn-sm btn-outline-info" onclick="viewUserDetails({{ $user->id }})" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        @if($user->id !== auth()->id())
                                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->name }}')" title="Delete User">
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="fas fa-user-tag mr-2"></i> Roles Management
                    </h5>
                            <p class="mb-0 opacity-75">Manage roles and permissions</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-light btn-sm mr-2" onclick="showRoleTemplates()">
                                <i class="fas fa-magic mr-1"></i> Templates
                            </button>
                            <a href="{{ route('createRole') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus mr-1"></i> Create Role
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Enhanced Search and Filter Bar -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="roleSearch" placeholder="Search roles by name...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="roleGroupFilter">
                                <option value="">All Groups</option>
                                @foreach($roleGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="roleStatusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-secondary btn-block" onclick="clearRoleFilters()">
                                <i class="fas fa-times mr-1"></i> Clear
                            </button>
                        </div>
                    </div>

                    @if($roles->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-user-tag fa-4x text-muted"></i>
                            </div>
                            <h5 class="text-muted mb-2">No roles found</h5>
                            <p class="text-muted mb-4">Create roles to organize user permissions</p>
                            <a href="{{ route('createRole') }}" class="btn btn-success">
                                <i class="fas fa-plus mr-1"></i> Create First Role
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="rolesTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="border-0">
                                            <i class="fas fa-tag mr-1"></i> Role Name
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-user mr-1"></i> Display Name
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-layer-group mr-1"></i> Group
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-key mr-1"></i> Permissions
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-users mr-1"></i> Users
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-toggle-on mr-1"></i> Status
                                        </th>
                                        <th class="border-0">
                                            <i class="fas fa-cog mr-1"></i> Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                        <tr data-role-id="{{ $role->id }}" 
                                            data-role-name="{{ $role->name }}" 
                                            data-role-group="{{ $role->group_id }}" 
                                            data-role-status="{{ $role->is_active ? 'active' : 'inactive' }}"
                                            class="border-bottom">
                                            <td class="align-middle">
                                                <code class="bg-light px-2 py-1 rounded">{{ $role->name }}</code>
                                            </td>
                                            <td class="align-middle">
                                                <strong>{{ $role->display_name ?: $role->name }}</strong>
                                                @if($role->description)
                                                    <br><small class="text-muted">{{ Str::limit($role->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if($role->group_id)
                                                    @php
                                                        $group = $roleGroups->find($role->group_id);
                                                    @endphp
                                                    @if($group)
                                                        <span class="badge" style="background-color: {{ $group->formatted_color }}; color: white;">
                                                            <i class="{{ $group->formatted_icon }} mr-1"></i> {{ $group->display_name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="fas fa-question-circle mr-1"></i> Unknown Group
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-minus mr-1"></i> No Group
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-info">
                                                    <i class="fas fa-key mr-1"></i> {{ $role->permissions->count() }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-success">
                                                    <i class="fas fa-users mr-1"></i> {{ $role->users->count() }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-{{ $role->is_active ? 'success' : 'secondary' }}">
                                                    <i class="fas fa-{{ $role->is_active ? 'check' : 'times' }}-circle mr-1"></i>
                                                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('viewRole', $role->id) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                                            onclick="quickEditRole({{ $role->id }}, '{{ $role->name }}', {{ $role->permissions->count() }})" 
                                                            title="Quick Edit Permissions">
                                                        <i class="fas fa-bolt"></i>
                                                    </button>
                                                    <a href="{{ route('editRole', $role->id) }}" class="btn btn-sm btn-outline-primary" title="Full Edit Role">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($role->users->count() == 0)
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmDeleteRole({{ $role->id }}, '{{ $role->name }}')" title="Delete Role">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled 
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

<!-- Quick Edit Role Modal -->
<div class="modal fade" id="quickEditModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-bolt"></i> Quick Edit Role: <span id="quickEditRoleName"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Current permissions: <span id="quickEditPermissionCount"></span>
                </div>
                <div class="row" id="quickEditPermissions">
                    <!-- Permissions will be loaded here via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuickEdit()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Role Templates Modal -->
<div class="modal fade" id="roleTemplatesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-magic"></i> Role Templates
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-user-tie"></i> Admin</h6>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Full access to all features and settings</p>
                                <button class="btn btn-primary btn-sm" onclick="applyRoleTemplate('admin')">
                                    Use Template
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-line"></i> Manager</h6>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Management access with reporting capabilities</p>
                                <button class="btn btn-success btn-sm" onclick="applyRoleTemplate('manager')">
                                    Use Template
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-shopping-cart"></i> Sales</h6>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Sales operations and customer management</p>
                                <button class="btn btn-info btn-sm" onclick="applyRoleTemplate('sales')">
                                    Use Template
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0"><i class="fas fa-boxes"></i> Inventory</h6>
                            </div>
                            <div class="card-body">
                                <p class="card-text">Inventory management and stock operations</p>
                                <button class="btn btn-warning btn-sm" onclick="applyRoleTemplate('inventory')">
                                    Use Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user mr-2"></i> User Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="avatar-lg rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="background-color: {{ auth()->user()->avatar_color }}; color: #ffffff;">
                            <span style="font-size: 28px; font-weight: 700;">{{ auth()->user()->initials }}</span>
                        </div>
                        <h5 id="userDetailsName" class="mb-1">Loading...</h5>
                        <p id="userDetailsEmail" class="text-muted">Loading...</p>
                    </div>
                    <div class="col-md-8">
                        <h6 class="mb-3">Assigned Roles</h6>
                        <div id="userDetailsRoles">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

    // Role search functionality
    $('#roleSearch').on('keyup', function() {
        filterRoles();
    });

    // Role group filter
    $('#roleGroupFilter').on('change', function() {
        filterRoles();
    });

    // Role status filter
    $('#roleStatusFilter').on('change', function() {
        filterRoles();
    });
});

function filterRoles() {
    const searchTerm = $('#roleSearch').val().toLowerCase();
    const groupFilter = $('#roleGroupFilter').val();
    const statusFilter = $('#roleStatusFilter').val();
    
    $('#rolesTable tbody tr').each(function() {
        const roleName = $(this).data('role-name').toLowerCase();
        const roleGroup = $(this).data('role-group');
        const roleStatus = $(this).data('role-status');
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !roleName.includes(searchTerm)) {
            showRow = false;
        }
        
        // Group filter
        if (groupFilter && roleGroup != groupFilter) {
            showRow = false;
        }
        
        // Status filter
        if (statusFilter && roleStatus !== statusFilter) {
            showRow = false;
        }
        
        $(this).toggle(showRow);
    });
}

function clearRoleFilters() {
    $('#roleSearch').val('');
    $('#roleGroupFilter').val('');
    $('#roleStatusFilter').val('');
    filterRoles();
}

function quickEditRole(roleId, roleName, permissionCount) {
    // Show quick edit modal
    $('#quickEditModal').modal('show');
    $('#quickEditRoleId').val(roleId);
    $('#quickEditRoleName').text(roleName);
    $('#quickEditPermissionCount').text(permissionCount);
    
    // Load role permissions via AJAX
    loadRolePermissions(roleId);
}

function loadRolePermissions(roleId) {
    $.ajax({
        url: `/roles/${roleId}/permissions`,
        method: 'GET',
        success: function(response) {
            displayRolePermissions(response.permissions, response.rolePermissions);
        },
        error: function() {
            alert('Error loading role permissions');
        }
    });
}

function displayRolePermissions(permissions, rolePermissions) {
    const container = $('#quickEditPermissions');
    container.empty();
    
    // Group permissions by module
    const groupedPermissions = {};
    permissions.forEach(permission => {
        const module = permission.name.split(' ')[0];
        if (!groupedPermissions[module]) {
            groupedPermissions[module] = [];
        }
        groupedPermissions[module].push(permission);
    });
    
    // Display grouped permissions
    Object.keys(groupedPermissions).forEach(module => {
        const moduleDiv = $(`
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">${module}</h6>
                    </div>
                    <div class="card-body">
                        ${groupedPermissions[module].map(permission => `
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input permission-checkbox" 
                                       id="quick_${permission.id}" 
                                       value="${permission.name}"
                                       ${rolePermissions.includes(permission.name) ? 'checked' : ''}>
                                <label class="form-check-label" for="quick_${permission.id}">
                                    ${permission.name.replace(/_/g, ' ')}
                                </label>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `);
        container.append(moduleDiv);
    });
}

function saveQuickEdit() {
    const roleId = $('#quickEditRoleId').val();
    const selectedPermissions = [];
    
    $('.permission-checkbox:checked').each(function() {
        selectedPermissions.push($(this).val());
    });
    
    $.ajax({
        url: `/roles/${roleId}/quick-update`,
        method: 'POST',
        data: {
            permissions: selectedPermissions,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            $('#quickEditModal').modal('hide');
            location.reload();
        },
        error: function() {
            alert('Error updating role permissions');
        }
    });
}

function showRoleTemplates() {
    $('#roleTemplatesModal').modal('show');
}

function applyRoleTemplate(templateName) {
    // Redirect to create role with template
    window.location.href = `/roles/create?template=${templateName}`;
}

function viewUserDetails(userId) {
    // Show user details modal
    $('#userDetailsModal').modal('show');
    // Load user details via AJAX
    loadUserDetails(userId);
}

function loadUserDetails(userId) {
    $.ajax({
        url: `/users/${userId}/details`,
        method: 'GET',
        success: function(response) {
            displayUserDetails(response.user);
        },
        error: function() {
            alert('Error loading user details');
        }
    });
}

function displayUserDetails(user) {
    $('#userDetailsName').text(user.name);
    $('#userDetailsEmail').text(user.email);
    $('#userDetailsRoles').empty();
    
    if (user.roles && user.roles.length > 0) {
        user.roles.forEach(role => {
            $('#userDetailsRoles').append(`
                <span class="badge badge-info mr-1 mb-1">
                    <i class="fas fa-user-tag mr-1"></i> ${role.display_name || role.name}
                </span>
            `);
        });
    } else {
        $('#userDetailsRoles').html('<span class="text-muted">No roles assigned</span>');
    }
}

function confirmDeleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete the user "${userName}"? This action cannot be undone.`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/users/delete/${userId}`;
        
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

@push('styles')
<style>
    /* Custom styles for Account Management */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745, #1e7e34);
    }
    
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107, #e0a800);
    }
    
    .avatar-sm {
        width: 40px;
        height: 40px;
        font-size: 16px;
        font-weight: bold;
    }
    
    .nav-tabs .nav-link {
        border: none;
        border-radius: 0;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: transparent;
    }
    
    .nav-tabs .nav-link.active {
        background-color: #fff;
        color: #007bff;
        border-color: transparent;
        border-bottom: 3px solid #007bff;
    }
    
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    .border-bottom {
        border-bottom: 1px solid #e9ecef !important;
    }
    
    .opacity-75 {
        opacity: 0.75;
    }
    
    /* Hover effects */
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Custom scrollbar */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush