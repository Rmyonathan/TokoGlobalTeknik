@extends('layout.Nav')

@section('content')
<!-- Database Selector -->
<section id="database-selector" class="mb-4">
    <div class="card shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Current Database: <span class="fw-bold text-primary">{{ ucfirst(session('selected_database', 'first_database')) }}</span></h5>
            <form action="/switchDatabase" method="POST" enctype="multipart/form-data" class="mb-0">
                @csrf
                <div class="input-group">
                    <select name="database" class="form-select form-select-lg" style="min-width: 200px;">
                        @foreach(config('database.available_databases') as $key => $db)
                            <option value="{{ $key }}" {{ session('selected_database', 'first_database') == $key ? 'selected' : '' }}>
                                {{ ucfirst($key) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-lg">Switch Database</button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Alert Messages -->
<section id="alerts" class="mb-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</section>

<!-- Profile Section -->
<section id="profile">
    <h2 class="mb-4">User Profiles</h2>

    @foreach($users as $user)
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Profile Information</h5>
            <p class="card-text"><strong>Name:</strong> {{ $user->name }}</p>
            <p class="card-text"><strong>Email:</strong> {{ $user->email }}</p>
            <p class="card-text"><strong>Role:</strong> {{ $user->role }}</p>
            <form action="/editAccount" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="users_id" value="{{ $user['id'] }}">
                <button class="btn btn-primary" type="submit">Edit</button>
            </form>
        </div>
    </div>
    @endforeach
</section>
@endsection