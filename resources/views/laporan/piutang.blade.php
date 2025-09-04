@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Piutang Pelanggan</div>
	<div class="card-body">
		@if(isset($summary))
		<div class="mb-3">
			<div class="row">
				<div class="col-md-6">
					<strong>Total Customer:</strong> {{ number_format($summary['total_customer']) }} |
					<strong>Total Faktur:</strong> {{ number_format($summary['total_faktur']) }} |
					<strong>Grand Sisa Piutang:</strong> Rp {{ number_format($summary['grand_sisa_piutang'], 0, ',', '.') }}
				</div>
				<div class="col-md-6">
					<strong>Faktur Jatuh Tempo:</strong> {{ number_format($summary['faktur_jatuh_tempo']) }} |
					<strong>Sisa Piutang Jatuh Tempo:</strong> Rp {{ number_format($summary['sisa_piutang_jatuh_tempo'], 0, ',', '.') }}
				</div>
			</div>
			<div class="row mt-2">
				<div class="col-md-12">
					<small class="text-muted">
						<i class="fas fa-calendar-alt"></i> 
						<strong>Tanggal Hari Ini:</strong> {{ now()->format('d/m/Y') }} | 
						<strong>Perhitungan Keterlambatan:</strong> Otomatis berdasarkan tanggal jatuh tempo
					</small>
				</div>
			</div>
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
				<div class="col-md-2">
					<label>Status Piutang</label>
					<select name="status_piutang" class="form-control">
						<option value="">-- Semua --</option>
						<option value="lunas" {{ request('status_piutang') == 'lunas' ? 'selected' : '' }}>Lunas</option>
						<option value="sebagian" {{ request('status_piutang') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
						<option value="belum_dibayar" {{ request('status_piutang') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
					</select>
				</div>
				<div class="col-md-2">
					<label>Status Keterlambatan</label>
					<select name="status_keterlambatan" class="form-control">
						<option value="">-- Semua --</option>
						<option value="belum_jatuh_tempo" {{ request('status_keterlambatan') == 'belum_jatuh_tempo' ? 'selected' : '' }}>Belum Jatuh Tempo</option>
						<option value="1-15" {{ request('status_keterlambatan') == '1-15' ? 'selected' : '' }}>Terlambat 1-15 hari</option>
						<option value="16-30" {{ request('status_keterlambatan') == '16-30' ? 'selected' : '' }}>Terlambat 16-30 hari</option>
						<option value=">30" {{ request('status_keterlambatan') == '>30' ? 'selected' : '' }}>Terlambat >30 hari</option>
					</select>
				</div>
				<div class="col-md-2 d-flex align-items-end">
					<button type="submit" class="btn btn-primary me-2">Filter</button>
					<a href="{{ route('laporan.piutang') }}" class="btn btn-secondary">Reset</a>
				</div>
			</div>
		</form>

		<!-- Legend untuk pewarnaan status keterlambatan -->
		<div class="alert alert-info mb-3">
			<h6><i class="fas fa-info-circle"></i> Keterangan Status Keterlambatan:</h6>
			<div class="row">
				<div class="col-md-3">
					<span class="badge badge-info">Biru Muda</span> - Terlambat 1-15 hari (Perhatian)
				</div>
				<div class="col-md-3">
					<span class="badge badge-warning">Kuning</span> - Terlambat 16-30 hari (Bahaya)
				</div>
				<div class="col-md-3">
					<span class="badge badge-danger">Merah</span> - Terlambat >30 hari (Kritis)
				</div>
				<div class="col-md-3">
					<span class="badge badge-success">Hijau</span> - Tepat waktu atau lunas
				</div>
			</div>
		</div>

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
						<th>Retur</th>
						<th>Sisa Piutang</th>
						<th>Status Pembayaran</th>
						<th>Status Keterlambatan</th>
					</tr>
				</thead>
				<tbody>
					@forelse(($laporanData ?? []) as $row)
					<tr class="{{ $row['row_class'] ?? '' }}">
						<td>{{ $row['no_transaksi'] }}</td>
						<td>{{ $row['tanggal'] }}</td>
						<td>{{ $row['tanggal_jatuh_tempo'] }}</td>
						<td>{{ $row['customer'] }}</td>
						<td class="text-right">{{ number_format($row['total_faktur'], 0, ',', '.') }}</td>
						<td class="text-right">{{ number_format($row['total_dibayar'], 0, ',', '.') }}</td>
						<td class="text-right">
							@if(($row['total_retur'] ?? 0) > 0)
								<span class="text-danger">-{{ number_format($row['total_retur'], 0, ',', '.') }}</span>
							@else
								-
							@endif
						</td>
						<td class="text-right">{{ number_format($row['sisa_piutang'], 0, ',', '.') }}</td>
						<td>
							<span class="badge 
								@if($row['status_piutang'] == 'lunas') badge-success
								@elseif($row['status_piutang'] == 'sebagian') badge-warning
								@else badge-danger
								@endif">
								{{ ucfirst(str_replace('_', ' ', $row['status_piutang'])) }}
							</span>
						</td>
						<td>
							@if(($row['hari_keterlambatan'] ?? 0) > 0)
								<span class="badge 
									@if($row['status_warna'] == 'biru') badge-info
									@elseif($row['status_warna'] == 'kuning') badge-warning
									@elseif($row['status_warna'] == 'merah') badge-danger
									@else badge-secondary
									@endif">
									<i class="fas fa-clock"></i> {{ number_format($row['hari_keterlambatan'], 0) }} hari
								</span>
								<br><small class="text-muted">{{ $row['status_keterlambatan'] ?? '' }}</small>
							@elseif(($row['status_warna'] ?? '') == 'hijau')
								<span class="badge badge-success">
									<i class="fas fa-check-circle"></i> Tepat Waktu
								</span>
							@else
								<span class="badge badge-secondary">
									<i class="fas fa-calendar"></i> Belum Jatuh Tempo
								</span>
							@endif
						</td>
					</tr>
					@empty
					<tr><td colspan="10" class="text-center">Tidak ada data</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection


