@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Utang Supplier</div>
	<div class="card-body">
		@if(isset($summary))
		<div class="mb-3">
			<strong>Total Supplier:</strong> {{ number_format($summary['total_supplier']) }} |
			<strong>Total Faktur:</strong> {{ number_format($summary['total_faktur']) }} |
			<strong>Grand Sisa Utang:</strong> Rp {{ number_format($summary['grand_sisa_utang'], 0, ',', '.') }}
		</div>
		@endif
		<form method="GET" action="{{ route('laporan.utang-supplier') }}" class="mb-3">
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
					<label>Supplier</label>
					<select name="supplier_id" class="form-control">
						<option value="">-- Semua Supplier --</option>
						@foreach($supplierList as $s)
							<option value="{{ $s->kode_supplier }}" 
								{{ request('supplier_id') == $s->kode_supplier ? 'selected' : '' }}>
								{{ $s->nama }}
							</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					<label>Status Utang</label>
					<select name="status_utang" class="form-control">
						<option value="">-- Semua --</option>
						<option value="lunas" {{ request('status_utang') == 'lunas' ? 'selected' : '' }}>Lunas</option>
						<option value="sebagian" {{ request('status_utang') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
						<option value="belum_dibayar" {{ request('status_utang') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
					</select>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<button type="submit" class="btn btn-primary me-2">Filter</button>
					<a href="{{ route('laporan.utang-supplier') }}" class="btn btn-secondary">Reset</a>
				</div>
			</div>
		</form>

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						<th>No Nota</th>
						<th>Tanggal</th>
						<th>Jatuh Tempo</th>
						<th>Supplier</th>
						<th>Total</th>
						<th>Sudah Dibayar</th>
						<th>Sisa Utang</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($laporanData ?? []) as $row)
					<tr class="{{ $row['row_class'] ?? '' }}">
						<td>{{ $row['nota'] }}</td>
						<td>{{ $row['tanggal'] }}</td>
						<td>{{ $row['tanggal_jatuh_tempo'] }}</td>
						<td>{{ $row['supplier'] }}</td>
						<td class="text-right">{{ number_format($row['total_faktur'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['total_dibayar'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['sisa_utang'], 0, ',', '.') }}</td>
						<td>
							{{ $row['status_utang'] }}
							@if(($row['hari_keterlambatan'] ?? 0) > 0)
								<small class="text-muted">(Terlambat {{ $row['hari_keterlambatan'] }} hari)</small>
							@endif
						</td>
					</tr>
					@empty
					<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if(isset($groupBySupplier) && $groupBySupplier->count() > 0)
		<div class="mt-4">
			<h5>Rekap per Supplier</h5>
			<div class="table-responsive">
				<table class="table table-bordered table-sm">
					<thead>
						<tr>
							<th>Supplier</th>
							<th>Jumlah Faktur</th>
							<th>Total Faktur</th>
							<th>Total Dibayar</th>
							<th>Sisa Utang</th>
							<th>Faktur Jatuh Tempo</th>
							<th>Sisa Utang Jatuh Tempo</th>
						</tr>
					</thead>
					<tbody>
						@foreach($groupBySupplier as $supplier)
						<tr>
							<td>{{ $supplier['nama_supplier'] }}</td>
							<td class="text-center">{{ number_format($supplier['jumlah_faktur']) }}</td>
							<td class="text-right">{{ number_format($supplier['total_faktur'], 0, ',', '.') }}</td>
							<td class="text-right">{{ number_format($supplier['total_dibayar'], 0, ',', '.') }}</td>
							<td class="text-right">{{ number_format($supplier['total_sisa_utang'], 0, ',', '.') }}</td>
							<td class="text-center">{{ number_format($supplier['faktur_jatuh_tempo']) }}</td>
							<td class="text-right">{{ number_format($supplier['sisa_utang_jatuh_tempo'], 0, ',', '.') }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
		@endif
	</div>
</div>
@endsection
