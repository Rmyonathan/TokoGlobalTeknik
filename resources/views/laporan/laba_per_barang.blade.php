@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Laba per Barang</div>
	<div class="card-body">
		<form class="form-inline mb-3" method="get">
			<input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date', isset($startDate) ? \Illuminate\Support\Carbon::parse($startDate)->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}">
			<input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date', isset($endDate) ? \Illuminate\Support\Carbon::parse($endDate)->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}">
			<button class="btn btn-primary" type="submit">Filter</button>
		</form>

		@if(isset($grandSummary))
		<div class="mb-3">
			<strong>Jenis Barang:</strong> {{ number_format($grandSummary['total_jenis_barang']) }} |
			<strong>Grand Omset:</strong> Rp {{ number_format($grandSummary['grand_total_omset'], 0, ',', '.') }} |
			<strong>Grand Modal:</strong> Rp {{ number_format($grandSummary['grand_total_modal'], 0, ',', '.') }} |
			<strong>Grand Laba Bersih:</strong> Rp {{ number_format($grandSummary['grand_total_laba_bersih'], 0, ',', '.') }}
		</div>
		@endif

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>Kode Barang</th>
						<th>Nama</th>
						<th>Total Qty</th>
						<th>Total Omset</th>
						<th>Total Modal</th>
						<th>Total Ongkos</th>
						<th>Total Laba Bersih</th>
						<th>Margin Bersih</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($laporanData ?? []) as $row)
					<tr>
						<td>{{ $row['kode_barang'] }}</td>
						<td>{{ $row['nama_barang'] }}</td>
						<td class="text-right">{{ number_format($row['total_qty'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['total_omset'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['total_modal'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['total_ongkos_kuli'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['total_laba_bersih'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['margin_bersih'], 2) }}%</td>
					</tr>
					@empty
					<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection


