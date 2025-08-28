@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Piutang Pelanggan</div>
	<div class="card-body">
		@if(isset($summary))
		<div class="mb-3">
			<strong>Total Customer:</strong> {{ number_format($summary['total_customer']) }} |
			<strong>Total Faktur:</strong> {{ number_format($summary['total_faktur']) }} |
			<strong>Grand Sisa Piutang:</strong> Rp {{ number_format($summary['grand_sisa_piutang'], 0, ',', '.') }}
		</div>
		@endif
		<form method="GET" action="{{ route('laporan.piutang') }}" class="mb-3">
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
					<select name="customer_id" class="form-control">
						<option value="">-- Semua Customer --</option>
						@foreach($custList as $c)
							<option value="{{ $c->kode_customer }}" 
								{{ request('customer_id') == $c->kode_customer ? 'selected' : '' }}>
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
				<div class="col-md-2 d-flex align-items-end">
					<button type="submit" class="btn btn-primary me-2">Filter</button>
					<a href="{{ route('laporan.piutang') }}" class="btn btn-secondary">Reset</a>
				</div>
			</div>
		</form>

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>No Faktur</th>
						<th>Tanggal</th>
						<th>Jatuh Tempo</th>
						<th>Customer</th>
						<th>Total</th>
						<th>Sudah Dibayar</th>
						<th>Sisa Piutang</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($laporanData ?? []) as $row)
					<tr>
						<td>{{ $row['no_transaksi'] }}</td>
						<td>{{ $row['tanggal'] }}</td>
						<td>{{ $row['tanggal_jatuh_tempo'] }}</td>
						<td>{{ $row['customer'] }}</td>
						<td class="text-right">{{ number_format($row['total_faktur'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['total_dibayar'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['sisa_piutang'], 0, ',', '.') }}</td>
						<td>{{ $row['status_piutang'] }}</td>
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


