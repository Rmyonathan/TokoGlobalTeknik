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
                <h5 class="card-title">Edit Your Information</h5>
                <form method="POST" action="{{ route('storeRole') }}">
                    @csrf
                    <input type="text" name="name" placeholder="Role name">

                    <h4>Assign Permissions:</h4>
                    @foreach ($permissions as $permission)
                        <label>
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}">
                            {{ $permission->name }}
                        </label><br>
                    @endforeach

                    <button type="submit">Create Role</button>
                </form>
            </div>
        </div>
    </section>
@endsection
