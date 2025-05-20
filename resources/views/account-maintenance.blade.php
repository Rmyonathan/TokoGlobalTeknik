@extends('layout.Nav')

@section('content')
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
    <a href="{{ route('createAccount') }}" class="btn btn-sm btn-success">
        <i class="fas fa-edit"></i> Create Account
    </a>
    <a href="{{ route('createRole') }}" class="btn btn-sm btn-success">
        <i class="fas fa-edit"></i> Create Role
    </a>
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