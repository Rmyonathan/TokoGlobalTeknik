@extends('layout.Nav')

@section('content')
    <section class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="mb-4">Edit Profile</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Edit Your Information</h5>
                <form action="/updateProfile" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="users_id" value="{{ $user['id'] }}">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{$user->name}}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{$user->email}}" required>
                    </div>
                    <div class="mb-3">
                        <label for="roles" class="form-label">Roles</label>
                        <select class="form-select select2-multiple" id="roles" name="roles[]" multiple>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" 
                                    {{ $user->roles->contains($role->id) ? 'selected' : '' }}>
                                    {{ $role->display_name ?: $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select one or more roles for this user</small>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Leave blank to keep current password">
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah anda yakin untuk mengubah profile ini?')">Update Profile</button>
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
