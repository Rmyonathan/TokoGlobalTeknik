@extends('layout.Nav')

@section('content')
<div class="container">
    <h1 class="mb-3">Chart of Accounts</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('chart-of-accounts.create') }}" class="btn btn-primary">Tambah Akun</a>
        <button type="button" class="btn btn-success" onclick="recalculateBalances()">Recalculate Saldo</button>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Parent</th>
                <th>Saldo</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $account)
            <tr>
                <td>{{ $account->code }}</td>
                <td><a href="{{ route('chart-of-accounts.show', $account) }}">{{ $account->name }}</a></td>
                <td>{{ $account->type->name ?? '-' }}</td>
                <td>{{ $account->parent->code ?? '-' }}</td>
                <td class="text-end">
                    <span class="badge {{ $account->balance >= 0 ? 'bg-success' : 'bg-danger' }}">
                        {{ number_format($account->balance, 2) }}
                    </span>
                    @if($account->balance_updated_at)
                        <small class="text-muted d-block">{{ $account->balance_updated_at->format('d/m/Y H:i') }}</small>
                    @endif
                </td>
                <td>{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                <td>
                    <a href="{{ route('chart-of-accounts.edit', $account) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('chart-of-accounts.destroy', $account) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Hapus akun?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $accounts->links() }}
</div>

<script>
function recalculateBalances() {
    if (confirm('Apakah Anda yakin ingin menghitung ulang semua saldo akun?')) {
        // Show loading
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Loading...';
        button.disabled = true;
        
        fetch('{{ route("chart-of-accounts.recalculate-balances") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Saldo berhasil dihitung ulang!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Gagal menghitung ulang saldo'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: Gagal menghitung ulang saldo');
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}
</script>
@endsection


