@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Komisi Sales</div>
	<div class="card-body">
		<form class="form-inline mb-3" method="get">
			<input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date', isset($startDate) ? \Illuminate\Support\Carbon::parse($startDate)->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}">
			<input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date', isset($endDate) ? \Illuminate\Support\Carbon::parse($endDate)->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}">
			<button class="btn btn-primary" type="submit">Filter</button>
		</form>

		@if(isset($summary))
		<div class="mb-3">
			<strong>Total Sales:</strong> {{ number_format($summary['total_sales']) }} |
			<strong>Grand Omset:</strong> Rp {{ number_format($summary['grand_total_omset'], 0, ',', '.') }} |
			<strong>Grand Komisi:</strong> Rp {{ number_format($summary['grand_total_komisi'], 0, ',', '.') }}
		</div>
		@endif

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>Sales</th>
						<th>Jumlah Faktur</th>
						<th>Total Omset</th>
						<th>Komisi (0.4%)</th>
						<th>Rata Omset/Faktur</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($laporanData ?? []) as $row)
					<tr>
						<td>{{ $row['sales_nama'] }} ({{ $row['sales_code'] }})</td>
						<td class="text-right">{{ number_format($row['jumlah_faktur']) }}</td>
						<td class="text-right">{{ number_format($row['total_omset'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['komisi'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['rata_omset_per_faktur'], 0, ',', '.') }}</td>
					</tr>
					@empty
					<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection


