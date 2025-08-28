@extends('layout.Nav')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h3 class="card-title">Edit Sales Order</h3>
					<div class="card-tools">
						<a href="{{ route('sales-order.show', $salesOrder) }}" class="btn btn-secondary btn-sm">Batal</a>
					</div>
				</div>
				<div class="card-body">
					<form action="{{ route('sales-order.update', $salesOrder) }}" method="POST" id="editSalesOrderForm">
						@csrf
						@method('PUT')

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>No. Sales Order</label>
									<input type="text" class="form-control" value="{{ $salesOrder->no_so }}" readonly>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="tanggal">Tanggal</label>
									<input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ optional($salesOrder->tanggal)->format('Y-m-d') }}" required>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="customer_id">Customer</label>
									<select class="form-control" id="customer_id" name="customer_id" required>
										@foreach($customers as $customer)
											<option value="{{ $customer->id }}" {{ $salesOrder->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->nama }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="salesman_id">Salesman</label>
									<select class="form-control" id="salesman_id" name="salesman_id" required>
										@foreach($salesmen as $salesman)
											<option value="{{ $salesman->id }}" {{ $salesOrder->salesman_id == $salesman->id ? 'selected' : '' }}>{{ $salesman->keterangan }}</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label for="cara_bayar">Cara Bayar</label>
									<select class="form-control" id="cara_bayar" name="cara_bayar" required>
										<option value="Tunai" {{ $salesOrder->cara_bayar == 'Tunai' ? 'selected' : '' }}>Tunai</option>
										<option value="Kredit" {{ $salesOrder->cara_bayar == 'Kredit' ? 'selected' : '' }}>Kredit</option>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group" id="hari_tempo_group">
									<label for="hari_tempo">Hari Tempo</label>
									<input type="number" class="form-control" id="hari_tempo" name="hari_tempo" min="0" value="{{ $salesOrder->hari_tempo }}">
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="tanggal_estimasi">Tanggal Estimasi Pengiriman</label>
									<input type="date" class="form-control" id="tanggal_estimasi" name="tanggal_estimasi" value="{{ optional($salesOrder->tanggal_estimasi)->format('Y-m-d') }}">
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="keterangan">Keterangan</label>
							<textarea class="form-control" id="keterangan" name="keterangan" rows="2">{{ $salesOrder->keterangan }}</textarea>
						</div>

						<hr>

						<h5>Detail Barang</h5>
						<div id="items-container">
							@foreach($salesOrder->items as $index => $item)
							<div class="item-row" data-index="{{ $index }}">
								<div class="row">
									<div class="col-md-3">
										<div class="form-group">
											<label>Barang</label>
											<select class="form-control item-barang" name="items[{{ $index }}][kode_barang_id]" required>
												@foreach($kodeBarangs as $barang)
													<option value="{{ $barang->id }}" {{ $barang->id == $item->kode_barang_id ? 'selected' : '' }}>{{ $barang->kode_barang }} - {{ $barang->name }}</option>
												@endforeach
											</select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>Qty</label>
											<input type="number" class="form-control item-qty" name="items[{{ $index }}][qty]" step="0.01" min="0.01" value="{{ $item->qty }}" required>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>Satuan</label>
											<select class="form-control item-satuan" name="items[{{ $index }}][satuan]" required>
												<option value="{{ $item->satuan }}" selected>{{ $item->satuan }}</option>
											</select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>Harga</label>
											<input type="number" class="form-control item-harga" name="items[{{ $index }}][harga]" step="0.01" min="0" value="{{ $item->harga }}" required>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>Total</label>
											<input type="number" class="form-control item-total" value="{{ $item->total }}" readonly>
										</div>
									</div>
								</div>
							</div>
							@endforeach
						</div>

						<div class="row mt-3">
							<div class="col-md-12">
								<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
								<a href="{{ route('sales-order.show', $salesOrder) }}" class="btn btn-secondary">Batal</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection


