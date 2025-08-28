@extends('layout.Nav')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h3 class="card-title">Detail Sales Order</h3>
					<a href="{{ route('sales-order.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
				</div>
				<div class="card-body">
					<div class="row mb-3">
						<div class="col-md-3">
							<div class="text-muted small">No. SO</div>
							<div class="font-weight-bold">{{ $salesOrder->no_so }}</div>
						</div>
						<div class="col-md-3">
							<div class="text-muted small">Tanggal</div>
							<div class="font-weight-bold">{{ optional($salesOrder->tanggal)->format('d/m/Y') }}</div>
						</div>
						<div class="col-md-3">
							<div class="text-muted small">Customer</div>
							<div class="font-weight-bold">{{ optional($salesOrder->customer)->nama }}</div>
						</div>
						<div class="col-md-3">
							<div class="text-muted small">Salesman</div>
							<div class="font-weight-bold">{{ $salesOrder->nama_salesman }}</div>
						</div>
					</div>

					<div class="row mb-3">
						<div class="col-md-3">
							<div class="text-muted small">Cara Bayar</div>
							<div class="font-weight-bold">{{ $salesOrder->cara_bayar }}</div>
						</div>
						<div class="col-md-3">
							<div class="text-muted small">Hari Tempo</div>
							<div class="font-weight-bold">{{ $salesOrder->hari_tempo }}</div>
						</div>
						<div class="col-md-3">
							<div class="text-muted small">Estimasi Kirim</div>
							<div class="font-weight-bold">{{ optional($salesOrder->tanggal_estimasi)->format('d/m/Y') ?? '-' }}</div>
						</div>
						<div class="col-md-3">
							<div class="text-muted small">Status</div>
							<div>
								<span class="badge {{ $salesOrder->getStatusBadge() }}">{{ $salesOrder->getStatusText() }}</span>
							</div>
						</div>
					</div>

					<hr>

					<h5>Items</h5>
					<div class="table-responsive">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>Kode Barang</th>
									<th>Nama</th>
									<th>Qty</th>
									<th>Satuan</th>
									<th>Harga</th>
									<th>Total</th>
								</tr>
							</thead>
							<tbody>
								@foreach($salesOrder->items as $item)
								<tr>
									<td>{{ $item->kodeBarang->kode_barang }}</td>
									<td>{{ $item->kodeBarang->name }}</td>
									<td>{{ $item->qty }}</td>
									<td>{{ $item->satuan }}</td>
									<td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
									<td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<div class="row mt-3">
						<div class="col-md-4 offset-md-8">
							<table class="table table-borderless">
								<tr>
									<td><strong>Subtotal:</strong></td>
									<td class="text-right">Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
								</tr>
								<tr>
									<td><strong>Grand Total:</strong></td>
									<td class="text-right">Rp {{ number_format($salesOrder->grand_total, 0, ',', '.') }}</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection


