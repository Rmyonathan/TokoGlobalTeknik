@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Laba per Faktur</div>
	<div class="card-body">
		<form class="form-inline mb-3" method="get">
			<input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date', isset($startDate) ? \Illuminate\Support\Carbon::parse($startDate)->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}">
			<input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date', isset($endDate) ? \Illuminate\Support\Carbon::parse($endDate)->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}">
			<button class="btn btn-primary" type="submit">Filter</button>
			@if(isset($laporanData) && count($laporanData) > 0)
			<a href="{{ route('laporan.laba-per-faktur.print', request()->query()) }}" class="btn btn-success ml-2" target="_blank">
				<i class="fas fa-print mr-1"></i> Print
			</a>
			@endif
		</form>

		@if(isset($summary))
		<div class="mb-3">
			<strong>Total Faktur:</strong> {{ number_format($summary['total_faktur']) }} |
			<strong>Total Omset:</strong> Rp {{ number_format($summary['total_omset'], 0, ',', '.') }} |
			<strong>Total Modal:</strong> Rp {{ number_format($summary['total_modal'], 0, ',', '.') }} |
			<strong>Laba Bersih:</strong> Rp {{ number_format($summary['total_laba_bersih'], 0, ',', '.') }}
		</div>
		@endif

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>No Faktur</th>
						<th>Tanggal</th>
						<th>Customer</th>
						<th>Omset</th>
						<th>Modal</th>
						<th>Ongkos Kuli</th>
						<th>Laba Bersih</th>
						<th>Margin Bersih</th>
						<th>Detail</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($laporanData ?? []) as $idx => $row)
					<tr>
						<td>{{ $row['no_transaksi'] }}</td>
						<td>{{ $row['tanggal'] }}</td>
						<td>{{ $row['customer'] }}</td>
						<td class="text-right">{{ number_format($row['omset'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['modal'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['ongkos_kuli'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['laba_bersih'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['margin_bersih'], 2) }}%</td>
						<td>
							<a class="btn btn-sm btn-outline-primary" data-toggle="collapse" href="#faktur{{ $idx }}" role="button" aria-expanded="false" aria-controls="faktur{{ $idx }}">
								Detail
							</a>
						</td>
					</tr>
					<tr class="collapse" id="faktur{{ $idx }}">
						<td colspan="9">
							<table class="table table-sm mb-0">
								<thead>
									<tr>
										<th>Barang</th>
										<th class="text-right">Harga Jual</th>
										<th class="text-right">Qty</th>
										<th class="text-right">Modal (FIFO)</th>
										<th class="text-right">Laba</th>
									</tr>
								</thead>
								<tbody>
									@foreach(($row['items'] ?? []) as $it)
									@php 
										$modalItem = 0; 
										foreach(($it['sumber'] ?? []) as $src){ $modalItem += ($src['qty_diambil'] ?? 0) * ($src['harga_beli'] ?? 0); }
										$labaItem = ($it['harga'] ?? 0) * ($it['qty'] ?? 0) - $modalItem;
									@endphp
									<tr>
										<td>{{ $it['nama_barang'] ?? ($it['kode_barang'] ?? '-') }}</td>
										<td class="text-right">{{ number_format($it['harga'] ?? 0, 0, ',', '.') }}</td>
										<td class="text-right">{{ number_format($it['qty'] ?? 0, 2, ',', '.') }}</td>
										<td class="text-right">{{ number_format($modalItem, 0, ',', '.') }}</td>
										<td class="text-right">{{ number_format($labaItem, 0, ',', '.') }}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</td>
					</tr>
					@empty
					<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
				@if(isset($summary))
				<tfoot>
					<tr class="font-weight-bold bg-light">
						<td colspan="3" class="text-center">TOTAL</td>
						<td class="text-right">{{ number_format($summary['total_omset'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($summary['total_modal'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($summary['total_ongkos_kuli'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($summary['total_laba_bersih'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($summary['margin_bersih_rata'], 2) }}%</td>
					</tr>
				</tfoot>
				@endif
			</table>
		</div>
	</div>
</div>
@endsection


