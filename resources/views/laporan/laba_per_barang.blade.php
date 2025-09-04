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
						<th>Detail Faktur</th>
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
						<td>
							<button class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailModal{{ $loop->index }}">
								<i class="fas fa-eye"></i> Lihat Detail
							</button>
						</td>
					</tr>
					@empty
					<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal Detail Faktur -->
@foreach(($laporanData ?? []) as $index => $row)
<div class="modal fade" id="detailModal{{ $index }}" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel{{ $index }}" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="detailModalLabel{{ $index }}">
					Detail Faktur - {{ $row['nama_barang'] }} ({{ $row['kode_barang'] }})
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-md-3">
						<strong>Total Qty:</strong> {{ number_format($row['total_qty'], 0, ',', '.') }}
					</div>
					<div class="col-md-3">
						<strong>Total Omset:</strong> Rp {{ number_format($row['total_omset'], 0, ',', '.') }}
					</div>
					<div class="col-md-3">
						<strong>Total Modal:</strong> Rp {{ number_format($row['total_modal'], 0, ',', '.') }}
					</div>
					<div class="col-md-3">
						<strong>Total Laba Bersih:</strong> Rp {{ number_format($row['total_laba_bersih'], 0, ',', '.') }}
					</div>
				</div>
				
				<div class="table-responsive">
					<table class="table table-bordered table-sm">
						<thead class="thead-light">
							<tr>
								<th>No Faktur</th>
								<th>Tanggal</th>
								<th>Customer</th>
								<th class="text-right">Qty</th>
								<th class="text-right">Harga</th>
								<th class="text-right">Subtotal</th>
								<th class="text-right">Ongkos Kuli</th>
								<th class="text-right">Modal</th>
								<th class="text-right">Laba Bersih</th>
								<th class="text-right">Margin</th>
							</tr>
						</thead>
						<tbody>
							@foreach($row['detail_transaksi'] as $transaksi)
							<tr>
								<td>{{ $transaksi['no_transaksi'] }}</td>
								<td>{{ $transaksi['tanggal'] }}</td>
								<td>{{ $transaksi['customer'] }}</td>
								<td class="text-right">{{ number_format($transaksi['qty'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($transaksi['omset'] / $transaksi['qty'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($transaksi['omset'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($transaksi['ongkos_kuli'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($transaksi['modal'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($transaksi['laba_bersih'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($transaksi['margin_bersih'], 2) }}%</td>
							</tr>
							@endforeach
						</tbody>
						<tfoot class="thead-light">
							<tr>
								<th colspan="3">TOTAL</th>
								<th class="text-right">{{ number_format($row['total_qty'], 0, ',', '.') }}</th>
								<th></th>
								<th class="text-right">{{ number_format($row['total_omset'], 0, ',', '.') }}</th>
								<th class="text-right">{{ number_format($row['total_ongkos_kuli'], 0, ',', '.') }}</th>
								<th class="text-right">{{ number_format($row['total_modal'], 0, ',', '.') }}</th>
								<th class="text-right">{{ number_format($row['total_laba_bersih'], 0, ',', '.') }}</th>
								<th class="text-right">{{ number_format($row['margin_bersih'], 2) }}%</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>
@endforeach

@endsection


