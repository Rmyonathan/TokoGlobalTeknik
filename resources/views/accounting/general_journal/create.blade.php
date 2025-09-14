@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Buat Jurnal Umum</div>
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

		<form method="POST" action="{{ route('accounting.general-journal.store') }}" id="gj-form">
			@csrf
			<div class="row g-3 mb-3">
				<div class="col-md-3">
					<label class="form-label">Tanggal</label>
					<input type="date" name="journal_date" class="form-control" value="{{ old('journal_date', now()->format('Y-m-d')) }}" required>
				</div>
				<div class="col-md-3">
					<label class="form-label">Periode</label>
					<select name="accounting_period_id" class="form-select" required>
						<option value="">- Pilih Periode -</option>
						@foreach ($periods as $p)
							<option value="{{ $p->id }}" {{ old('accounting_period_id')==$p->id?'selected':'' }}>{{ $p->name }} ({{ $p->start_date }} s/d {{ $p->end_date }})</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label">Referensi</label>
					<input type="text" name="reference" class="form-control" value="{{ old('reference') }}">
				</div>
				<div class="col-md-12">
					<label class="form-label">Deskripsi</label>
					<input type="text" name="description" class="form-control" value="{{ old('description') }}">
				</div>
			</div>

			<table class="table table-bordered" id="lines-table">
				<thead>
					<tr>
						<th style="width:40%">Akun</th>
						<th style="width:20%">Debit</th>
						<th style="width:20%">Kredit</th>
						<th style="width:18%">Memo</th>
						<th style="width:2%"></th>
					</tr>
				</thead>
				<tbody id="lines-body">
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
			<button type="submit" class="btn btn-primary">Simpan</button>
		</form>
	</div>
</div>

<template id="line-template">
	<tr>
		<td>
			<select name="lines[__INDEX__][account_id]" class="form-select" required>
				<option value="">- Pilih Akun -</option>
				@foreach ($accounts as $a)
					<option value="{{ $a->id }}">{{ $a->code }} - {{ $a->name }}</option>
				@endforeach
			</select>
		</td>
		<td><input type="number" step="0.01" min="0" name="lines[__INDEX__][debit]" class="form-control amount debit" value="0"></td>
		<td><input type="number" step="0.01" min="0" name="lines[__INDEX__][credit]" class="form-control amount credit" value="0"></td>
		<td><input type="text" name="lines[__INDEX__][memo]" class="form-control"></td>
		<td><button type="button" class="btn btn-sm btn-danger remove-line">×</button></td>
	</tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function(){
	let idx = 0;
	const tbody = document.getElementById('lines-body');
	const tpl = document.getElementById('line-template').innerHTML;
	const addBtn = document.getElementById('add-line');

	function recalc(){
		let tD=0, tC=0;
		document.querySelectorAll('#lines-body .debit').forEach(i=>{ tD += parseFloat(i.value||0); });
		document.querySelectorAll('#lines-body .credit').forEach(i=>{ tC += parseFloat(i.value||0); });
		document.getElementById('total-debit').innerText = tD.toFixed(2);
		document.getElementById('total-credit').innerText = tC.toFixed(2);
	}

	function addLine(){
		const html = tpl.replaceAll('__INDEX__', idx++);
		const tr = document.createElement('tr');
		tr.innerHTML = html;
		tbody.appendChild(tr);
		recalc();
	}

	addBtn.addEventListener('click', addLine);
	addLine();
	addLine();

	document.getElementById('lines-table').addEventListener('input', function(e){
		if (e.target.classList.contains('amount')) recalc();
	});

	document.getElementById('lines-table').addEventListener('click', function(e){
		if (e.target.classList.contains('remove-line')){
			e.target.closest('tr').remove();
			recalc();
		}
	});
});
</script>
@endsection


