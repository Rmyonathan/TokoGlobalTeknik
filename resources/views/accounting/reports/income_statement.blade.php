@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Laba Rugi</div>
	<div class="card-body">
		<form class="row g-2 mb-3" method="GET">
			<div class="col-md-4">
				<label class="form-label">Dari Tanggal</label>
				<input type="date" name="from_date" class="form-control" id="from-date" value="{{ $fromDate ?? '' }}" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">Sampai Tanggal</label>
				<input type="date" name="to_date" class="form-control" id="to-date" value="{{ $toDate ?? '' }}" required>
			</div>
			<div class="col-md-4 d-flex align-items-end">
				<button class="btn btn-primary w-100">Tampilkan</button>
			</div>
		</form>

		<div class="d-flex justify-content-end mb-2">
			<button type="button" id="btn-save-report" class="btn btn-success btn-sm">Download CSV</button>
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
  const fromDate = document.getElementById('from-date');
  const toDate = document.getElementById('to-date');
  
  // Form validation
  document.querySelector('form').addEventListener('submit', function(e) {
    const fromDateValue = fromDate.value;
    const toDateValue = toDate.value;
    
    if (!fromDateValue || !toDateValue) {
      e.preventDefault();
      alert('Harap isi kedua tanggal');
      return false;
    }
    
    if (fromDateValue > toDateValue) {
      e.preventDefault();
      alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
      return false;
    }
  });
  
  const btn = document.getElementById('btn-save-report');
  if (!btn) return;
  btn.addEventListener('click', function(){
    const fromDateValue = fromDate.value;
    const toDateValue = toDate.value;
    
    if (!fromDateValue || !toDateValue) {
      alert('Harap isi kedua tanggal terlebih dahulu');
      return;
    }
    
    if (fromDateValue > toDateValue) {
      alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
      return;
    }
    
    // Redirect to CSV export
    const url = "{{ route('accounting.reports.income_statement.export') }}?from_date=" + fromDateValue + "&to_date=" + toDateValue;
    window.location.href = url;
  });
});
</script>
@endsection


