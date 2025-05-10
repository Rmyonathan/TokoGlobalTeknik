@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Transaksi Penjualan</h2>
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
                            <label for="no_transaksi">No. Transaksi</label>
                            <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" value="{{ $noTransaksi ?? 'KP/WS/0147' }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
                        </div>

                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="customer">Customer</label>
                            <input type="text" id="customer" name="customer_display" class="form-control" placeholder="Masukkan kode atau nama customer">
                            <input type="hidden" id="kode_customer" name="kode_customer"> <!-- Hanya kode_customer yang dikirim -->
                            <div id="customerDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                        </div>

                        <div class="form-group">
                            <label for="sales">Sales</label>
                            <input type="text" id="sales" name="sales_display" class="form-control" placeholder="Masukkan kode atau nama sales">
                            <input type="hidden" id="kode_sales" name="sales"> <!-- Hanya kode_sales yang dikirim -->
                            <div id="salesDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lokasi">Lokasi</label>
                            <select class="form-control" id="lokasi" name="lokasi">
                                <option value="LAMPUNG" selected>LAMPUNG</option>
                                <option value="JAKARTA">JAKARTA</option>
                                <option value="BANDUNG">BANDUNG</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran</label>
                            <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
                                <option selected disabled value=""> Pilih Metode Pembayaran</option>
                                <option value="Tunai">Tunai</option>
                                <option value="Non Tunai">Non Tunai</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cara_bayar">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar" name="cara_bayar">
                                <option value="">Pilih Metode Dulu</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_jadi">Tanggal Jadi</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal_jadi" name="tanggal_jadi" value="{{ date('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
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
                            <th>Length</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Diskon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="itemsList">
                        <!-- Dynamic items will be added here -->
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
                        <input type="text" class="form-control text-right" id="total" name="total" readonly value="0">
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="discount_checkbox">
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">Disc(%)</span>
                            </div>
                            <input type="number" class="form-control" id="discount_percent" value="0" disabled>
                            <input type="text" class="form-control text-right" id="discount_amount" value="0" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="disc_rp_checkbox">
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">Disc(Rp.)</span>
                            </div>
                            <input type="number" class="form-control" id="disc_rp" value="0" disabled>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="ppn_checkbox">
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">PPN</span>
                            </div>
                            <input type="text" class="form-control text-right" id="ppn_amount" value="0" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="dp_checkbox">
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">DP</span>
                            </div>
                            <input type="number" class="form-control" id="dp_amount" value="0" disabled>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cara Bayar</label>
                        <select class="form-control" id="cara_bayar_akhir" disabled>
                            <option value="">-- Belum Dipilih --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grand Total</label>
                        <input type="text" class="form-control text-right" id="grand_total" readonly value="0" style="font-size: 18px; font-weight: bold;">
                    </div>
                    <div class="form-group text-right mt-4">
                        <button type="button" class="btn btn-success" id="saveTransaction">
                            <i class="fas fa-save"></i> Simpan Transaksi
                        </button>
                        <button type="button" class="btn btn-warning" id="buatPOBtn">
                            <i class="fas fa-file-alt"></i> Buat PO
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelTransaction">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addCustomerForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Tambah Customer Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <input type="text" class="form-control" id="alamat" name="alamat">
                    </div>
                    <div class="form-group">
                        <label for="hp">HP</label>
                        <input type="text" class="form-control" id="hp" name="hp" required>
                    </div>
                    <div class="form-group">
                        <label for="telepon">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
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
                @include('transaksi.add_item')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="saveItemBtn">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Invoice -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Invoice Content -->
                <div id="invoiceContent">
                    <h4>No Transaksi: <span id="invoiceNoTransaksi"></span></h4>
                    <p>Tanggal: <span id="invoiceTanggal"></span></p>
                    <p>Customer: <span id="invoiceCustomer"></span></p>
                    <p>Grand Total: <span id="invoiceGrandTotal"></span></p>
                    <!-- Tambahkan detail lainnya jika diperlukan -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="printInvoiceBtn">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" id="backToFormBtn">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
$(document).ready(function() {
    // Initialize variables
    let items = [];
    let grandTotal = 0;

    // Metode Pembayaran
    $('#metode_pembayaran').on('change', function () {
        const metode = $(this).val();
        $('#cara_bayar').html('<option value="">Loading...</option>');
        
        $.ajax({
            url: '{{ url("api/cara-bayar/by-metode") }}',
            method: 'GET',
            data: { metode: metode },
            success: function (data) {
                let options = '<option value="">-- Pilih Cara Bayar --</option>';
                data.forEach(cb => {
                    options += `<option value="${cb.nama}">${cb.nama}</option>`;
                });
                $('#cara_bayar').html(options);
            },
            error: function () {
                $('#cara_bayar').html('<option value="">Gagal load data</option>');
            }
        });
    });

    $('#cara_bayar').on('change', function () {
        const selected = $(this).val();
        $('#cara_bayar_akhir')
            .html(`<option value="${selected}">${selected}</option>`)
            .val(selected);
    });

    // Search customers
    $('#customer').on('input', function () {
        const keyword = $(this).val();
        if (keyword.length > 0) {
            $.ajax({
                url: "{{ route('api.customers.search') }}",
                method: "GET",
                data: { keyword },
                success: function (data) {
                    let dropdown = '';
                    if (data.length > 0) {
                        data.forEach(customer => {
                            dropdown += `<a class="dropdown-item customer-item" data-kode="${customer.kode_customer}" data-name="${customer.nama}">${customer.kode_customer} - ${customer.nama}</a>`;
                        });
                    } else {
                        dropdown = '<a class="dropdown-item disabled">Tidak ada customer ditemukan</a>';
                    }
                    $('#customerDropdown').html(dropdown).show();
                },
                error: function () {
                    alert('Terjadi kesalahan saat mencari customer.');
                }
            });
        } else {
            $('#customerDropdown').hide();
        }
    });

    // Select Customer
    $(document).on('click', '.customer-item', function () {
        const kodeCustomer = $(this).data('kode');
        const namaCustomer = $(this).data('name');
        $('#kode_customer').val(kodeCustomer); // Isi input hidden dengan kode customer
        $('#customer').val(`${kodeCustomer} - ${namaCustomer}`); // Tampilkan kode dan nama customer di input utama
        $('#customerDropdown').hide();
    });

    // Search Sales
    $('#sales').on('input', function () {
        const keyword = $(this).val();
        if (keyword.length > 0) {
            $.ajax({
                url: "{{ route('api.sales.search') }}",
                method: "GET",
                data: { keyword },
                success: function (data) {
                    let dropdown = '';
                    if (data.length > 0) {
                        data.forEach(sales => {
                            dropdown += `<a class="dropdown-item sales-item" data-kode="${sales.kode_stok_owner}" data-name="${sales.keterangan}">${sales.kode_stok_owner} - ${sales.keterangan}</a>`;
                        });
                    } else {
                        dropdown = '<a class="dropdown-item disabled">Tidak ada sales ditemukan</a>';
                    }
                    $('#salesDropdown').html(dropdown).show();
                },
                error: function () {
                    alert('Terjadi kesalahan saat mencari sales.');
                }
            });
        } else {
            $('#salesDropdown').hide();
        }
    });

    // Select Sales
    $(document).on('click', '.sales-item', function () {
        const kodeSales = $(this).data('kode'); // Ambil kode sales
        const namaSales = $(this).data('name'); // Ambil nama sales
        $('#kode_sales').val(kodeSales); // Isi input hidden dengan kode sales
        $('#sales').val(`${kodeSales}`); // Tampilkan kode dan nama sales di input utama
        $('#salesDropdown').hide();
    });

    // Hide dropdown when clicking outside
    $(document).click(function (e) {
        if (!$(e.target).closest('#customer, #customerDropdown').length) {
            $('#customerDropdown').hide();
        }
        if (!$(e.target).closest('#sales, #salesDropdown').length) {
            $('#salesDropdown').hide();
        }
    });


    // Add new customer
    $('#addCustomerForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: "{{ route('customers.store') }}",
            method: "POST",
            data: formData,
            success: function (response) {
                alert('Customer berhasil ditambahkan!');
                $('#addCustomerModal').modal('hide');
                $('#addCustomerForm')[0].reset();
            },
            error: function (xhr) {
                alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
            }
        });
    });

    // Show add customer modal
    $('#findCustomer').click(function () {
        $('#addCustomerModal').modal('show');
    });

    // Toggle discount and DP inputs
    $('#discount_checkbox').change(function() {
        $('#discount_percent').prop('disabled', !this.checked);
        calculateTotals();
    });

    $('#disc_rp_checkbox').change(function() {
        $('#disc_rp').prop('disabled', !this.checked);
        calculateTotals();
    });

    $('#ppn_checkbox').change(function() {
        calculateTotals();
    });

    $('#dp_checkbox').change(function() {
        $('#dp_amount').prop('disabled', !this.checked);
        calculateTotals();
    });

    // Calculate input changes
    $('#discount_percent, #disc_rp, #dp_amount').on('input', function() {
        calculateTotals();
    });

    $('#saveItemBtn').click(function() {
        const kodeBarang = $('#kode_barang').val();
        const namaBarang = $('#nama_barang').val();
        const keterangan = $('#keterangan').val();
        const harga = parseInt($('#harga').val()) || 0;
        const panjang = parseInt($('#panjang').val()) || 0;
        const lebar = parseInt($('#lebar').val()) || 0;
        const qty = parseInt($('#quantity').val()) || 0;
        const diskon = parseInt($('#diskon').val()) || 0;

        if (!kodeBarang || !namaBarang || !harga || !qty) {
            alert('Mohon lengkapi data barang!');
            return;
        }

        $.ajax({
            url: `/panel/${kodeBarang}`,
            method: 'GET',
            success: function(panel) {
                const proporsi = (panjang / panel.length);
                const price = panel.price * proporsi
                const total = price * qty;

                const newItem = {
                    kodeBarang,
                    namaBarang,
                    keterangan,
                    harga: panel.price,
                    panjang,
                    lebar,
                    qty,
                    diskon,
                    total
                };

                items.push(newItem);
                renderItems();
                calculateTotals();

                // Reset form and close modal
                $('#addItemForm')[0].reset();
                $('#addItemModal').modal('hide');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            },
            error: function() {
                alert('Gagal mengambil data panel.');
            }
        });
    });

    // Function to render items table
    function renderItems() {
        const tbody = $('#itemsList');
        tbody.empty();

        items.forEach((item, index) => {
            tbody.append(`
                <tr>
                    <td>${item.kodeBarang}</td>
                    <td>${item.namaBarang}</td>
                    <td>${item.keterangan}</td>
                    <td class="text-right">${formatCurrency(item.harga)}</td>
                    <td>${item.panjang}</td>
                    <td>${item.qty}</td>
                    <td class="text-right">${formatCurrency(item.total)}</td>
                    <td class="text-right">${item.diskon}%</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        // Remove item handling
        $('.remove-item').click(function() {
            const index = $(this).data('index');
            items.splice(index, 1);
            renderItems();
            calculateTotals();
        });

        $('#addItemModal').modal('hide');
    }

    // Calculate all totals
    function calculateTotals() {
        // Calculate subtotal
        const subtotal = items.reduce((sum, item) => sum + item.total, 0);
        $('#total').val(formatCurrency(subtotal));

        // Calculate discount
        let discountAmount = 0;
        if ($('#discount_checkbox').is(':checked')) {
            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            discountAmount = (subtotal * discountPercent) / 100;
        }
        $('#discount_amount').val(formatCurrency(discountAmount));

        // Calculate additional discount
        let discRp = 0;
        if ($('#disc_rp_checkbox').is(':checked')) {
            discRp = parseFloat($('#disc_rp').val()) || 0;
        }

        // Calculate PPN
        let ppnAmount = 0;
        if ($('#ppn_checkbox').is(':checked')) {
            ppnAmount = ((subtotal - discountAmount - discRp) * 0.11); // Using 11% for PPN
        }
        $('#ppn_amount').val(formatCurrency(ppnAmount));

        // Calculate DP
        let dpAmount = 0;
        if ($('#dp_checkbox').is(':checked')) {
            dpAmount = parseFloat($('#dp_amount').val()) || 0;
        }

        // Calculate grand total
        grandTotal = subtotal - discountAmount - discRp + ppnAmount - dpAmount;
        $('#grand_total').val(formatCurrency(grandTotal));
    }

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }

    // Save transaction
    $('#saveTransaction').click(function() {
        if (confirm('Apakah Anda Yakin ingin menyimpan?')){
            if (!$('#kode_customer').val()) {
                alert('Pilih customer dari daftar yang tersedia!');
                return;
            }

            if (items.length === 0) {
                alert('Tidak ada barang yang ditambahkan!');
                return;
            }

            const transactionData = {
                no_transaksi: $('#no_transaksi').val(),
                tanggal: $('#tanggal').val(),
                kode_customer: $('#kode_customer').val(),
                sales: $('#sales').val(),
                lokasi: $('#lokasi').val(),
                pembayaran: $('#metode_pembayaran').val(),
                cara_bayar: $('#cara_bayar').val(),
                tanggal_jadi: $('#tanggal_jadi').val(),
                items: items,
                subtotal: $('#total').val().replace(/\./g, ''),
                discount: $('#discount_amount').val().replace(/\./g, ''),
                disc_rp: $('#disc_rp').val(),
                ppn: $('#ppn_amount').val().replace(/\./g, ''),
                dp: $('#dp_amount').val(),
                grand_total: grandTotal
            };
            // Simpan transaksi ke backend
            $.ajax({
                url: "{{ route('transaksi.store') }}",
                method: "POST",
                data: transactionData,
                success: function(response) {
                    // Tampilkan modal invoice
                    $('#invoiceNoTransaksi').text(response.no_transaksi);
                    $('#invoiceTanggal').text(response.tanggal);
                    $('#invoiceCustomer').text(response.customer);
                    $('#invoiceGrandTotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.grand_total || 0));

                    // Simpan ID transaksi untuk tombol Print
                    const transactionId = response.id;

                    // Tombol Print
                    $('#printInvoiceBtn').off('click').on('click', function() {
                        window.location.href = "{{ url('transaksi/lihatnota') }}/" + transactionId;
                    });

                    $('#invoiceModal').modal('show');
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan: ' + xhr.responseJSON.message);
                }
            });

            // Tombol Kembali
            $('#backToFormBtn').off('click').on('click', function(){
                $('#invoiceModal').modal('hide');
                $('#transactionForm')[0].reset();
                items = [];
                renderItems();
                calculateTotals();
                window.location.href = "{{ route('transaksi.penjualan') }}";
            });

            // You would typically send this data to your backend using AJAX
            console.log('Transaction data:', transactionData);
        }
    });

    // Button Buat PO
    $('#buatPOBtn').click(function(){
    if (confirm('Simpan sebagai PO (tidak mempengaruhi stok)?')) {
        if (!$('#kode_customer').val()) {
            alert('Pilih customer dari daftar yang tersedia!');
            return;
        }

        if (items.length === 0) {
            alert('Tidak ada barang yang ditambahkan!');
            return;
        }

        // Format items for PO
        const poItems = items.map(item => ({
            kodeBarang: item.kodeBarang,
            namaBarang: item.namaBarang,
            keterangan: item.keterangan || '',
            harga: item.harga,
            panjang: item.panjang || 0,
            qty: item.qty,
            total: item.total,
            diskon: item.diskon || 0
        }));

        // Create the PO data
        const formData = new FormData();
        
        // Add form fields
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('tanggal', $('#tanggal').val());
        formData.append('kode_customer', $('#kode_customer').val());
        formData.append('sales', $('#kode_sales').val());
        formData.append('lokasi', $('#lokasi').val());
        formData.append('pembayaran', $('#metode_pembayaran').val());
        formData.append('cara_bayar', $('#cara_bayar').val());
        formData.append('subtotal', parseFloat($('#total').val().replace(/\./g, '')) || 0);
        formData.append('discount', $('#discount_checkbox').is(':checked') ? parseFloat($('#discount_percent').val()) || 0 : 0);
        formData.append('disc_rupiah', $('#disc_rp_checkbox').is(':checked') ? parseFloat($('#disc_rp').val()) || 0 : 0);
        formData.append('ppn', $('#ppn_checkbox').is(':checked') ? parseFloat($('#ppn_amount').val().replace(/\./g, '')) || 0 : 0);
        formData.append('dp', $('#dp_checkbox').is(':checked') ? parseFloat($('#dp_amount').val()) || 0 : 0);
        formData.append('grand_total', grandTotal);
        
        // Add items as JSON string
        formData.append('items', JSON.stringify(poItems));

        // Send to server
        $.ajax({
            url: "{{ route('purchase-order.store') }}",
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert('PO berhasil disimpan!');
                // Redirect to PO list
                window.location.href = "{{ route('transaksi.purchaseorder') }}";
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                alert('Gagal menyimpan PO: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error'));
            }
        });
    }
});

    // Cancel transaction
    $('#cancelTransaction').click(function() {
        if (confirm('Batalkan transaksi? Semua data akan hilang.')) {
            $('#transactionForm')[0].reset();
            items = [];
            renderItems();
            calculateTotals();
        }
    });
});
</script>
@endsection
