@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Detail Purchase Order</h4>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small">No. PO</label>
                        <div class="font-weight-bold">{{ $po->no_po }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Tanggal</label>
                        <div class="font-weight-bold">{{ \Carbon\Carbon::parse($po->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Sales</label>
                        <div class="font-weight-bold">{{ $po->sales }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            @if($po->status === 'pending')
                                <span class="badge badge-pill badge-warning">Pending</span>
                            @elseif($po->status === 'completed')
                                <span class="badge badge-pill badge-success">Completed</span>
                            @elseif($po->status === 'cancelled')
                                <span class="badge badge-pill badge-danger">Batal</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted small">Customer</label>
                        <div class="font-weight-bold">{{ $po->kode_customer }} - {{ $po->customer->nama ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Alamat Customer</label>
                        <div class="font-weight-bold">{{ $po->customer->alamat ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">HP / Telp Customer</label>
                        <div class="font-weight-bold">HP: {{ $po->customer->hp ?? 'N/A' }} / TLP: {{ $po->customer->telepon ?? 'N/A' }}</div>
                    </div>
                    @if($po->tanggal_jadi)
                    <div class="mb-3">
                        <label class="text-muted small">Tanggal Jadi</label>
                        <div class="font-weight-bold">{{ \Carbon\Carbon::parse($po->tanggal_jadi)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4 shadow-sm">
                <div class="card-header mb-3">
                    <h5 class="mb-0">Daftar Item</h5>
                </div>
                <div class="table-responsive mb-3" style="max-height: 350px; overflow-y: auto; border-radius: 0.25rem;">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr class="bg-dark text-white" style="position: sticky; top: 0; z-index: 999;">
                                <th style="width: 12%; border-top-left-radius: 0.25rem;">Kode Barang</th>
                                <th style="width: 22%;">Nama Barang</th>
                                <th style="width: 26%;">Keterangan</th>
                                <th style="width: 10%; text-align: right;">Harga</th>
                                <th style="width: 8%; text-align: center;">Panjang</th>
                                <th style="width: 6%; text-align: center;">Qty</th>
                                <th style="width: 6%; text-align: center;">Diskon</th>
                                <th style="width: 10%; text-align: right; border-top-right-radius: 0.25rem;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $item)
                                <tr>
                                    <td>{{ $item->kode_barang }}</td>
                                    <td>{{ $item->nama_barang }}</td>
                                    <td>{{ $item->keterangan }}</td>
                                    <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td>{{ $item->panjang }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->diskon }}%</td>
                                    <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Informasi Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Pembayaran</div>
                                <div class="col-7 font-weight-bold">{{ $po->pembayaran ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Cara Bayar</div>
                                <div class="col-7 font-weight-bold">{{ $po->cara_bayar ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">Rincian Biaya</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td>Subtotal</td>
                                        <td class="text-right font-weight-bold">Rp {{ number_format($po->subtotal ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Diskon</td>
                                        <td class="text-right">{{ $po->discount ?? 0 }}%</td>
                                    </tr>
                                    <tr>
                                        <td>Diskon Rupiah</td>
                                        <td class="text-right">Rp {{ number_format($po->disc_rupiah ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>PPN</td>
                                        <td class="text-right">Rp {{ number_format($po->ppn ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>DP</td>
                                        <td class="text-right">Rp {{ number_format($po->dp ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td class="font-weight-bold">Grand Total</td>
                                        <td class="text-right font-weight-bold">Rp {{ number_format($po->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                <a href="{{ route('transaksi.purchaseorder') }}" class="btn btn-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar PO
                </a>
                @if($po->status === 'pending')
                    {{-- Form Selesaikan Transaksi --}}
                    <form action="{{ route('purchase-order.complete', ['id' => $po->id]) }}" method="POST" class="mr-2">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Yakin ingin selesaikan transaksi ini?');">
                            <i class="fas fa-check mr-1"></i> Selesaikan Transaksi
                        </button>
                    </form>

                    {{-- Form Batalkan PO --}}
                    <form action="{{ route('purchase-order.cancel', ['id' => $po->id]) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin membatalkan PO ini?');">
                            <i class="fas fa-times mr-1"></i> Batalkan PO
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection