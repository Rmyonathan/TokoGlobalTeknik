@extends('layout.Nav')

@section('content')
    <section class="container-fluid py-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Kas</h2>
            <a href="/logistics" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>

        <form action="/update_kas" method="POST" enctype="multipart/form-data">
            @csrf
            @isset($booking)
                @if ($booking->id)
                    <input type="hidden" class="form-control" id="booking_id" name="booking_id" value="{{ $booking->id }}" required>
                @endif
            @endisset
            <input type="hidden" class="form-control" id="transaction_id" name="transaction_id" value="{{ $kas->id }}" required>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $kas->name }}" required>
            </div>
            <div class="mb-3">
                @if (empty($hutang))
                    <label for="description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="description" name="description" rows="3" value="{{ old('description', $kas->description) }}" required>
                @else
                    <input type="hidden" class="form-control" id="description" name="description" rows="3" value="{{ old('description', $kas->description) }}" required readonly>
                @endif
            </div>
            <div class="mb-3">
                @if (empty($hutang))
                    <label for="transaction" class="form-label">Harga</label>
                    <input type="number" class="form-control" id="transaction" name="transaction" step="0.01" rows="3" value="{{ $kas->transaction / $kas->qty }}" required>
                @else
                    <label for="transaction" class="form-label">Jumlah yang mau dicicil</label>
                    <input type="number" class="form-control" id="transaction" name="transaction" step="0.01" rows="3" value="{{ $kas->transaction }}" required>
                @endif
            </div>
            @if (empty($hutang))
                <div class="mb-3">
                    <label for="qty" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="qty" name="qty" step="0.01" value="{{ $kas->qty }}" required>
                </div>
            @else
                <input type="hidden" class="form-control" id="qty" name="qty" step="0.01" value="{{ $kas->qty }}" required>
            @endif
            <div class="mb-3">
                <label for="qty" class="form-label">Type</label>
                <input type="text" class="form-control" id="type" name="type" step="0.01" value="{{ $kas->type }}" readonly required>
            </div>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Add Item
            </button>
        </form>
    </section>
@endsection