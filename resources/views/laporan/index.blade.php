@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Laporan</div>
	<div class="card-body">
		<div class="row">
			<div class="col-md-6">
				<h5>Total Penjualan (Bulan ini)</h5>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Jumlah Faktur</th>
							<th>Total Omset</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{ \App\Models\Transaksi::where('status','!=','canceled')->whereBetween('tanggal',[now()->startOfMonth(), now()->endOfMonth()])->count() }}</td>
							<td>{{ number_format(\App\Models\Transaksi::where('status','!=','canceled')->whereBetween('tanggal',[now()->startOfMonth(), now()->endOfMonth()])->sum('grand_total')) }}</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="col-md-6">
				<h5>Total Retur Penjualan (Bulan ini)</h5>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Jumlah Retur</th>
							<th>Total Nilai Retur</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{ \App\Models\ReturPenjualan::whereBetween('tanggal',[now()->startOfMonth(), now()->endOfMonth()])->count() }}</td>
							<td>{{ number_format(\App\Models\ReturPenjualan::whereBetween('tanggal',[now()->startOfMonth(), now()->endOfMonth()])->sum('total_retur')) }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="list-group mt-3">
			<a href="{{ route('laporan.penjualan-per-hari') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-chart-line mr-2"></i>Laporan Penjualan per Hari
			</a>
			<a href="{{ route('laporan.cogs') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-calculator mr-2"></i>Laporan COGS/HPP
			</a>
			<a href="{{ route('laporan.penjualan-dan-retur') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-exchange-alt mr-2"></i>Laporan Penjualan dan Retur
			</a>
			<a href="{{ route('laporan.laba-per-faktur') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-file-invoice mr-2"></i>Laporan Laba per Faktur
			</a>
			<a href="{{ route('laporan.laba-per-barang') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-boxes mr-2"></i>Laporan Laba per Barang
			</a>
			<a href="{{ route('laporan.ongkos-kuli') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-tools mr-2"></i>Laporan Ongkos Kuli
			</a>
			<a href="{{ route('laporan.komisi-sales') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-user-tie mr-2"></i>Laporan Komisi Sales
			</a>
			<a href="{{ route('laporan.stok') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-warehouse mr-2"></i>Laporan Stok
			</a>
			<a href="{{ route('laporan.piutang') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-hand-holding-usd mr-2"></i>Laporan Piutang Pelanggan
			</a>
			<a href="{{ route('laporan.utang-supplier') }}" class="list-group-item list-group-item-action">
				<i class="fas fa-truck mr-2"></i>Laporan Utang Supplier
			</a>
		</div>
	</div>
</div>
@endsection


