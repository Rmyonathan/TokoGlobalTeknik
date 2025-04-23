@extends('layout.Nav')

@section('content')
<div class="container">
    <h3>Display Transaksi Penjualan</h3>

    <form method="POST" action="{{ route('transaksi.store') }}">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3">
                <label>No Penjualan</label>
                <input type="text" class="form-control bg-warning text-white fw-bold" value="ACP-0425-00146" readonly>
            </div>
            <div class="col-md-3">
                <label>Cabang</label>
                <input type="text" class="form-control" value="LAMPUNG" readonly>
            </div>
            <div class="col-md-3">
                <label>Tanggal</label>
                <input type="date" class="form-control" value="2025-04-14">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Customer</label>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" placeholder="Nama Customer">
                    <button class="btn btn-outline-secondary">Baru</button>
                </div>
            </div>
            <div class="col-md-4">
                <label>Sales</label>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" placeholder="Nama Sales">
                    <button class="btn btn-outline-secondary">Baru</button>
                </div>
            </div>
            <div class="col-md-4">
                <label>Pembayaran</label>
                <select class="form-control">
                    <option value="tunai">Tunai</option>
                    <option value="tempo">Tempo</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Cara Bayar</label>
                <select class="form-control">
                    <option value="transfer">Transfer</option>
                    <option value="cash">Cash</option>
                    <option value="debit">Debit</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Tanggal Jadi</label>
                <input type="date" class="form-control" value="2025-04-14">
            </div>
        </div>

        <!-- Input Barang -->
        <h5 class="mt-4">Input Barang</h5>
        <table class="table table-bordered mb-3">
            <thead>
                <tr>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Keterangan</th>
                    <th>Harga</th>
                    <th>P</th>
                    <th>L</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Diskon</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control" placeholder="Kode"></td>
                    <td><input type="text" class="form-control" placeholder="Nama"></td>
                    <td><input type="text" class="form-control" placeholder="Keterangan"></td>
                    <td><input type="number" class="form-control"></td>
                    <td><input type="number" class="form-control" style="width: 60px;"></td>
                    <td><input type="number" class="form-control" style="width: 60px;"></td>
                    <td><input type="number" class="form-control"></td>
                    <td><input type="number" class="form-control" readonly></td>
                    <td>
                        <div class="d-flex">
                            <input type="number" class="form-control" placeholder="Rp" style="width: 80px;">
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-secondary btn-sm">Clear</button>
                            <button class="btn btn-success btn-sm">OK</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Daftar Barang -->
        <h5>Daftar Barang</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Kode Barang</th>
                    <th>Keterangan</th>
                    <th>Harga</th>
                    <th>P</th>
                    <th>L</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Satuan</th>
                    <th>Disc(%)</th>
                    <th>Disc(Rp.)</th>
                    <th>Sub Total</th>
                </tr>
            </thead>
            <tbody>
                {{-- Dummy Data --}}
                <tr>
                    <td>BRG001</td>
                    <td>Barang A</td>
                    <td>10000</td>
                    <td>10</td>
                    <td>5</td>
                    <td>2</td>
                    <td>100000</td>
                    <td>Pcs</td>
                    <td>10</td>
                    <td>1000</td>
                    <td>89000</td>
                </tr>
                <tr>
                    <td>BRG002</td>
                    <td>Barang B</td>
                    <td>20000</td>
                    <td>15</td>
                    <td>5</td>
                    <td>1</td>
                    <td>20000</td>
                    <td>Box</td>
                    <td>5</td>
                    <td>1000</td>
                    <td>18000</td>
                </tr>
            </tbody>
        </table>

        <!-- Total -->
        <div class="row justify-content-end">
            <div class="col-md-4">
                <table class="table table-bordered">
                    <tr>
                        <th>Total</th>
                        <td>108000</td>
                    </tr>
                    <tr>
                        <th>Disc(%)</th>
                        <td><input type="number" class="form-control" value="5"></td>
                    </tr>
                    <tr>
                        <th>Disc(Rp)</th>
                        <td><input type="number" class="form-control" value="5400"></td>
                    </tr>
                    <tr>
                        <th>PPN</th>
                        <td><input type="number" class="form-control" value="0"></td>
                    </tr>
                    <tr>
                        <th>DP</th>
                        <td><input type="number" class="form-control" value="0"></td>
                    </tr>
                    <tr>
                        <th>Grand Total</th>
                        <td><strong>102600</strong></td>
                    </tr>
                    <tr>
                        <th>Cara Bayar</th>
                        <td>
                            <select class="form-control">
                                <option>Transfer</option>
                                <option>Cash</option>
                                <option>Debit</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Simpan</button>
            <button class="btn btn-secondary">Refresh</button>
        </div>
    </form>
</div>
@endsection

(displaypenjualan.blade.php)