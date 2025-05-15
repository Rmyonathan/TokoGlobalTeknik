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

        <h2 class="mb-4">Create Role</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Create new users</h5>
                <form action="/storeAccount" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="role" value="first">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Role</label>
                        <select name="role_new" id="role_new" class="form-select">
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">
                                    {{ strtoupper($role->name) }}
                                </option>
                            @endforeach
                        </select>
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
@endsection
