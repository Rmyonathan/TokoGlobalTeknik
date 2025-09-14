@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Detail Tutup Buku: {{ $closing->fiscal_year }}</div>
	<div class="card-body">
		<div class="mb-3">
			<div><strong>Status:</strong> {{ $closing->status }}</div>
			<div><strong>Tanggal Tutup:</strong> {{ $closing->closed_on }}</div>
			<div><strong>Oleh:</strong> {{ $closing->closed_by }}</div>
		</div>
		<h6>Snapshot Reports</h6>
		<pre style="white-space: pre-wrap">{{ json_encode($closing->snapshots, JSON_PRETTY_PRINT) }}</pre>
		<a href="{{ route('accounting.year-end.index') }}" class="btn btn-secondary">Kembali</a>
	</div>
</div>
@endsection


