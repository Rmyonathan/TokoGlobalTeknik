@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>Buku Besar (General Ledger)</span>
		@if(($entries ?? collect())->isNotEmpty() && ($accountId ?? null))
			<div>
				<a class="btn btn-sm btn-outline-secondary text-white" href="{{ route('accounting.reports.gl', array_merge(request()->query(), ['export' => 'csv'])) }}">Export CSV</a>
				<a class="btn btn-sm btn-outline-secondary " href="{{ route('accounting.reports.gl', array_merge(request()->query(), ['export' => 'pdf'])) }}">Export PDF</a>
			</div>
		@endif
	</div>
	<div class="card-body">
		<form class="row g-2 mb-3" method="GET">
			<div class="col-md-3">
				<label class="form-label">Dari Tanggal</label>
				<input type="date" name="from" class="form-control" value="{{ $from ?? request('from') }}" required>
			</div>
			<div class="col-md-3">
				<label class="form-label">Sampai Tanggal</label>
				<input type="date" name="to" class="form-control" value="{{ $to ?? request('to') }}" required>
			</div>
			<div class="col-md-4">
				<label class="form-label">Akun</label>
				<select name="account_id" class="form-select" required>
					<option value="">- Pilih Akun -</option>
					@foreach ($accounts as $a)
						<option value="{{ $a->id }}" {{ (isset($accountId) && $accountId==$a->id) ? 'selected' : '' }}>{{ $a->code }} - {{ $a->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Tampilkan</button></div>
		</form>

		<div class="d-flex justify-content-end mb-2">
			<small class="text-muted">Periode: {{ $from ?? '-' }} s/d {{ $to ?? '-' }}</small>
		</div>

		@if(($entries ?? collect())->isEmpty())
			<div class="alert alert-info">Silakan pilih tanggal dan akun untuk menampilkan buku besar.</div>
		@else
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>Tanggal</th>
						<th>No. Jurnal</th>
						<th>Referensi</th>
						<th>Keterangan</th>
						<th class="text-end">Debet</th>
						<th class="text-end">Kredit</th>
					</tr>
				</thead>
				<tbody>
					@php $totalD=0; $totalK=0; @endphp
					@foreach($entries as $e)
					<tr>
						<td>{{ optional($e->journal->journal_date)->format('Y-m-d') }}</td>
						<td>{{ $e->journal->journal_no }}</td>
						<td>{{ $e->journal->reference }}</td>
						<td>{{ $e->memo ?? $e->journal->description }}</td>
						<td class="text-end">@php $d=(float)$e->debit; echo number_format($d,0,',','.'); $totalD += $d; @endphp</td>
						<td class="text-end">@php $k=(float)$e->credit; echo number_format($k,0,',','.'); $totalK += $k; @endphp</td>
					</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4" class="text-end">Total</th>
						<th class="text-end">{{ number_format($totalD,0,',','.') }}</th>
						<th class="text-end">{{ number_format($totalK,0,',','.') }}</th>
					</tr>
				</tfoot>
			</table>
		@endif
	</div>
</div>
@endsection


