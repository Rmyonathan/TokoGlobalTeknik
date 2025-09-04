@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Edit Role: {{ $role->display_name ?: $role->name }}
                    </h3>
                </div>
                <div class="card-body">
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

                    <form method="POST" action="{{ route('updateRole', $role->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $role->name) }}" 
                                           placeholder="e.g., sales_manager" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Unique role identifier (lowercase, no spaces)</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_name">Display Name</label>
                                    <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                           id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" 
                                           placeholder="e.g., Sales Manager">
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Human-readable name for the role</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="2" 
                                      placeholder="Brief description of this role's responsibilities">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="group_id">Role Group</label>
                                    <select class="form-control @error('group_id') is-invalid @enderror" 
                                            id="group_id" name="group_id">
                                        <option value="">-- Select Role Group (Optional) --</option>
                                        @foreach($roleGroups as $group)
                                            <option value="{{ $group->id }}" {{ old('group_id', $role->group_id) == $group->id ? 'selected' : '' }}>
                                                <i class="{{ $group->formatted_icon }}"></i> {{ $group->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('group_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Organize roles by grouping them together</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                               value="1" {{ old('is_active', $role->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            <strong>Active Role</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Inactive roles cannot be assigned to users</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Assign Permissions <span class="text-danger">*</span></label>
                            <div class="row">
                                @php
                                    $permissionsByModule = $permissions->groupBy(function($permission) {
                                        $parts = explode(' ', $permission->name);
                                        return ucfirst($parts[0]);
                                    });
                                    $rolePermissions = $role->permissions->pluck('name')->toArray();
                                @endphp
                                
                                @foreach($permissionsByModule as $module => $modulePermissions)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-header" style="cursor: pointer;">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-folder"></i> {{ $module }}
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @foreach($modulePermissions as $permission)
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" 
                                                               id="permission_{{ $permission->id }}" 
                                                               name="permissions[]" 
                                                               value="{{ $permission->name }}"
                                                               {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                            {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Role
                            </button>
                            <a href="{{ route('accounts.maintenance') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-danger float-right" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Delete Role
                            </button>
                        </div>
                    </form>

                    <!-- Delete Form (Hidden) -->
                    <form id="deleteForm" action="{{ route('deleteRole', $role->id) }}" method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-fill display name from role name
    $('#name').on('input', function() {
        const roleName = $(this).val();
        if (roleName && !$('#display_name').val()) {
            const displayName = roleName
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
            $('#display_name').val(displayName);
        }
    });

    // Select all permissions in a module
    $('.card-header').on('click', function() {
        const card = $(this).closest('.card');
        const checkboxes = card.find('input[type="checkbox"]');
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        
        checkboxes.prop('checked', !allChecked);
    });
});

function confirmDelete() {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
@endsection
