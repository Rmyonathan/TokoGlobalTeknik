@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Laba Rugi</div>
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
			<div class="col-md-6">
				<h6>Pendapatan</h6>
				<table class="table table-bordered table-sm">
					<thead><tr><th>Akun</th><th>Nilai</th></tr></thead>
					<tbody>
						@php($totalRevenue=0)
						@foreach ($revenue as $r)
							@php($val = (float)($r->credit_sum ?? 0) - (float)($r->debit_sum ?? 0))
							<tr><td>{{ $r->code }} - {{ $r->name }}</td><td>{{ number_format($val,2) }}</td></tr>
							@php($totalRevenue += $val)
						@endforeach
					</tbody>
					<tfoot><tr><th>Total Pendapatan</th><th>{{ number_format($totalRevenue,2) }}</th></tr></tfoot>
				</table>
			</div>
			<div class="col-md-6">
				<h6>Beban</h6>
				<table class="table table-bordered table-sm">
					<thead><tr><th>Akun</th><th>Nilai</th></tr></thead>
					<tbody>
						@php($totalExpense=0)
						@foreach ($expense as $e)
							@php($val = (float)($e->debit_sum ?? 0) - (float)($e->credit_sum ?? 0))
							<tr><td>{{ $e->code }} - {{ $e->name }}</td><td>{{ number_format($val,2) }}</td></tr>
							@php($totalExpense += $val)
						@endforeach
					</tbody>
					<tfoot><tr><th>Total Beban</th><th>{{ number_format($totalExpense,2) }}</th></tr></tfoot>
				</table>
			</div>
		</div>

		@php($netIncome = $totalRevenue - $totalExpense)
		<div class="alert alert-info">Laba/Rugi Bersih: <strong>{{ number_format($netIncome,2) }}</strong></div>
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
          report_name: 'Income Statement',
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


