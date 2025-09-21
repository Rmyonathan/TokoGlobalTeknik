@extends('layout.Nav')

@section('content')
<div class="container">
    <h1>Detail Akun</h1>

    <dl class="row">
        <dt class="col-sm-3">Kode</dt>
        <dd class="col-sm-9">{{ $account->code }}</dd>

        <dt class="col-sm-3">Nama</dt>
        <dd class="col-sm-9">{{ $account->name }}</dd>

        <dt class="col-sm-3">Tipe</dt>
        <dd class="col-sm-9">{{ $account->type->name ?? '-' }}</dd>

        <dt class="col-sm-3">Parent</dt>
        <dd class="col-sm-9">{{ $account->parent->code ?? '-' }}</dd>

        <dt class="col-sm-3">Status</dt>
        <dd class="col-sm-9">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</dd>

        <dt class="col-sm-3">Saldo</dt>
        <dd class="col-sm-9">
            <span class="badge {{ $account->balance >= 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                {{ number_format($account->balance, 2) }}
            </span>
            @if($account->balance_updated_at)
                <small class="text-muted d-block">Terakhir diupdate: {{ $account->balance_updated_at->format('d/m/Y H:i') }}</small>
            @endif
        </dd>
    </dl>

    <a href="{{ route('chart-of-accounts.edit', $account) }}" class="btn btn-warning">Edit</a>
    <button type="button" class="btn btn-success" onclick="recalculateBalance({{ $account->id }})">Recalculate Saldo</button>
    <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">Kembali</a>

    <hr>
    <h3>Jurnal Terkait</h3>
    <form method="GET" class="row g-2 mb-2">
        <div class="col-auto">
            <label for="tanggal_awal" class="col-form-label">Dari</label>
        </div>
        <div class="col-auto">
            <input type="date" id="tanggal_awal" name="tanggal_awal" value="{{ $start }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <label for="tanggal_akhir" class="col-form-label">Sampai</label>
        </div>
        <div class="col-auto">
            <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="{{ $end }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <a href="{{ route('chart-of-accounts.show', $account) }}" class="btn btn-sm btn-secondary">Reset</a>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Jurnal</th>
                    <th>Keterangan</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journalDetails as $jd)
                    <tr>
                        <td>{{ optional($jd->journal)->journal_date }}</td>
                        <td>{{ optional($jd->journal)->journal_no }}</td>
                        <td>{{ optional($jd->journal)->description }}</td>
                        <td class="text-end">{{ number_format($jd->debit, 2) }}</td>
                        <td class="text-end">{{ number_format($jd->credit, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada jurnal untuk akun ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>
        {{ $journalDetails->links() }}
    </div>
</div>

<script>
function recalculateBalance(accountId) {
    if (confirm('Apakah Anda yakin ingin menghitung ulang saldo akun ini?')) {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Loading...';
        button.disabled = true;
        
        fetch(`/chart-of-accounts/${accountId}/recalculate-balance`, {
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


