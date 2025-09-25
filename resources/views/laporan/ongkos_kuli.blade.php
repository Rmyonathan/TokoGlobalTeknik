@extends('layout.Nav')

@section('content')
<div class="card">
<div class="card-header">Laporan Expense</div>
	<div class="card-body">
		<form class="form-inline mb-3" method="get">
			<input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date', isset($startDate) ? \Illuminate\Support\Carbon::parse($startDate)->format('Y-m-d') : now()->startOfMonth()->format('Y-m-d')) }}">
			<input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date', isset($endDate) ? \Illuminate\Support\Carbon::parse($endDate)->format('Y-m-d') : now()->endOfMonth()->format('Y-m-d')) }}">
			<button class="btn btn-primary" type="submit">Filter</button>
			@if(isset($data) && $data->count() > 0)
			<a href="{{ route('laporan.ongkos-kuli.print', request()->query()) }}" class="btn btn-success ml-2" target="_blank">
				<i class="fas fa-print mr-1"></i> Print
			</a>
			@endif
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
						<td>{{ $row->customer_nama }}</td>
						<td>{{ $row->kode_barang }} - {{ $row->nama_barang }}</td>
						<td class="text-right">{{ number_format($row->qty, 0, ',', '.') }} {{ $row->satuan }}</td>
						<td class="text-right">{{ number_format($row->ongkos_kuli, 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row->subtotal_item, 0, ',', '.') }}</td>
					</tr>
					@empty
					<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>


		{{-- Rekap Per Nomor Faktur (detail per item dalam faktur) --}}
		@php
			$groupByFaktur = collect($data ?? [])->groupBy('no_transaksi');
		@endphp
		@if($groupByFaktur->count() > 0)
		<hr>
		<h5 class="mt-3">Rekap Per Nomor Faktur</h5>
		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>No Faktur</th>
						<th>Tanggal</th>
						<th>Customer</th>
						<th>Wilayah</th>
						<th class="text-right">Total Ongkos Kuli</th>
						<th class="text-right">Total Omset</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					@foreach($groupByFaktur as $noFaktur => $rows)
					@php 
						$collapseId = 'faktur-'.str_replace(['/', ' ', '#'], '-', $noFaktur);
						$tanggal = optional($rows->first()->tanggal) ? \Illuminate\Support\Carbon::parse($rows->first()->tanggal)->format('d/m/Y') : '-';
						$customerNama = $rows->first()->customer_nama ?? '-';
						$wilayahNama = $rows->first()->wilayah_nama ?? '-';
						$totalOngkos = $rows->sum('ongkos_kuli');
						$totalOmset = $rows->sum('subtotal_item');
					@endphp
					<tr>
						<td>{{ $noFaktur }}</td>
						<td>{{ $tanggal }}</td>
						<td>{{ $customerNama }}</td>
						<td>{{ $wilayahNama }}</td>
						<td class="text-right">{{ number_format($totalOngkos, 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($totalOmset, 0, ',', '.') }}</td>
						<td>
							<button class="btn btn-sm btn-info" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}">Detail</button>
						</td>
					</tr>
					<tr class="collapse" id="{{ $collapseId }}">
						<td colspan="7">
							<div class="table-responsive">
								<table class="table table-striped table-sm mb-0">
									<thead>
										<tr>
											<th>Barang</th>
											<th class="text-right">Qty</th>
											<th class="text-right">Ongkos Kuli</th>
											<th class="text-right">Subtotal</th>
										</tr>
									</thead>
									<tbody>
										@foreach($rows as $r)
										<tr>
											<td>{{ $r->kode_barang }} - {{ $r->nama_barang }}</td>
											<td class="text-right">{{ number_format($r->qty, 0, ',', '.') }} {{ $r->satuan }}</td>
											<td class="text-right">{{ number_format($r->ongkos_kuli, 0, ',', '.') }}</td>
											<td class="text-right">{{ number_format($r->subtotal_item, 0, ',', '.') }}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		@endif
	</div>
</div>
@endsection


