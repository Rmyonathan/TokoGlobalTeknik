@extends('layout.Nav')

@section('content')
    <section class="container-fluid py-4">
        <h2 class="mb-4">Edit Profile</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Your Information</h5>
                <form action="/updateProfile" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="johndoe@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="(123) 456-7890" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="mb-3">
                        <label for="roles" class="form-label">Roles</label>
                        <select class="form-select select2-multiple" id="roles" name="roles[]" multiple>
                            @if(isset($roles))
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" 
                                        {{ isset($user) && $user->roles->contains($role->id) ? 'selected' : '' }}>
                                        {{ $role->display_name ?: $role->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            @endif
                        </select>
                        <small class="form-text text-muted">Select one or more roles for this user</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
    $(document).ready(function() {
        // Initialize Select2 for roles
        $('.select2-multiple').select2({
            placeholder: 'Select roles...',
            allowClear: true,
            width: '100%',
            // theme: 'bootstrap-5'
        });
    });
    </script>
    @endpush
@endsection
