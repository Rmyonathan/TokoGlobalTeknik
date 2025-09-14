@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Posisi Keuangan (Neraca)</div>
	<div class="card-body">
		<form class="row g-2 mb-3" method="GET">
			<div class="col-md-6">
				<label class="form-label">Periode</label>
				<select name="period_id" class="form-select" required>
					<option value="">- Pilih Periode -</option>
					@foreach ($periods as $p)
						<option value="{{ $p->id }}" {{ ($periodId==$p->id)?'selected':'' }}>{{ $p->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Tampilkan</button></div>
		</form>

		<div class="d-flex justify-content-end mb-2">
			<button type="button" id="btn-save-report" class="btn btn-success btn-sm">Simpan Laporan</button>
		</div>
		<div class="row" id="report-root">
			<div class="col-md-4">
				<h6>Aset</h6>
				<table class="table table-bordered table-sm">
					<thead><tr><th>Akun</th><th>Saldo</th></tr></thead>
					<tbody>
						@php($totalAssets=0)
						@foreach ($assets as $a)
							@php($val = (float)($a->debit_sum ?? 0) - (float)($a->credit_sum ?? 0))
							<tr><td>{{ $a->code }} - {{ $a->name }}</td><td>{{ number_format($val,2) }}</td></tr>
							@php($totalAssets += $val)
						@endforeach
					</tbody>
					<tfoot><tr><th>Total Aset</th><th>{{ number_format($totalAssets,2) }}</th></tr></tfoot>
				</table>
			</div>
			<div class="col-md-4">
				<h6>Kewajiban</h6>
				<table class="table table-bordered table-sm">
					<thead><tr><th>Akun</th><th>Saldo</th></tr></thead>
					<tbody>
						@php($totalLiab=0)
						@foreach ($liab as $l)
							@php($val = (float)($l->credit_sum ?? 0) - (float)($l->debit_sum ?? 0))
							<tr><td>{{ $l->code }} - {{ $l->name }}</td><td>{{ number_format($val,2) }}</td></tr>
							@php($totalLiab += $val)
						@endforeach
					</tbody>
					<tfoot><tr><th>Total Kewajiban</th><th>{{ number_format($totalLiab,2) }}</th></tr></tfoot>
				</table>
			</div>
			<div class="col-md-4">
				<h6>Ekuitas</h6>
				<table class="table table-bordered table-sm">
					<thead><tr><th>Akun</th><th>Saldo</th></tr></thead>
					<tbody>
						@php($totalEquity=0)
						@foreach ($equity as $e)
							@php($val = (float)($e->credit_sum ?? 0) - (float)($e->debit_sum ?? 0))
							<tr><td>{{ $e->code }} - {{ $e->name }}</td><td>{{ number_format($val,2) }}</td></tr>
							@php($totalEquity += $val)
						@endforeach
					</tbody>
					<tfoot><tr><th>Total Ekuitas</th><th>{{ number_format($totalEquity,2) }}</th></tr></tfoot>
				</table>
			</div>
		</div>

		<div class="alert alert-info">Aset = Kewajiban + Ekuitas ? {{ number_format($totalAssets,2) }} vs {{ number_format($totalLiab + $totalEquity,2) }}</div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const btn = document.getElementById('btn-save-report');
  if (!btn) return;
  btn.addEventListener('click', async function(){
    const periodSel = document.querySelector('select[name="period_id"]');
    const snapshot = { html: document.querySelector('.card-body').innerHTML };
    try {
      const res = await fetch("{{ route('accounting.reports.save') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
          report_name: 'Balance Sheet',
          accounting_period_id: periodSel ? periodSel.value : null,
          snapshot: snapshot
        })
      });
      const data = await res.json();
      if (data.success) alert('Laporan disimpan. ID: ' + data.id);
      else alert('Gagal menyimpan laporan');
    } catch(e){ alert('Error: ' + e.message); }
  });
});
</script>
@endsection


