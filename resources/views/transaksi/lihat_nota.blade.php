@extends('layout.Nav')

@section('content')
<div class="container py-2">
    <h3>Daftar Transaksi</h3>

    <!-- Search and Date Filters -->
    <form action="{{ route('transaksi.index') }}" method="GET" class="row mb-3">
        <div class="col-md-3 mb-2">
            <select name="search_by" id="search_by" class="form-control">
                <option selected disabled value="">Cari Berdasarkan</option>
                <option value="no_transaksi" {{ request('search_by') == 'no_transaksi' ? 'selected' : '' }}>No Transaksi</option>
                <option value="customer" {{ request('search_by') == 'customer' ? 'selected' : '' }}>Customer</option>
                <option value="alamat" {{ request('search_by') == 'alamat' ? 'selected' : '' }}>Alamat</option>
            </select>
        </div>
        <div class="col-md-3 mb-2">
            <input type="text" name="search" id="search_input" class="form-control" placeholder="Cari..." value="{{ request('search') }}" disabled>
        </div>
        <div class="col-md-2 mb-2">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-2 mb-2">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-2 mb-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <table class="table table-bordered" id="transactionTable" style="border: 1px">
        <thead>
            <tr>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->no_transaksi }}</td>
                    <td>{{ $transaction->tanggal }}</td>
                    <td>{{ $transaction->customer->nama ?? 'N/A' }}</td>
                    <td>{{ $transaction->customer->alamat ?? 'N/A' }}</td>
                    <td>{{ $transaction->customer->hp }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('transaksi.shownota', $transaction->id) }}" class="btn btn-primary btn-sm">Lihat Nota</a>
                        @if(!str_starts_with($transaction->status, 'cancelled'))
                            <form action="{{ route('transaksi.cancel', $transaction->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Batalkan transaksi ini?')">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                            </form>
                        @else
                        <span class="badge badge-danger">
                            {{ ucfirst($transaction->status) }}
                        </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $transactions->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen select dan input
    const searchBySelect = document.getElementById('search_by');
    const searchInput = document.getElementById('search_input');

    // Fungsi untuk mengecek status dropdown dan mengatur disabled state pada input
    function updateSearchInputState() {
        if (searchBySelect.value !== "" && searchBySelect.selectedIndex !== 0) {
            searchInput.disabled = false;
        } else {
            searchInput.disabled = true;
            searchInput.value = '';
        }
    }

    updateSearchInputState();
    searchBySelect.addEventListener('change', updateSearchInputState);
});
</script>
@endsection