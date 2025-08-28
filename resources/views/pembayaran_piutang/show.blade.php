@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Detail Pembayaran Piutang</div>
	<div class="card-body">
		@if(isset($pembayaran))
			<div class="mb-3">
				<strong>No Pembayaran:</strong> {{ $pembayaran->no_pembayaran }}<br>
				<strong>Tanggal:</strong> {{ optional($pembayaran->tanggal_bayar)->format('d/m/Y') }}<br>
				<strong>Customer:</strong> {{ optional($pembayaran->customer)->nama ?? '-' }}<br>
				<strong>Total Bayar:</strong> Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}<br>
				<strong>Status:</strong> {{ $pembayaran->status }}
			</div>

			<h6>Rincian Pelunasan</h6>
			<div class="table-responsive">
				<table class="table table-bordered table-sm">
					<thead>
						<tr>
							<th>No Faktur</th>
							<th>Total</th>
							<th>Sudah Dibayar (sebelum)</th>
							<th>Dibayar</th>
							<th>Sisa</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						@forelse(($pembayaran->details ?? []) as $d)
							<tr>
								<td>{{ $d->no_transaksi }}</td>
								<td class="text-right">{{ number_format($d->total_faktur, 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($d->sudah_dibayar, 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($d->jumlah_dilunasi, 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($d->sisa_tagihan, 0, ',', '.') }}</td>
								<td>{{ $d->status_pelunasan }}</td>
							</tr>
						@empty
							<tr><td colspan="6" class="text-center">Tidak ada detail</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
		@endif

		<a href="{{ route('pembayaran-piutang.index') }}" class="btn btn-secondary mt-2">Kembali</a>
	</div>
</div>
@endsection


