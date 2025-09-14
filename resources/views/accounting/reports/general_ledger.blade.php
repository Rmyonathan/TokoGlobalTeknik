@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Buku Besar (General Ledger)</div>
	<div class="card-body">
		<form class="row g-2 mb-3" method="GET">
			<div class="col-md-4">
				<label class="form-label">Periode</label>
				<select name="period_id" class="form-select" required>
					<option value="">- Pilih Periode -</option>
					@foreach ($periods as $p)
						<option value="{{ $p->id }}" {{ ($periodId==$p->id)?'selected':'' }}>{{ $p->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-4">
				<label class="form-label">Akun</label>
				<select name="account_id" class="form-select" required>
					<option value="">- Pilih Akun -</option>
					@foreach ($accounts as $a)
						<option value="{{ $a->id }}" {{ ($accountId==$a->id)?'selected':'' }}>{{ $a->code }} - {{ $a->name }}</option>
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
					<th>Tanggal</th>
					<th>No Jurnal</th>
					<th>Referensi</th>
					<th>Deskripsi</th>
					<th>Debit</th>
					<th>Kredit</th>
				</tr>
			</thead>
			<tbody>
				@php($tD=0)
				@php($tC=0)
				@foreach ($entries as $e)
					<tr>
						<td>{{ optional($e->journal)->journal_date }}</td>
						<td>{{ optional($e->journal)->journal_no }}</td>
						<td>{{ optional($e->journal)->reference }}</td>
						<td>{{ optional($e->journal)->description }} | {{ $e->memo }}</td>
						<td>{{ number_format($e->debit,2) }}</td>
						<td>{{ number_format($e->credit,2) }}</td>
					</tr>
					@php($tD += $e->debit)
					@php($tC += $e->credit)
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th colspan="4">Total</th>
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
          report_name: 'General Ledger',
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


