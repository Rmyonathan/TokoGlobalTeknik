@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-edit mr-2"></i>Edit Transaksi Pembelian</h2>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Data Transaksi</h5>
        </div>
        <div class="card-body">
            <form id="transactionForm">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_nota">No. Nota</label>
                            <input type="text" class="form-control" id="no_nota" name="nota" value="{{ $purchase->nota }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $purchase->tanggal->format('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="supplier">Supplier</label>
                            <input type="text" id="supplier" name="supplier_display" class="form-control" value="{{ $supplier }}" placeholder="Masukkan kode atau nama supplier">
                            <input type="hidden" id="kode_supplier" name="kode_supplier" value="{{ $purchase->kode_supplier }}">
                            <div id="supplierDropdown" class="dropdown-menu" style="display: none; position: absolute; width: 100%;"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cabang_display">Cabang (Stok Owner)</label>
                            <div class="input-group">
                                <input type="text" id="cabang_display" class="form-control" placeholder="Masukkan kode atau nama stok owner">
                                <input type="hidden" id="cabang" name="cabang">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                            <div id="cabangDropdown" class="dropdown-menu" style="display: none; width: 100%;"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="pembayaran">Pembayaran</label>
                            <select class="form-control" id="pembayaran" name="pembayaran">
                                <option value="Tunai" {{ $purchase->pembayaran == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                <option value="Transfer" {{ $purchase->pembayaran == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                <option value="Kredit" {{ $purchase->pembayaran == 'Kredit' ? 'selected' : '' }}>Kredit</option>
                                <option value="Debit" {{ $purchase->pembayaran == 'Debit' ? 'selected' : '' }}>Debit</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cara_bayar">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar" name="cara_bayar">
                                <option value="Cash" {{ $purchase->cara_bayar == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Credit Card" {{ $purchase->cara_bayar == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="Debit" {{ $purchase->cara_bayar == 'Debit' ? 'selected' : '' }}>Debit</option>
                                <option value="Cicilan" {{ $purchase->cara_bayar == 'Cicilan' ? 'selected' : '' }}>Cicilan</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Items Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Rincian Barang</h5>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addItemModal">
                <i class="fas fa-plus-circle"></i> Tambah Barang
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Keterangan</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Panjang</th>
                            <th>Total</th>
                            <th>Diskon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsList">
                        <!-- Dynamic items will be loaded by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Total</label>
                        <input type="text" class="form-control text-right" id="total" name="total" readonly value="{{ number_format($purchase->subtotal, 0, ',', '.') }}">
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="discount_checkbox" {{ $purchase->diskon > 0 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">Disc(%)</span>
                            </div>
                            <input type="number" class="form-control" id="discount_percent" value="{{ $purchase->diskon > 0 ? ($purchase->diskon / $purchase->subtotal) * 100 : 0 }}" {{ $purchase->diskon > 0 ? '' : 'disabled' }}>
                            <input type="text" class="form-control text-right" id="discount_amount" value="{{ number_format($purchase->diskon, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="ppn_checkbox" {{ $purchase->ppn > 0 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">PPN</span>
                            </div>
                            <input type="text" class="form-control text-right" id="ppn_amount" value="{{ number_format($purchase->ppn, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cara Bayar</label>
                        <select class="form-control" id="cara_bayar_akhir">
                            <option value="Cash" {{ $purchase->cara_bayar == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Credit Card" {{ $purchase->cara_bayar == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="Debit" {{ $purchase->cara_bayar == 'Debit' ? 'selected' : '' }}>Debit</option>
                            <option value="Cicilan" {{ $purchase->cara_bayar == 'Cicilan' ? 'selected' : '' }}>Cicilan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grand Total</label>
                        <input type="text" class="form-control text-right" id="grand_total" readonly value="{{ number_format($purchase->grand_total, 0, ',', '.') }}" style="font-size: 18px; font-weight: bold;">
                    </div>
                    <div class="form-group text-right mt-4">
                        <button type="button" class="btn btn-success" id="updateTransaction">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('pembelian.nota.show', $purchase->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Tambah Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addItemForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_barang">Kode Barang</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="kode_barang" name="kode_barang" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="findItem">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="nama_barang">Nama Barang</label>
                                <input type="text" class="form-control" id="nama_barang" name="nama_barang" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="harga">Harga</label>
                                <input type="number" class="form-control" id="harga" name="harga" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1" required>
                            </div>

                            <div class="form-group">
                                <label for="panjang">Panjang</label>
                                <input type="number" class="form-control" id="panjang" name="panjang" value="0" min="0" step="0.01">
                            </div>
                            
                            <div class="form-group">
                                <label for="diskon">Diskon (%)</label>
                                <input type="number" class="form-control" id="diskon" name="diskon" value="0" min="0" max="100">
                            </div>
                            
                            <div class="form-group">
                                <label for="satuan">Satuan</label>
                                <select class="form-control" id="satuan" name="satuan">
                                    <option value="PCS">PCS</option>
                                    <option value="MTR">MTR</option>
                                    <option value="BTG">BTG</option>
                                    <option value="LBR">LBR</option>
                                    <option value="UNIT">UNIT</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Kode</th>
                                            <th>Keterangan</th>
                                            <th>Harga</th>
                                            <th>Qty</th>
                                            <th>Panjang</th>
                                            <th>Total</th>
                                            <th>Satuan</th>
                                            <th>Disc(%)</th>
                                            <th>Sub Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemPreview">
                                        <!-- Item preview will be shown here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="saveItemBtn">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- IMPORTANT: Define global variables here that will be used in the external JS file --}}
<script>
    // Expose Laravel routes as global window variables
    window.supplierSearchUrl = "{{ route('api.suppliers.search') }}";
    window.updateTransactionUrl = "{{ route('pembelian.update', $purchase->id) }}";
    window.notaShowUrl = "{{ route('pembelian.nota.show', $purchase->id) }}";
    window.csrfToken = "{{ csrf_token() }}";
    window.purchaseId = "{{ $purchase->id }}";
    window.grandTotal = "{{ $purchase->grand_total }}";
    
    // Initial items data
    const initialItems = {!! json_encode($purchase->items->map(function($item) {
        return [
            'kodeBarang' => $item->kode_barang,
            'namaBarang' => $item->nama_barang,
            'keterangan' => $item->keterangan,
            'harga' => (int)$item->harga,
            'qty' => (int)$item->qty,
            'panjang' => 0, // Assuming this isn't stored, adjust if it is
            'diskon' => (int)$item->diskon,
            'total' => (int)$item->total
        ];
    })) !!};
</script>

{{-- Include the external JS file using file_get_contents to load directly from views directory --}}
<script>
{!! file_get_contents(resource_path('views/scripts/editpembelian.js')) !!}
</script>
@endsection
