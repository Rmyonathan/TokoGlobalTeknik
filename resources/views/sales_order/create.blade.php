@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Buat Sales Order Baru</h3>
                    <div class="card-tools">
                        <a href="{{ route('sales-order.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('sales-order.store') }}" method="POST" id="salesOrderForm">
                        @csrf
                        
                        <!-- Header Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="no_so">No. Sales Order</label>
                                    <input type="text" class="form-control" id="no_so" name="no_so" value="{{ $noSo }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal">Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_id">Customer</label>
                                    <select class="form-control" id="customer_id" name="customer_id" required>
                                        <option value="">Pilih Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                data-limit-kredit="{{ $customer->limit_kredit }}"
                                                data-hari-tempo="{{ $customer->limit_hari_tempo }}">
                                                {{ $customer->nama }} - {{ $customer->getStatusKredit() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="salesman_id">Salesman</label>
                                    <select class="form-control" id="salesman_id" name="salesman_id" required>
                                        <option value="">Pilih Salesman</option>
                                        @foreach($salesmen as $salesman)
                                            <option value="{{ $salesman->id }}">{{ $salesman->keterangan }}</option>
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
                                        <option value="Tunai">Tunai</option>
                                        <option value="Kredit">Kredit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group" id="hari_tempo_group" style="display: none;">
                                    <label for="hari_tempo">Hari Tempo</label>
                                    <input type="number" class="form-control" id="hari_tempo" name="hari_tempo" min="0" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tanggal_estimasi">Tanggal Estimasi Pengiriman</label>
                                    <input type="date" class="form-control" id="tanggal_estimasi" name="tanggal_estimasi">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="2"></textarea>
                        </div>

                        <hr>

                        <!-- Items Section -->
                        <h5>Detail Barang</h5>
                        <div id="items-container">
                            <div class="item-row" data-index="0">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Barang</label>
                                            <select class="form-control item-barang" name="items[0][kode_barang_id]" required>
                                                <option value="">Pilih Barang</option>
                                                @foreach($kodeBarangs as $barang)
                                                    <option value="{{ $barang->id }}" 
                                                        data-harga="{{ $barang->harga_jual }}"
                                                        data-unit-dasar="{{ $barang->unit_dasar }}">
                                                        {{ $barang->kode_barang }} - {{ $barang->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Qty</label>
                                            <input type="number" class="form-control item-qty" name="items[0][qty]" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Satuan</label>
                                            <select class="form-control item-satuan" name="items[0][satuan]" required>
                                                <option value="LBR">LBR</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Harga</label>
                                            <input type="number" class="form-control item-harga" name="items[0][harga]" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Total</label>
                                            <input type="number" class="form-control item-total" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm remove-item" style="display: none;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Keterangan</label>
                                            <input type="text" class="form-control" name="items[0][keterangan]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-success btn-sm" id="add-item">
                                    <i class="fas fa-plus"></i> Tambah Item
                                </button>
                            </div>
                        </div>

                        <hr>

                        <!-- Summary -->
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-right">Rp <span id="subtotal">0</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Grand Total:</strong></td>
                                        <td class="text-right">Rp <span id="grand-total">0</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Sales Order
                                </button>
                                <a href="{{ route('sales-order.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Messages -->
@if($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 0;

    // Handle cara bayar change
    $('#cara_bayar').change(function() {
        if ($(this).val() === 'Kredit') {
            $('#hari_tempo_group').show();
            $('#hari_tempo').prop('required', true);
        } else {
            $('#hari_tempo_group').hide();
            $('#hari_tempo').prop('required', false);
        }
    });

    // Handle customer change
    $('#customer_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const limitKredit = selectedOption.data('limit-kredit');
        const hariTempo = selectedOption.data('hari-tempo');
        
        if ($('#cara_bayar').val() === 'Kredit') {
            $('#hari_tempo').val(hariTempo);
        }
    });

    // Handle barang change
    $(document).on('change', '.item-barang', function() {
        const row = $(this).closest('.item-row');
        const selectedOption = $(this).find('option:selected');
        const harga = selectedOption.data('harga') || 0;
        const unitDasar = selectedOption.data('unit-dasar') || 'LBR';
        
        row.find('.item-harga').val(harga);
        row.find('.item-satuan').val(unitDasar);
        
        // Get available units for this product
        const kodeBarangId = $(this).val();
        if (kodeBarangId) {
            $.get(`/sales-order/available-units/${kodeBarangId}`, function(units) {
                const satuanSelect = row.find('.item-satuan');
                satuanSelect.empty();
                units.forEach(function(unit) {
                    satuanSelect.append(`<option value="${unit}">${unit}</option>`);
                });
                satuanSelect.val(unitDasar);
            });
        }
        
        calculateItemTotal(row);
    });

    // Handle qty change
    $(document).on('input', '.item-qty', function() {
        calculateItemTotal($(this).closest('.item-row'));
    });

    // Handle harga change
    $(document).on('input', '.item-harga', function() {
        calculateItemTotal($(this).closest('.item-row'));
    });

    // Handle satuan change
    $(document).on('change', '.item-satuan', function() {
        const row = $(this).closest('.item-row');
        const customerId = $('#customer_id').val();
        const kodeBarangId = row.find('.item-barang').val();
        const satuan = $(this).val();
        
        if (customerId && kodeBarangId && satuan) {
            $.get('/sales-order/customer-price', {
                customer_id: customerId,
                kode_barang_id: kodeBarangId,
                unit: satuan
            }, function(priceInfo) {
                row.find('.item-harga').val(priceInfo.harga_jual);
                calculateItemTotal(row);
            });
        }
    });

    // Add new item
    $('#add-item').click(function() {
        itemIndex++;
        const newRow = $('.item-row').first().clone();
        
        // Update names and IDs
        newRow.attr('data-index', itemIndex);
        newRow.find('select, input').each(function() {
            const name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace('[0]', `[${itemIndex}]`));
            }
        });
        
        // Clear values
        newRow.find('input, select').val('');
        newRow.find('.item-satuan').html('<option value="LBR">LBR</option>');
        newRow.find('.remove-item').show();
        
        $('#items-container').append(newRow);
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        calculateTotals();
    });

    // Calculate item total
    function calculateItemTotal(row) {
        const qty = parseFloat(row.find('.item-qty').val()) || 0;
        const harga = parseFloat(row.find('.item-harga').val()) || 0;
        const total = qty * harga;
        
        row.find('.item-total').val(total.toFixed(2));
        calculateTotals();
    }

    // Calculate totals
    function calculateTotals() {
        let subtotal = 0;
        $('.item-total').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        
        $('#subtotal').text(subtotal.toLocaleString('id-ID'));
        $('#grand-total').text(subtotal.toLocaleString('id-ID'));
    }

    // Form validation
    $('#salesOrderForm').submit(function(e) {
        const customerId = $('#customer_id').val();
        const caraBayar = $('#cara_bayar').val();
        
        if (caraBayar === 'Kredit' && !customerId) {
            alert('Customer harus dipilih untuk transaksi kredit');
            e.preventDefault();
            return false;
        }
        
        // Check if at least one item has data
        let hasItems = false;
        $('.item-barang').each(function() {
            if ($(this).val()) {
                hasItems = true;
                return false;
            }
        });
        
        if (!hasItems) {
            alert('Minimal satu item harus diisi');
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
