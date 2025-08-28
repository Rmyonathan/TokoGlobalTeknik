@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Ongkos Kuli</div>
	<div class="card-body">
		<form class="form-inline mb-3" method="get">
			<input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date', isset($startDate) ? \Illuminate\Support\Carbon::parse($startDate)->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}">
			<input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date', isset($endDate) ? \Illuminate\Support\Carbon::parse($endDate)->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}">
			<button class="btn btn-primary" type="submit">Filter</button>
		</form>

		@if(isset($summary))
		<div class="mb-3">
			<strong>Total Transaksi:</strong> {{ number_format($summary['total_transaksi']) }} |
			<strong>Total Ongkos Kuli:</strong> Rp {{ number_format($summary['total_ongkos_kuli'], 0, ',', '.') }} |
			<strong>Total Omset:</strong> Rp {{ number_format($summary['total_omset'], 0, ',', '.') }}
		</div>
		@endif

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>No Faktur</th>
						<th>Tanggal</th>
						<th>Customer</th>
						<th>Barang</th>
						<th>Qty</th>
						<th>Ongkos Kuli</th>
						<th>Subtotal</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($data ?? []) as $row)
					<tr>
						<td>{{ $row->no_transaksi }}</td>
						<td>{{ \Illuminate\Support\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
						<td>{{ $row->customer_nama }}</td>
						<td>{{ $row->kode_barang }} - {{ $row->nama_barang }}</td>
						<td class="text-right">{{ number_format($row->qty, 0, ',', '.') }} {{ $row->satuan }}</td>
						<td class="text-right">{{ number_format($row->ongkos_kuli, 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row->subtotal_item, 0, ',', '.') }}</td>
					</tr>
					@empty
					<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection


