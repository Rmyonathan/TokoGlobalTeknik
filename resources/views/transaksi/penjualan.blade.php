@extends('layout.Nav')

@section('content')
<div id="loadingOverlay" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(255,255,255,0.7);backdrop-filter:blur(2px);justify-content:center;align-items:center;">
    <div style="font-size:1.5rem;color:#333;">
        <span class="spinner-border text-primary" role="status"></span>
        <span class="ml-2">Memproses...</span>
    </div>
</div>
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Transaksi Penjualan</h2>
    </div>
    @if(session('warning'))
<script>
    alert("{{ session('warning') }}");
</script>
@endif
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
                            <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" value="{{ $noTransaksi ?? '' }}" placeholder="Masukkan nomor transaksi" style="background-color: #fff; color: #000; font-weight: bold;">
                            <!-- <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" value="{{ $noTransaksi ?? 'KP/WS/0147' }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;"> -->
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
                            <div class="input-group">
                                <input type="text" id="customer" name="customer_display" class="form-control" placeholder="Masukkan kode atau nama customer">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addCustomerModal" title="Tambah Customer Baru">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" id="kode_customer" name="kode_customer">
                            <div id="customerDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                        </div>
                        <div class="form-group">
                            <label for="customer">Alamat Customer</label>
                            <input type="text" id="alamatCustomer" name="customer-alamat" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="customer">No HP / Telp Customer</label>
                            <input type="text" id="hpCustomer" name="customer-hp" class="form-control" readonly>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="form-group">
                            <label for="sales">Sales</label>
                            <input type="text" id="sales" name="sales_display" class="form-control" placeholder="Masukkan kode atau nama sales">
                            <input type="hidden" id="kode_sales" name="sales"> <!-- Hanya kode_sales yang dikirim -->
                            <div id="salesDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
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
                            <th>Harga Jual</th>
                            <th>Qty & Satuan</th>
                            <th>Satuan Besar</th>
                            <th>Total</th>
                            <th>Ongkos Kuli</th>
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

<!-- Simplified Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border: 3px solid black;">
            <form id="addCustomerForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Customer Baru</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Kode customer akan dibuat otomatis
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="hp">HP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="hp" name="hp" required>
                    </div>
                    <div class="form-group">
                        <label for="telepon">Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Customer
                    </button>
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
                <div id="invoiceContent">
                    <h4>No Transaksi: <span id="invoiceNoTransaksi"></span></h4>
                    <p>Tanggal: <span id="invoiceTanggal"></span></p>
                    <p>Customer: <span id="invoiceCustomer"></span></p>
                    <p>Grand Total: <span id="invoiceGrandTotal"></span></p>
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
    function showLoading() {
        $('#loadingOverlay').fadeIn(100);
    }
    function hideLoading() {
        $('#loadingOverlay').fadeOut(100);
    }
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
                    let options = '';
                    // Preselect first option and reflect to summary selector
                    data.forEach(cb => {
                        options += `<option value="${cb.nama}">${cb.nama}</option>`;
                    });
                    $('#cara_bayar').html(options);
                    const first = data.length > 0 ? data[0].nama : '';
                    if (first) {
                        $('#cara_bayar').val(first).trigger('change');
                    }
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
                                dropdown += `<a class="dropdown-item customer-item" 
                                    data-kode="${customer.kode_customer}" 
                                    data-name="${customer.nama}"
                                    data-alamat="${customer.alamat}"
                                    data-hp="${customer.hp}"
                                    data-telp="${customer.telepon}">
                                ${customer.kode_customer} - ${customer.nama} - ${customer.alamat} - ${customer.hp}</a>`;
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
            const alamatCustomer = $(this).data('alamat');
            const hpCustomer = $(this).data('hp');
            const telpCustomer = $(this).data('telp');
            $('#kode_customer').val(kodeCustomer); // Isi input hidden dengan kode customer
            $('#customer').val(`${kodeCustomer} - ${namaCustomer}`); // Tampilkan kode dan nama customer di input utama
            $('#alamatCustomer').val(alamatCustomer);
            $('#hpCustomer').val(`${hpCustomer} / ${telpCustomer}`);
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

        // ===== INTEGRASI SISTEM FAKTUR FIFO =====
        
        // Auto-populate harga dan ongkos kuli saat barang/satuan kecil dipilih
        $(document).on('change', '#kode_barang, #satuanKecil', function() {
            const kodeBarang = $('#kode_barang').val();
            const satuan = $('#satuanKecil').val();
            const customerId = $('#kode_customer').val();
            
            if (kodeBarang && satuan && customerId) {
                getHargaDanOngkos(kodeBarang, satuan, customerId);
            }
        });

        // Get harga dan ongkos kuli via AJAX
        function getHargaDanOngkos(kodeBarang, satuan, customerId) {
            // Cari kode_barang_id dari kode_barang
            $.ajax({
                url: "{{ route('kodeBarang.search') }}",
                method: "GET",
                data: { keyword: kodeBarang },
                success: function(data) {
                    if (data.length > 0) {
                        const kodeBarangData = data[0];
                        const kodeBarangId = kodeBarangData.id;
                        
                        // Sekarang panggil API getHargaDanOngkos
                        $.ajax({
                            url: "{{ route('api.transaksi.harga-ongkos') }}",
                            method: "GET",
                            data: {
                                customer_id: customerId,
                                kode_barang_id: kodeBarangId,
                                satuan: satuan
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Auto-populate harga dan ongkos kuli
                                    $('#harga').val(response.harga_jual);
                                    $('#ongkos_kuli').val(response.ongkos_kuli);
                                    
                                    // Update preview jika ada
                                    updateItemPreview();
                                }
                            },
                            error: function(xhr) {
                                console.log('Error getting harga dan ongkos kuli:', xhr.responseText);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    console.log('Error searching kode barang:', xhr.responseText);
                }
            });
        }

        // Manual get ongkos kuli button
        $(document).on('click', '#getOngkosKuliBtn', function() {
            const kodeBarang = $('#kode_barang').val();
            const satuan = $('#satuanKecil').val();
            const customerId = $('#kode_customer').val();
            
            if (!kodeBarang || !satuan || !customerId) {
                alert('Pilih customer, kode barang, dan satuan terlebih dahulu!');
                return;
            }
            
            getHargaDanOngkos(kodeBarang, satuan, customerId);
        });

        // Update item preview
        function updateItemPreview() {
            const harga = parseInt($('#harga').val()) || 0;
            const qty = parseInt($('#quantity').val()) || 0;
            const diskon = parseInt($('#diskon').val()) || 0;
            const ongkosKuli = parseInt($('#ongkos_kuli').val()) || 0;
            
            const subtotal = harga * qty;
            const diskonAmount = (subtotal * diskon) / 100;
            const total = subtotal - diskonAmount;
            
            // Update preview table
            $('#itemPreview').html(`
                <tr>
                    <td>${$('#kode_barang').val() || '-'}</td>
                    <td>${$('#nama_barang').val() || '-'}</td>
                    <td class="text-right">${formatCurrency(harga)}</td>
                    <td>${$('#panjang').val() || 0}</td>
                    <td>${qty} ${$('#satuanKecil').val() || 'LBR'}</td>
                    <td class="text-right">${formatCurrency(total)}</td>
                    <td>${$('#satuanKecil').val() || 'LBR'}</td>
                    <td>${diskon}%</td>
                    <td class="text-right">${formatCurrency(diskonAmount)}</td>
                    <td class="text-right">${formatCurrency(total)}</td>
                </tr>
            `);
        }

        // Update preview saat input berubah
        $('#harga, #quantity, #diskon, #ongkos_kuli').on('input', function() {
            updateItemPreview();
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


        $('#addCustomerForm').on('submit', function (e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            
            $.ajax({
                url: "{{ route('customers.store') }}",
                method: "POST",
                data: formData,
                success: function (response) {
                    alert('Customer berhasil ditambahkan!');
                    
                    // Auto-populate customer field
                    const customer = response.customer;
                    $('#kode_customer').val(customer.kode_customer);
                    $('#customer').val(`${customer.kode_customer} - ${customer.nama}`);
                    $('#alamatCustomer').val(customer.alamat);
                    $('#hpCustomer').val(`${customer.hp}${customer.telepon ? ' / ' + customer.telepon : ''}`);
                    
                    $('#addCustomerModal').modal('hide');
                    $('#addCustomerForm')[0].reset();
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat menyimpan customer.');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
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
        const harga = parseInt($('#harga').val()) || 0;  // Use edited price
        const panjang = parseInt($('#panjang').val()) || 0;
        const lebar = parseInt($('#lebar').val()) || 0;
        const qty = parseInt($('#quantity').val()) || 0;
        const satuan = $('#satuanKecil').val();
        const satuanBesar = $('#satuanBesar').val();
        const diskon = parseInt($('#diskon').val()) || 0;
        const ongkosKuli = parseInt($('#ongkos_kuli').val()) || 0;

        if (!kodeBarang || !namaBarang || !harga || !qty) {
            alert('Mohon lengkapi data barang!');
            return;
        }

        // Calculate total using the edited harga value
        const total = harga * qty;

        const newItem = {
            kodeBarang,
            namaBarang,
            keterangan,
            harga: harga,  // Use the edited harga value
            panjang,
            lebar,
            qty,
            satuan,
            satuanBesar,
            diskon,
            ongkosKuli,
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
                        <td>${item.keterangan || '-'}</td>
                        <td class="text-right">${formatCurrency(item.harga)}</td>
                        <td>${item.qty} ${item.satuan || 'LBR'}</td>
                        <td>${item.satuanBesar || 'BOX'}</td>
                        <td class="text-right">${formatCurrency(item.total)}</td>
                        <td class="text-right">${formatCurrency(item.ongkosKuli || 0)}</td>
                        <td class="text-right">${item.diskon || 0}%</td>
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
            pembayaran: $('#metode_pembayaran').val(),
            cara_bayar: $('#cara_bayar').val() || $('#cara_bayar_akhir').val() || 'Tunai',
            tanggal_jadi: $('#tanggal_jadi').val(),
            items: items,
            subtotal: $('#total').val().replace(/\./g, ''),
            discount: $('#discount_amount').val().replace(/\./g, ''),
            disc_rp: $('#disc_rp').val(),
            ppn: $('#ppn_amount').val().replace(/\./g, ''),
            dp: $('#dp_amount').val(),
            grand_total: grandTotal
        };

        showLoading();

        // Simpan transaksi ke backend
        $.ajax({
            url: "{{ route('transaksi.store') }}",
            method: "POST",
            data: transactionData,
            success: function(response) {
                hideLoading();
                // Tampilkan modal invoice
                $('#invoiceNoTransaksi').text(response.no_transaksi);
                $('#invoiceTanggal').text(response.tanggal);
                $('#invoiceCustomer').text(response.customer);
                $('#invoiceGrandTotal').text('Rp ' + new Intl.NumberFormat('id-ID').format(response.grand_total || 0));

                // Simpan ID transaksi untuk tombol Print
                const transactionId = response.id;

                // Tombol Print
                $('#printInvoiceBtn').off('click').on('click', function() {
                    // --- PERUBAHAN DI SINI ---
                    // Buka nota di tab baru dengan parameter auto_print=1
                    const printUrl = `{{ url('transaksi/lihatnota') }}/${transactionId}?auto_print=1`;
                    window.open(printUrl, '_blank');
                });

                $('#invoiceModal').modal('show');
            },
            error: function(xhr) {
                hideLoading();
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
                showLoading();
                $.ajax({
                    url: "{{ route('purchase-order.store') }}", // Ubah ini ke route PO
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        hideLoading();
                        alert('PO berhasil disimpan!');
                        // Opsional: reset form
                        $('#transactionForm')[0].reset();
                        items = [];
                        renderItems();
                        calculateTotals();
                        window.location.href = "{{ route('transaksi.penjualan') }}";
                    },
                    error: function(xhr) {
                        hideLoading();
                        alert('Gagal menyimpan PO: ' + xhr.responseJSON.message);
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
