@extends('layout.Nav')

@section('content')
<div class="container">
    <h2 class="mb-4">Master Cara Bayar</h2>
    
    <form action="#" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="cara_bayar" class="form-label">Cara Bayar</label>
                <input type="text" class="form-control" id="cara_bayar" name="cara_bayar">
            </div>
            <div class="col-md-4">
                <label for="keterangan" class="form-label">Keterangan</label>
                <input type="text" class="form-control" id="keterangan" name="keterangan">
            </div>
            <div class="col-md-3">
                <label for="kode_account" class="form-label">Kode Account</label>
                <select class="form-select" id="kode_account" name="kode_account">
                    <option selected disabled>Pilih Kode Account</option>
                    <option value="111020">111020</option>
                    <option value="111003">111003</option>
                    <option value="111004">111004</option>
                    <option value="111005">111005</option>
                    <option value="111046">111046</option>
                    <option value="111030">111030</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="lokasi" class="form-label">Lokasi</label>
                <select class="form-select" id="lokasi" name="lokasi">
                    <option value="AL">AL</option>
                    <option value="ML">ML</option>
                    <option value="SL">SL</option>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="reset" class="btn btn-warning">Hapus</button>
            <button type="button" class="btn btn-info">Refresh</button>
            <a href="/" class="btn btn-danger">Exit</a>
        </div>
    </form>

    <h5>Display Data Cara Bayar</h5>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Cara Bayar</th>
                <th>Keterangan</th>
                <th>Kode Account</th>
                <th>Lokasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>BCA 15106000</td>
                <td>TRANSFER BCA 151-066000</td>
                <td>111020</td>
                <td>AL</td>
            </tr>
            <tr>
                <td>BCA 151065666</td>
                <td>TRANSFER BCA 151065666</td>
                <td>111003</td>
                <td>AL</td>
            </tr>
            <tr>
                <td>BCA 1510714888</td>
                <td>TRANSFER BCA 1510714888</td>
                <td>111004</td>
                <td>AL</td>
            </tr>
            <tr>
                <td>BCA 1510727777</td>
                <td>TRANSFER BCA 1510727777</td>
                <td>111005</td>
                <td>AL</td>
            </tr>
            <tr>
                <td>BCA 1510837777</td>
                <td>TRANSFER BCA 1510837777 CAB LAMPUNG</td>
                <td>111046</td>
                <td>AL</td>
            </tr>
            <tr>
                <td>BCA 15106000</td>
                <td>TRANSFER REKENING BCA 151-060600</td>
                <td>111020</td>
                <td>AL</td>
            </tr>
            <tr>
                <td>BCA 151150015</td>
                <td>TRANSFER BCA 151150015</td>
                <td>111030</td>
                <td>AL</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection