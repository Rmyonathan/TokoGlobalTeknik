@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Pembayaran Piutang</div>
	<div class="card-body">
		@if(isset($summary))
		<div class="mb-3">
			<strong>Total Faktur:</strong> {{ number_format($summary['total_faktur'] ?? 0) }} |
			<strong>Total Nilai Faktur:</strong> Rp {{ number_format($summary['total_nilai_faktur'] ?? 0, 0, ',', '.') }} |
			<strong>Total Sudah Dibayar:</strong> Rp {{ number_format($summary['total_sudah_dibayar'] ?? 0, 0, ',', '.') }} |
			<strong>Total Sisa Piutang:</strong> Rp {{ number_format($summary['total_sisa_piutang'] ?? 0, 0, ',', '.') }}
		</div>
		@endif

		<form method="GET" action="{{ route('pembayaran-piutang.laporan') }}" class="mb-3">
			<div class="row">
				<div class="col-md-3">
					<label>Start Date</label>
					<input type="date" name="start_date" value="{{ request('start_date') ?? $startDate->format('Y-m-d') }}" class="form-control">
				</div>
				<div class="col-md-3">
					<label>End Date</label>
					<input type="date" name="end_date" value="{{ request('end_date') ?? $endDate->format('Y-m-d') }}" class="form-control">
				</div>
				<div class="col-md-3">
					<label>Customer</label>
					<select name="kode_customer" class="form-control">
						<option value="">-- Semua Customer --</option>
						@foreach($customers as $c)
							<option value="{{ $c->kode_customer }}" {{ (request('kode_customer') == $c->kode_customer) ? 'selected' : '' }}>
								{{ $c->nama }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					<label>Status Piutang</label>
					<select name="status_piutang" class="form-control">
						<option value="">-- Semua --</option>
						<option value="lunas" {{ request('status_piutang') == 'lunas' ? 'selected' : '' }}>Lunas</option>
						<option value="sebagian" {{ request('status_piutang') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
						<option value="belum_dibayar" {{ request('status_piutang') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
					</select>
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-12">
					<button type="submit" class="btn btn-primary">Filter</button>
					<a href="{{ route('pembayaran-piutang.laporan') }}" class="btn btn-secondary">Reset</a>
				</div>
			</div>
		</form>

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>No Faktur</th>
						<th>Tanggal</th>
						<th>Customer</th>
						<th>Total</th>
						<th>Sudah Dibayar</th>
						<th>Sisa</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($transaksi ?? []) as $t)
					<tr>
						<td>{{ $t->no_transaksi }}</td>
						<td>{{ optional($t->tanggal)->format('d/m/Y') }}</td>
						<td>{{ optional($t->customer)->nama ?? '-' }}</td>
						<td class="text-right">{{ number_format($t->grand_total, 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($t->total_dibayar, 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($t->sisa_piutang, 0, ',', '.') }}</td>
						<td>{{ $t->status_piutang }}</td>
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


