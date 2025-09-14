@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Edit Jurnal Umum</div>
	<div class="card-body">
		@if ($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form method="POST" action="{{ route('accounting.general-journal.update', $journal->id) }}" id="gj-form">
			@csrf
			@method('PUT')
			<div class="row g-3 mb-3">
				<div class="col-md-3">
					<label class="form-label">Tanggal</label>
					<input type="date" name="journal_date" class="form-control" value="{{ old('journal_date', optional($journal->journal_date)->format('Y-m-d')) }}" required>
				</div>
				<div class="col-md-3">
					<label class="form-label">Periode</label>
					<select name="accounting_period_id" class="form-select" required>
						@foreach ($periods as $p)
							<option value="{{ $p->id }}" {{ old('accounting_period_id',$journal->accounting_period_id)==$p->id?'selected':'' }}>{{ $p->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label">Referensi</label>
					<input type="text" name="reference" class="form-control" value="{{ old('reference',$journal->reference) }}">
				</div>
				<div class="col-md-12">
					<label class="form-label">Deskripsi</label>
					<input type="text" name="description" class="form-control" value="{{ old('description',$journal->description) }}">
				</div>
			</div>

			<table class="table table-bordered" id="lines-table">
				<thead>
					<tr>
						<th>Akun</th>
						<th>Debit</th>
						<th>Kredit</th>
						<th>Memo</th>
						<th></th>
					</tr>
				</thead>
				<tbody id="lines-body">
					@foreach ($journal->details as $i=>$d)
						<tr>
							<td>
								<select name="lines[{{ $i }}][account_id]" class="form-select" required>
									@foreach ($accounts as $a)
										<option value="{{ $a->id }}" {{ $d->account_id==$a->id?'selected':'' }}>{{ $a->code }} - {{ $a->name }}</option>
									@endforeach
								</select>
							</td>
							<td><input type="number" step="0.01" min="0" name="lines[{{ $i }}][debit]" class="form-control amount debit" value="{{ $d->debit }}"></td>
							<td><input type="number" step="0.01" min="0" name="lines[{{ $i }}][credit]" class="form-control amount credit" value="{{ $d->credit }}"></td>
							<td><input type="text" name="lines[{{ $i }}][memo]" class="form-control" value="{{ $d->memo }}"></td>
							<td><button type="button" class="btn btn-sm btn-danger remove-line">×</button></td>
						</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<th>Total</th>
						<th id="total-debit">0</th>
						<th id="total-credit">0</th>
						<th colspan="2"></th>
					</tr>
				</tfoot>
			</table>

			<button type="button" class="btn btn-secondary" id="add-line">Tambah Baris</button>
			<button type="submit" class="btn btn-primary">Update</button>
		</form>
	</div>
</div>
@endsection


