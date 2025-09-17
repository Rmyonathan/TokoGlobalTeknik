@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-exchange-alt mr-2"></i>Transfer Stok Antar Database</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="transferForm">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Database Asal</label>
                            <select class="form-control" id="source_db" name="source_db" required>
                                <option value="">Pilih Database</option>
                                @foreach($databases as $key => $db)
                                    <option value="{{ $key }}">{{ $db['name'] ?? $key }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Database Tujuan</label>
                            <select class="form-control" id="target_db" name="target_db" required>
                                <option value="">Pilih Database</option>
                                @foreach($databases as $key => $db)
                                    <option value="{{ $key }}">{{ $db['name'] ?? $key }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Barang</label>
                            <select class="form-control" id="kode_barang_id" name="kode_barang_id" required>
                                <option value="">Pilih Barang</option>
                                @foreach($kodeBarangs as $barang)
                                    <option value="{{ $barang->id }}">{{ $barang->kode_barang }} - {{ $barang->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" class="form-control" id="qty" name="qty" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Unit</label>
                            <input type="text" class="form-control" id="unit" name="unit" value="LBR">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Catatan</label>
                            <input type="text" class="form-control" id="note" name="note" placeholder="Catatan transfer (opsional)">
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-primary" id="doTransfer"><i class="fas fa-paper-plane mr-1"></i> Transfer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3" id="resultCard" style="display:none;">
        <div class="card-body">
            <h5 class="mb-3">Hasil Transfer</h5>
            <p><strong>No Transfer:</strong> <span id="res_transfer_no"></span></p>
            <p><strong>Qty:</strong> <span id="res_qty"></span></p>
            <p><strong>Avg Cost:</strong> <span id="res_avg_cost"></span></p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function(){
    $('#doTransfer').on('click', function(){
        const data = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            source_db: $('#source_db').val(),
            target_db: $('#target_db').val(),
            kode_barang_id: $('#kode_barang_id').val(),
            qty: $('#qty').val(),
            unit: $('#unit').val(),
            note: $('#note').val()
        };

        $.ajax({
            url: '{{ route("stock-transfer.store") }}',
            method: 'POST',
            data: data,
            success: function(resp){
                if(resp.success){
                    $('#res_transfer_no').text(resp.data.transfer_no);
                    $('#res_qty').text(resp.data.qty);
                    $('#res_avg_cost').text(resp.data.avg_cost);
                    $('#resultCard').show();
                    alert('Transfer berhasil');
                } else {
                    alert(resp.message || 'Transfer gagal');
                }
            },
            error: function(xhr){
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Terjadi kesalahan';
                alert('Gagal: ' + msg);
            }
        });
    });
});
</script>
@endsection


