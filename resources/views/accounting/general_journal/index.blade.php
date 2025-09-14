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
		<table class="table table-bordered table-sm">
			<thead>
				<tr>
					<th>No</th>
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


