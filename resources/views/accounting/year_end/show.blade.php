@extends('layout.Nav')

@section('content')
<div class="card">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>Detail Tutup Buku: {{ $closing->fiscal_year }}</span>
		<div>
			<a href="{{ route('accounting.year-end.export.all-accounts', $closing) }}" class="btn btn-sm btn-outline-secondary text-white">Export Semua Akun (CSV)</a>
			<a href="{{ route('accounting.year-end.export.csv', $closing) }}" class="btn btn-sm btn-outline-secondary text-white">Export CSV</a>
			<a href="{{ route('accounting.year-end.export.pdf', $closing) }}" class="btn btn-sm btn-outline-secondary text-white">Export PDF</a>
		</div>
	</div>
	<div class="card-body">
		<div class="mb-3">
			<div><strong>Status:</strong> {{ $closing->status }}</div>
			<div><strong>Tanggal Tutup:</strong> {{ optional($closing->closed_on)->format('Y-m-d') }}</div>
			<div><strong>Oleh:</strong> {{ $closing->closed_by }}</div>
		</div>

		@php($snap = $closing->snapshots ?? [])
		@if(!empty($snap))
			@if(!empty($snap['summary']))
				<div class="card mb-3">
					<div class="card-header">Ringkasan Periode</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-4">
								<div><strong>Periode:</strong></div>
								<div>{{ ($snap['summary']['period']['start_date'] ?? '-') }} s/d {{ ($snap['summary']['period']['end_date'] ?? '-') }}</div>
							</div>
							<div class="col-md-8">
								<div class="table-responsive">
									<table class="table table-sm table-bordered mb-0">
										<tbody>
											<tr>
												<th style="width: 220px;">Jumlah Jurnal</th>
												<td>{{ number_format($snap['summary']['journal_count'] ?? 0) }}</td>
											</tr>
											<tr>
												<th>Total Debit</th>
												<td>Rp {{ number_format($snap['summary']['total_debit'] ?? 0, 0, ',', '.') }}</td>
											</tr>
											<tr>
												<th>Total Kredit</th>
												<td>Rp {{ number_format($snap['summary']['total_credit'] ?? 0, 0, ',', '.') }}</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif

			@if(!empty($snap['top_accounts']))
				<div class="card mb-3">
					<div class="card-header">Top Akun Pergerakan</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-sm">
								<thead class="thead-light">
									<tr>
										<th>Kode</th>
										<th>Nama Akun</th>
										<th class="text-end">Debet</th>
										<th class="text-end">Kredit</th>
									</tr>
								</thead>
								<tbody>
									@foreach($snap['top_accounts'] as $row)
									<tr>
										<td>{{ $row['code'] ?? '-' }}</td>
										<td>{{ $row['name'] ?? '-' }}</td>
										<td class="text-end">Rp {{ number_format($row['debit'] ?? 0, 0, ',', '.') }}</td>
										<td class="text-end">Rp {{ number_format($row['credit'] ?? 0, 0, ',', '.') }}</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			@endif

			<div class="card">
				<div class="card-header">Reports</div>
				<div class="card-body">
					@if(!empty($snap['reports']))
						<div class="table-responsive">
							<table class="table table-bordered table-sm">
								<thead class="thead-light">
									<tr>
										<th>Jenis</th>
										<th>Nama</th>
										<th>Dibuat Pada</th>
										<th>Metadata</th>
									</tr>
								</thead>
								<tbody>
									@foreach($snap['reports'] as $r)
									<tr>
										<td>{{ $r['type'] ?? '-' }}</td>
										<td>{{ $r['name'] ?? '-' }}</td>
										<td>{{ $r['created_at'] ?? '-' }}</td>
										<td><code>{{ isset($r['metadata']) ? json_encode($r['metadata']) : '-' }}</code></td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@else
						<div class="text-muted">Belum ada report tersimpan untuk periode ini.</div>
					@endif
				</div>
			</div>
		@else
			<div class="text-muted">Snapshot belum tersedia.</div>
		@endif

		<a href="{{ route('accounting.year-end.index') }}" class="btn btn-secondary mt-3">Kembali</a>
	</div>
</div>
@endsection


