@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">
		<div class="d-flex justify-content-between align-items-center">
			<div>
				<i class="fas fa-chart-line mr-2"></i>Detail Pergerakan Barang
			</div>
			<a href="{{ route('laporan.stok') }}" class="btn btn-sm btn-secondary">
				<i class="fas fa-arrow-left mr-1"></i> Kembali ke Laporan Stok
			</a>
		</div>
	</div>
	<div class="card-body">
		<!-- Info Barang -->
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-box mr-2"></i>Informasi Barang
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-3">
						<strong>Kode Barang:</strong><br>
						<span class="text-primary">{{ $summary['kode_barang'] }}</span>
					</div>
					<div class="col-md-4">
						<strong>Nama Barang:</strong><br>
						{{ $summary['nama_barang'] }}
					</div>
					<div class="col-md-3">
						<strong>Attribute:</strong><br>
						{{ $summary['attribute'] ?? '-' }}
					</div>
					<div class="col-md-2">
						<strong>Periode:</strong><br>
						{{ \Carbon\Carbon::parse($summary['periode']['start_date'])->format('d/m/Y') }} - 
						{{ \Carbon\Carbon::parse($summary['periode']['end_date'])->format('d/m/Y') }}
					</div>
				</div>
			</div>
		</div>

		<!-- Filter Form -->
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-filter mr-2"></i>Filter
			</div>
			<div class="card-body">
				<form method="GET" action="{{ route('laporan.stok.pergerakan', $summary['kode_barang']) }}">
					<div class="row">
						<div class="col-md-3 mb-2">
							<label>Tanggal Mulai</label>
							<input type="date" name="start_date" class="form-control" 
								value="{{ $startDate }}">
						</div>
						<div class="col-md-3 mb-2">
							<label>Tanggal Akhir</label>
							<input type="date" name="end_date" class="form-control" 
								value="{{ $endDate }}">
						</div>
						<div class="col-md-3 mb-2">
							<label>Jenis Pergerakan</label>
							<select name="jenis_pergerakan" class="form-control">
								<option value="">-- Semua --</option>
								<option value="masuk" {{ $jenisPergerakan == 'masuk' ? 'selected' : '' }}>Masuk</option>
								<option value="keluar" {{ $jenisPergerakan == 'keluar' ? 'selected' : '' }}>Keluar</option>
							</select>
						</div>
						<div class="col-md-3 mb-2">
							<label>&nbsp;</label>
							<div class="btn-group d-block">
								<button type="submit" class="btn btn-primary">
									<i class="fas fa-search mr-1"></i> Filter
								</button>
								<a href="{{ route('laporan.stok.pergerakan', $summary['kode_barang']) }}" class="btn btn-secondary">
									<i class="fas fa-sync-alt mr-1"></i> Reset
								</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>

		<!-- Summary -->
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-chart-bar mr-2"></i>Ringkasan Pergerakan
			</div>
			<div class="card-body">
				<div class="row text-center">
					<div class="col-md-2">
						<div class="border rounded p-3">
							<h5 class="text-primary">{{ number_format($summary['total_transaksi']) }}</h5>
							<small class="text-muted">Total Transaksi</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="border rounded p-3">
							<h5 class="text-success">{{ number_format($summary['total_masuk']) }}</h5>
							<small class="text-muted">Transaksi Masuk</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="border rounded p-3">
							<h5 class="text-danger">{{ number_format($summary['total_keluar']) }}</h5>
							<small class="text-muted">Transaksi Keluar</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="border rounded p-3">
							<h5 class="text-info">{{ number_format($summary['total_qty_masuk'], 0, ',', '.') }}</h5>
							<small class="text-muted">Qty Masuk</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="border rounded p-3">
							<h5 class="text-warning">{{ number_format($summary['total_qty_keluar'], 0, ',', '.') }}</h5>
							<small class="text-muted">Qty Keluar</small>
						</div>
					</div>
					<div class="col-md-2">
						<div class="border rounded p-3">
							<h5 class="{{ $summary['selisih_qty'] >= 0 ? 'text-success' : 'text-danger' }}">
								{{ number_format($summary['selisih_qty'], 0, ',', '.') }}
							</h5>
							<small class="text-muted">Selisih Qty</small>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Data Pergerakan -->
		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead class="thead-light">
					<tr>
						<th>Tanggal</th>
						<th>Waktu</th>
						<th>No Transaksi</th>
						<th>No Nota</th>
						<th>Supplier/Customer</th>
						<th>Jenis</th>
						<th class="text-right">Qty Masuk</th>
						<th class="text-right">Qty Keluar</th>
						<th>Satuan</th>
						<th>Keterangan</th>
						<th>Created By</th>
					</tr>
				</thead>
				<tbody>
					@forelse($pergerakanData as $row)
						<tr>
							<td>{{ $row['tanggal'] }}</td>
							<td>{{ $row['waktu'] }}</td>
							<td>{{ $row['no_transaksi'] }}</td>
							<td>{{ $row['no_nota'] ?? '-' }}</td>
							<td>{{ $row['supplier_customer'] ?? '-' }}</td>
							<td>
								@if($row['jenis_pergerakan'] == 'MASUK')
									<span class="badge badge-success">{{ $row['jenis_pergerakan'] }}</span>
								@else
									<span class="badge badge-danger">{{ $row['jenis_pergerakan'] }}</span>
								@endif
							</td>
							<td class="text-right">
								@if($row['qty_masuk'] > 0)
									<span class="text-success">{{ number_format($row['qty_masuk'], 0, ',', '.') }}</span>
								@else
									-
								@endif
							</td>
							<td class="text-right">
								@if($row['qty_keluar'] > 0)
									<span class="text-danger">{{ number_format($row['qty_keluar'], 0, ',', '.') }}</span>
								@else
									-
								@endif
							</td>
							<td>{{ $row['satuan'] ?? '-' }}</td>
							<td>{{ $row['keterangan'] ?? '-' }}</td>
							<td>{{ $row['created_by'] ?? '-' }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="11" class="text-center text-muted">
								<i class="fas fa-inbox fa-2x mb-2"></i><br>
								Tidak ada data pergerakan untuk periode ini
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if($pergerakanData->count() > 0)
		<div class="mt-3">
			<small class="text-muted">
				<i class="fas fa-info-circle mr-1"></i>
				Menampilkan {{ $pergerakanData->count() }} transaksi pergerakan
			</small>
		</div>
		@endif
	</div>
</div>
@endsection
