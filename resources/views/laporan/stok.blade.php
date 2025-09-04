@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Stok {{ request('show_batches') ? '(per Batch)' : '' }}</div>
	<div class="card-body">
		<!-- Filter Form -->
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-filter mr-2"></i>Filter
			</div>
			<div class="card-body">
				<form method="GET" action="{{ route('laporan.stok') }}">
					<div class="row">
						<div class="col-md-3 mb-2">
							<label>Kode Barang</label>
							<input type="text" name="kode_barang" class="form-control" 
								placeholder="Filter kode barang..." 
								value="{{ $kodeBarang ?? '' }}">
						</div>
						<div class="col-md-3 mb-2">
							<label>Nama Barang</label>
							<input type="text" name="nama_barang" class="form-control" 
								placeholder="Filter nama barang..." 
								value="{{ $namaBarang ?? '' }}">
						</div>
						<div class="col-md-3 mb-2">
							<label>Grup Barang</label>
							<select name="grup_barang" class="form-control">
								<option value="">-- Semua Grup --</option>
								@php $grupList = \App\Models\GrupBarang::orderBy('name')->get(); @endphp
								@foreach($grupList as $g)
									<option value="{{ $g->id }}" {{ ($grupBarangId ?? '') == $g->id ? 'selected' : '' }}>
										{{ $g->name }}
									</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 mb-2">
							<label>&nbsp;</label>
							<div class="btn-group d-block">
								<button type="submit" class="btn btn-primary">
									<i class="fas fa-search mr-1"></i> Filter
								</button>
								<a href="{{ route('laporan.stok') }}" class="btn btn-secondary">
									<i class="fas fa-sync-alt mr-1"></i> Reset
								</a>
							</div>
						</div>
					</div>
					<!-- Hidden fields to preserve show_batches and show_pergerakan -->
					@if(request('show_batches'))
						<input type="hidden" name="show_batches" value="1">
					@endif
					@if(request('show_pergerakan'))
						<input type="hidden" name="show_pergerakan" value="1">
					@endif
				</form>
			</div>
		</div>

		<!-- Filter khusus untuk Pergerakan Barang -->
		@if($showPergerakan)
		<div class="card mb-4">
			<div class="card-header">
				<i class="fas fa-calendar-alt mr-2"></i>Filter Pergerakan Barang
			</div>
			<div class="card-body">
				<form method="GET" action="{{ route('laporan.stok') }}">
					<div class="row">
						<div class="col-md-3 mb-2">
							<label>Tanggal Pergerakan</label>
							<input type="date" name="tanggal_pergerakan" class="form-control" 
								value="{{ $tanggalPergerakan ?? now()->format('Y-m-d') }}" required>
						</div>
						<div class="col-md-3 mb-2">
							<label>Jenis Pergerakan</label>
							<select name="jenis_pergerakan" class="form-control">
								<option value="semua" {{ ($jenisPergerakan ?? '') == 'semua' ? 'selected' : '' }}>Semua</option>
								<option value="masuk" {{ ($jenisPergerakan ?? '') == 'masuk' ? 'selected' : '' }}>Stok Masuk</option>
								<option value="keluar" {{ ($jenisPergerakan ?? '') == 'keluar' ? 'selected' : '' }}>Stok Keluar</option>
							</select>
						</div>
						<div class="col-md-3 mb-2">
							<label>&nbsp;</label>
							<div class="btn-group d-block">
								<button type="submit" class="btn btn-primary">
									<i class="fas fa-search mr-1"></i> Filter
								</button>
								<a href="{{ route('laporan.stok', ['show_pergerakan' => 1]) }}" class="btn btn-secondary">
									<i class="fas fa-sync-alt mr-1"></i> Reset
								</a>
							</div>
						</div>
					</div>
					<!-- Preserve other filters -->
					<input type="hidden" name="show_pergerakan" value="1">
					@if($kodeBarang)
						<input type="hidden" name="kode_barang" value="{{ $kodeBarang }}">
					@endif
					@if($namaBarang)
						<input type="hidden" name="nama_barang" value="{{ $namaBarang }}">
					@endif
					@if($grupBarangId)
						<input type="hidden" name="grup_barang" value="{{ $grupBarangId }}">
					@endif
				</form>
			</div>
		</div>
		@endif

		<div class="mb-3">
			<div class="btn-group" role="group">
				<a href="{{ route('laporan.stok', array_merge(request()->query(), ['show_batches' => null, 'show_pergerakan' => null])) }}" 
					class="btn btn-sm {{ !$showBatches && !$showPergerakan ? 'btn-primary' : 'btn-secondary' }}">
					<i class="fas fa-warehouse mr-1"></i> Ringkas per Barang
				</a>
				<a href="{{ route('laporan.stok', array_merge(request()->query(), ['show_batches' => 1, 'show_pergerakan' => null])) }}" 
					class="btn btn-sm {{ $showBatches && !$showPergerakan ? 'btn-primary' : 'btn-secondary' }}">
					<i class="fas fa-layer-group mr-1"></i> Detail per Batch
				</a>
				<a href="{{ route('laporan.stok', array_merge(request()->query(), ['show_batches' => null, 'show_pergerakan' => 1])) }}" 
					class="btn btn-sm {{ $showPergerakan ? 'btn-primary' : 'btn-secondary' }}">
					<i class="fas fa-exchange-alt mr-1"></i> Pergerakan Barang
				</a>
			</div>
		</div>

		@if(isset($summary))
		<div class="mb-3">
			@if($showPergerakan)
				<!-- Summary untuk Pergerakan Barang -->
				<div class="alert alert-info">
					<div class="row">
						<div class="col-md-3">
							<strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($summary['tanggal'])->format('d/m/Y') }}
						</div>
						<div class="col-md-3">
							<strong>Total Transaksi:</strong> {{ number_format($summary['total_transaksi']) }}
						</div>
						<div class="col-md-3">
							<strong>Stok Masuk:</strong> {{ number_format($summary['total_masuk']) }} transaksi
						</div>
						<div class="col-md-3">
							<strong>Stok Keluar:</strong> {{ number_format($summary['total_keluar']) }} transaksi
						</div>
					</div>
					<div class="row mt-2">
						<div class="col-md-3">
							<strong>Qty Masuk:</strong> {{ number_format($summary['total_qty_masuk'], 0, ',', '.') }}
						</div>
						<div class="col-md-3">
							<strong>Qty Keluar:</strong> {{ number_format($summary['total_qty_keluar'], 0, ',', '.') }}
						</div>
						<div class="col-md-3">
							<strong>Selisih:</strong> 
							<span class="{{ $summary['selisih_qty'] >= 0 ? 'text-success' : 'text-danger' }}">
								{{ number_format($summary['selisih_qty'], 0, ',', '.') }}
							</span>
						</div>
						<div class="col-md-3">
							<strong>Jenis Barang:</strong> {{ number_format($summary['jenis_barang_terlibat']) }}
						</div>
					</div>
				</div>
			@else
				<!-- Summary untuk Laporan Stok -->
				<strong>Jenis Barang:</strong> {{ number_format($summary['total_jenis_barang']) }} |
				<strong>Total Batch:</strong> {{ number_format($summary['total_batch']) }} |
				<strong>Grand Total Qty:</strong> {{ number_format($summary['grand_total_qty'], 0, ',', '.') }} |
				<strong>Grand Total Nilai:</strong> Rp {{ number_format($summary['grand_total_nilai'], 0, ',', '.') }}
			@endif
		</div>
		@endif

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						@if($showPergerakan)
							<th>Waktu</th>
							<th>Kode Barang</th>
							<th>Nama Barang</th>
							<th>No Transaksi</th>
							<th>No Nota</th>
							<th>Supplier/Customer</th>
							<th>Jenis</th>
							<th class="text-right">Qty Masuk</th>
							<th class="text-right">Qty Keluar</th>
							<th>Satuan</th>
							<th>Keterangan</th>
							<th>Created By</th>
						@elseif(request('show_batches'))
							<th>Kode Barang</th>
							<th>Nama</th>
							<th>Batch</th>
							<th>Tgl Masuk</th>
							<th>Qty Masuk</th>
							<th>Qty Sisa</th>
							<th>Harga Beli</th>
							<th>Nilai Sisa</th>
							<th>Action</th>
						@else
							<th>Kode Barang</th>
							<th>Nama</th>
							<th>Attribute</th>
							<th>Total Qty</th>
							<th>Jumlah Batch</th>
							<th>Total Nilai</th>
							<th>Rata Harga</th>
							<th>Action</th>
						@endif
					</tr>
				</thead>
				<tbody>
					@if($showPergerakan)
						@forelse(($pergerakanData ?? []) as $row)
							<tr class="{{ $row['jenis_pergerakan'] == 'MASUK' ? 'table-success' : 'table-danger' }}">
								<td>{{ $row['waktu'] }}</td>
								<td>{{ $row['kode_barang'] }}</td>
								<td>{{ $row['nama_barang'] }}</td>
								<td>{{ $row['no_transaksi'] }}</td>
								<td>{{ $row['no_nota'] ?: '-' }}</td>
								<td>{{ $row['supplier_customer'] }}</td>
								<td>
									<span class="badge {{ $row['jenis_pergerakan'] == 'MASUK' ? 'badge-success' : 'badge-danger' }}">
										{{ $row['jenis_pergerakan'] }}
									</span>
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
								<td>{{ $row['satuan'] }}</td>
								<td>{{ $row['keterangan'] ?: '-' }}</td>
								<td>{{ $row['created_by'] ?: '-' }}</td>
							</tr>
						@empty
							<tr>
								<td colspan="12" class="text-center">
									<div class="py-4">
										<i class="fas fa-inbox fa-3x text-muted mb-3"></i>
										<p class="text-muted">Tidak ada pergerakan barang pada tanggal {{ \Carbon\Carbon::parse($tanggalPergerakan ?? now())->format('d/m/Y') }}</p>
									</div>
								</td>
							</tr>
						@endforelse
					@else
						@forelse(($laporanData ?? []) as $row)
							<tr>
								@if(request('show_batches'))
									<td>{{ $row['kode_barang'] }}</td>
									<td>{{ $row['nama_barang'] }}</td>
									<td>#{{ $row['batch_id'] }}</td>
									<td>{{ $row['tanggal_masuk'] }}</td>
									<td class="text-right">{{ number_format($row['qty_masuk'], 0, ',', '.') }}</td>
									<td class="text-right">{{ number_format($row['qty_sisa'], 0, ',', '.') }}</td>
									<td class="text-right">{{ number_format($row['harga_beli'], 0, ',', '.') }}</td>
									<td class="text-right">{{ number_format($row['total_nilai_sisa'], 0, ',', '.') }}</td>
									<td>
										<a href="{{ $row['action_pergerakan'] }}" class="btn btn-sm btn-info" title="Lihat Pergerakan">
											<i class="fas fa-chart-line"></i>
										</a>
									</td>
								@else
									<td>{{ $row['kode_barang'] }}</td>
									<td>{{ $row['nama_barang'] }}</td>
									<td>{{ $row['attribute'] }}</td>
									<td class="text-right">{{ number_format($row['total_qty_sisa'], 0, ',', '.') }}</td>
									<td class="text-right">{{ number_format($row['jumlah_batch'], 0, ',', '.') }}</td>
									<td class="text-right">{{ number_format($row['total_nilai_stok'], 0, ',', '.') }}</td>
									<td class="text-right">{{ number_format($row['rata_harga_beli'], 0, ',', '.') }}</td>
									<td>
										<a href="{{ $row['action_pergerakan'] }}" class="btn btn-sm btn-info" title="Lihat Pergerakan">
											<i class="fas fa-chart-line"></i>
										</a>
									</td>
								@endif
							</tr>
						@empty
							<tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
						@endforelse
					@endif
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection


