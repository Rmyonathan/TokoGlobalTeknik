@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan Stok {{ request('show_batches') ? '(per Batch)' : '' }}</div>
	<div class="card-body">
		<div class="mb-3">
			<a href="{{ route('laporan.stok') }}" class="btn btn-sm btn-secondary">Ringkas per Barang</a>
			<a href="{{ route('laporan.stok', ['show_batches' => 1]) }}" class="btn btn-sm btn-primary">Detail per Batch</a>
		</div>

		@if(isset($summary))
		<div class="mb-3">
			<strong>Jenis Barang:</strong> {{ number_format($summary['total_jenis_barang']) }} |
			<strong>Total Batch:</strong> {{ number_format($summary['total_batch']) }} |
			<strong>Grand Total Qty:</strong> {{ number_format($summary['grand_total_qty'], 0, ',', '.') }} |
			<strong>Grand Total Nilai:</strong> Rp {{ number_format($summary['grand_total_nilai'], 0, ',', '.') }}
		</div>
		@endif

		<div class="table-responsive">
			<table class="table table-bordered table-sm">
				<thead>
					<tr>
						@if(request('show_batches'))
							<th>Kode Barang</th>
							<th>Nama</th>
							<th>Batch</th>
							<th>Tgl Masuk</th>
							<th>Qty Masuk</th>
							<th>Qty Sisa</th>
							<th>Harga Beli</th>
							<th>Nilai Sisa</th>
						@else
							<th>Kode Barang</th>
							<th>Nama</th>
							<th>Attribute</th>
							<th>Total Qty</th>
							<th>Jumlah Batch</th>
							<th>Total Nilai</th>
							<th>Rata Harga</th>
						@endif
					</tr>
				</thead>
				<tbody>
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
							@else
								<td>{{ $row['kode_barang'] }}</td>
								<td>{{ $row['nama_barang'] }}</td>
								<td>{{ $row['attribute'] }}</td>
								<td class="text-right">{{ number_format($row['total_qty_sisa'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($row['jumlah_batch'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($row['total_nilai_stok'], 0, ',', '.') }}</td>
								<td class="text-right">{{ number_format($row['rata_harga_beli'], 0, ',', '.') }}</td>
							@endif
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


