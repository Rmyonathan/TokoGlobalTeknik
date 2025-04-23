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
            <h2>Tambah Kas Baru</h2>
            <a href="/logistics" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>
        </div>

        <form action="/addTransaction" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            @if(empty($hutang))
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
            @endif
            <div class="mb-3">
                <label for="transaction" class="form-label">Harga</label>
                <input type="number" class="form-control" id="transaction" name="transaction" step="0.01" required>
            </div>
            @if (empty($hutang))
                <div class="mb-3">
                    <label for="qty" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="qty" name="qty" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="payment_method">Transaction Type</label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="">Select a Transaction Type</option>
                        <option value="Kredit">Debit</option>
                        <option value="Debit">Kredit</option>
                        <option value="Bonus">Bonus</option>
                        <option value="Hutang">Hutang</option>
                    </select>
                    <div class="form-text text-muted">
                        <p>Debit: Kurangi balance</p>
                        <p>Kredit:  Tambah balance</p>
                        <p>Bonus: Kalo Add Bonus </p>
                        <p>Hutang: Menambahkan Piutang</p>
                    </div>
                </div>
            @else
                <input type="hidden" name="qty" value="1">
                <input type="hidden" name="type" value="Hutang">
            @endif
            <button type="submit" class="btn btn-success">
                <i class="bi bi-plus-circle me-2"></i>Add Item
            </button>
        </form>
    </section>
@endsection