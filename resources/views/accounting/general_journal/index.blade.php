@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>Jurnal Umum</span>
		<a href="{{ route('accounting.general-journal.create') }}" class="btn btn-primary btn-sm">Buat Jurnal</a>
	</div>
	<div class="card-body">
		@if(session('success'))
			<div class="alert alert-success">{{ session('success') }}</div>
		@endif

		<form method="GET" class="row g-2 mb-3" action="{{ route('accounting.general-journal.index') }}">
			<div class="col-md-3">
				<label class="form-label">Dari Tanggal</label>
				<input type="date" name="from" value="{{ request('from') }}" class="form-control">
			</div>
			<div class="col-md-3">
				<label class="form-label">Sampai Tanggal</label>
				<input type="date" name="to" value="{{ request('to') }}" class="form-control">
			</div>
			<div class="col-md-4">
				<label class="form-label">Cari (No/Referensi/Deskripsi)</label>
				<input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Ketik kata kunci...">
			</div>
			<div class="col-md-2 d-flex align-items-end">
				<button class="btn btn-secondary w-100" type="submit">Filter</button>
			</div>
		</form>

		<div class="d-flex justify-content-end mb-2">
			<small class="text-muted">Urut: terbaru (created_at) ke lama</small>
		</div>

		<table class="table table-bordered table-sm">
			<thead>
				<tr>
					<th>No</th>
					<th>Dibuat</th>
					<th>Tanggal</th>
					<th>Referensi</th>
					<th>Deskripsi</th>
					<th>Jumlah Baris</th>
					<th>Aksi</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($journals as $j)
				<tr>
					<td>{{ $j->journal_no }}</td>
					<td>{{ optional($j->created_at)->format('Y-m-d H:i') }}</td>
					<td>{{ $j->journal_date }}</td>
					<td>{{ $j->reference }}</td>
					<td>{{ $j->description }}</td>
					<td>{{ $j->details_count }}</td>
					<td>
						<a href="{{ route('accounting.general-journal.show', $j) }}" class="btn btn-sm btn-info">Lihat</a>
						<a href="{{ route('accounting.general-journal.edit', $j->id) }}" class="btn btn-sm btn-warning">Edit</a>
						<form action="{{ route('accounting.general-journal.destroy', $j->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jurnal?')">
							@csrf
							@method('DELETE')
							<button class="btn btn-sm btn-danger">Hapus</button>
						</form>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		{{ $journals->links() }}
	</div>
</div>
@endsection


