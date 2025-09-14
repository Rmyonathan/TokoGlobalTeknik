@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header">Detail Jurnal: {{ $journal->journal_no }}</div>
	<div class="card-body">
		<div class="mb-3">
			<div><strong>Tanggal:</strong> {{ $journal->journal_date }}</div>
			<div><strong>Referensi:</strong> {{ $journal->reference }}</div>
			<div><strong>Deskripsi:</strong> {{ $journal->description }}</div>
		</div>
		<table class="table table-bordered table-sm">
			<thead>
				<tr>
					<th>Akun</th>
					<th>Debit</th>
					<th>Kredit</th>
					<th>Memo</th>
				</tr>
			</thead>
			<tbody>
				@php($tD=0)
				@php($tC=0)
				@foreach ($journal->details as $d)
					<tr>
						<td>{{ optional($d->account)->code }} - {{ optional($d->account)->name }}</td>
						<td>{{ number_format($d->debit,2) }}</td>
						<td>{{ number_format($d->credit,2) }}</td>
						<td>{{ $d->memo }}</td>
					</tr>
					@php($tD += $d->debit)
					@php($tC += $d->credit)
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<th>Total</th>
					<th>{{ number_format($tD,2) }}</th>
					<th>{{ number_format($tC,2) }}</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
		<a href="{{ route('accounting.general-journal.index') }}" class="btn btn-secondary">Kembali</a>
		<a href="{{ route('accounting.general-journal.edit', $journal) }}" class="btn btn-primary">Edit</a>
	</div>
</div>
@endsection


