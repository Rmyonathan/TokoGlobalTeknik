@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>Tutup Buku Tahunan</span>
		<a href="{{ route('accounting.year-end.create') }}" class="btn btn-primary btn-sm">Tutup Buku</a>
	</div>
	<div class="card-body">
		@if(session('success'))
			<div class="alert alert-success">{{ session('success') }}</div>
		@endif
		@if(session('error'))
			<div class="alert alert-danger">{{ session('error') }}</div>
		@endif
		<table class="table table-bordered table-sm">
			<thead>
				<tr>
					<th>Tahun Fiskal</th>
					<th>Status</th>
					<th>Tanggal Tutup</th>
					<th>Ditutup Oleh</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($closings as $c)
				<tr>
					<td>{{ $c->fiscal_year }}</td>
					<td>{{ $c->status }}</td>
					<td>{{ $c->closed_on }}</td>
					<td>{{ $c->closed_by }}</td>
					<td><a href="{{ route('accounting.year-end.show',$c) }}" class="btn btn-sm btn-info">Lihat</a></td>
				</tr>
				@endforeach
			</tbody>
		</table>
		{{ $closings->links() }}
	</div>
</div>
@endsection


