@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Proses Tutup Buku Tahunan</div>
	<div class="card-body">
		@if(session('error'))
			<div class="alert alert-danger">{{ session('error') }}</div>
		@endif
		<form method="POST" action="{{ route('accounting.year-end.store') }}">
			@csrf
			<div class="row g-3 mb-3">
				<div class="col-md-6">
					<label class="form-label">Periode Akuntansi</label>
					<select name="accounting_period_id" class="form-select" required>
						<option value="">- Pilih Periode -</option>
						@foreach ($periods as $p)
							<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->start_date }} - {{ $p->end_date }})</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label">Tahun Fiskal</label>
					<input type="number" name="fiscal_year" class="form-control" value="{{ now()->year }}" required>
				</div>
			</div>
			<button class="btn btn-primary">Proses Tutup Buku</button>
			<a href="{{ route('accounting.year-end.index') }}" class="btn btn-secondary">Batal</a>
		</form>
	</div>
</div>
@endsection


