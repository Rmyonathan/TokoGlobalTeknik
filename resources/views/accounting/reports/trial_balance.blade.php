@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Neraca Saldo (Trial Balance)</div>
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
		<table class="table table-bordered table-sm" id="report-table">
			<thead>
				<tr>
					<th>Kode</th>
					<th>Nama Akun</th>
					<th>Debit</th>
					<th>Kredit</th>
				</tr>
			</thead>
			<tbody>
				@php($tD=0)
				@php($tC=0)
				@foreach ($rows as $r)
					@php($d = (float)($r->debit_sum ?? 0))
					@php($c = (float)($r->credit_sum ?? 0))
					<tr>
						<td>{{ $r->code }}</td>
						<td>{{ $r->name }}</td>
						<td>{{ number_format($d,2) }}</td>
						<td>{{ number_format($c,2) }}</td>
					</tr>
					@php($tD += $d)
					@php($tC += $c)
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th colspan="2">Total</th>
					<th>{{ number_format($tD,2) }}</th>
					<th>{{ number_format($tC,2) }}</th>
				</tr>
			</tfoot>
		</table>
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
          report_name: 'Trial Balance',
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


