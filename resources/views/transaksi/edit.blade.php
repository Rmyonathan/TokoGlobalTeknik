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
        <h2><i class="fas fa-edit mr-2"></i>Edit Transaksi Penjualan</h2>
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
                            <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" value="{{ $transaction->no_transaksi }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
                        </div>

                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $transaction->tanggal->format('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="customer">Customer</label>
                            <input type="text" id="customer" name="customer_display" class="form-control" value="{{ $customer }}" placeholder="Masukkan kode atau nama customer">
                            <input type="hidden" id="kode_customer" name="kode_customer" value="{{ $transaction->kode_customer }}">
                            <div id="customerDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                        </div>

                        <div class="form-group">
                            <label for="customer">Alamat Customer</label>
                            <input type="text" id="alamatCustomer" name="customer-alamat" class="form-control" value="{{ $transaction->customer->alamat ?? '' }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="customer">No HP / Telp Customer</label>
                            <input type="text" id="hpCustomer" name="customer-hp" class="form-control" value="{{ $transaction->customer->hp ?? '' }} / {{ $transaction->customer->telepon ?? '' }}" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sales">Sales</label>
                            <input type="text" id="sales" name="sales_display" class="form-control" value="{{ $transaction->sales }}" placeholder="Masukkan kode atau nama sales">
                            <input type="hidden" id="kode_sales" name="sales" value="{{ $transaction->sales }}">
                            <div id="salesDropdown" class="dropdown-menu" style="display: none; position: relative; width: 100%;"></div>
                        </div>

                        <div class="form-group">
                            <label for="metode_pembayaran">Metode Pembayaran</label>
                            <select class="form-control" id="metode_pembayaran" name="metode_pembayaran">
                                <option selected disabled value=""> Pilih Metode Pembayaran</option>
                                <option value="Tunai" {{ $transaction->pembayaran == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                <option value="Non Tunai" {{ $transaction->pembayaran == 'Non Tunai' ? 'selected' : '' }}>Non Tunai</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cara_bayar">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar" name="cara_bayar">
                                <option value="{{ $transaction->cara_bayar }}">{{ $transaction->cara_bayar }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_jadi">Tanggal Jadi</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="tanggal_jadi" name="tanggal_jadi" value="{{ $transaction->tanggal_jadi ? $transaction->tanggal_jadi->format('Y-m-d') : date('Y-m-d') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_reason">Alasan Edit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_reason" name="edit_reason" placeholder="Masukkan alasan perubahan transaksi" required>
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
                        <input type="text" class="form-control text-right" id="total" name="total" readonly value="{{ number_format($transaction->subtotal, 0, ',', '.') }}">
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="discount_checkbox" {{ $transaction->discount > 0 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">Disc(%)</span>
                            </div>
                            <input type="number" class="form-control" id="discount_percent" value="{{ $transaction->discount }}" {{ $transaction->discount > 0 ? '' : 'disabled' }}>
                            <input type="text" class="form-control text-right" id="discount_amount" value="{{ number_format($transaction->discount, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="disc_rp_checkbox" {{ $transaction->disc_rupiah > 0 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">Disc(Rp.)</span>
                            </div>
                            <input type="number" class="form-control" id="disc_rp" value="{{ $transaction->disc_rupiah }}" {{ $transaction->disc_rupiah > 0 ? '' : 'disabled' }}>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="ppn_checkbox" {{ $transaction->ppn > 0 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">PPN</span>
                            </div>
                            <input type="text" class="form-control text-right" id="ppn_amount" value="{{ number_format($transaction->ppn, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" id="dp_checkbox" {{ $transaction->dp > 0 ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">DP</span>
                            </div>
                            <input type="number" class="form-control" id="dp_amount" value="{{ $transaction->dp }}" {{ $transaction->dp > 0 ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cara Bayar</label>
                        <select class="form-control" id="cara_bayar_akhir">
                            <option value="{{ $transaction->cara_bayar }}">{{ $transaction->cara_bayar }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grand Total</label>
                        <input type="text" class="form-control text-right" id="grand_total" readonly value="{{ number_format($transaction->grand_total, 0, ',', '.') }}" style="font-size: 18px; font-weight: bold;">
                    </div>
                    <div class="form-group text-right mt-4">
                        <button type="button" class="btn btn-success" id="updateTransaction">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('transaksi.shownota', $transaction->id) }}" class="btn btn-secondary">
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
                @include('transaksi.add_item')
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
<script>
    function showLoading() {
        $('#loadingOverlay').fadeIn(100);
    }
    function hideLoading() {
        $('#loadingOverlay').fadeOut(100);
    }
    
    $(document).ready(function() {
        // Initialize variables with existing items from the database
        let items = @json($transaction->items->map(function($item) {
            $array = [];
            $array['kodeBarang'] = $item->kode_barang;
            $array['namaBarang'] = $item->nama_barang;
            $array['keterangan'] = $item->keterangan;
            $array['harga'] = (int)$item->harga;
            $array['panjang'] = (int)$item->panjang;
            $array['lebar'] = (int)$item->lebar;
            $array['qty'] = (int)$item->qty;
            $array['diskon'] = (int)$item->diskon;
            $array['total'] = (int)$item->total;
            return $array;
        })->toArray());
        let grandTotal = {{ $transaction->grand_total }};
        
        // Render items immediately to display existing items
        renderItems();
        calculateTotals();

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
            $('#kode_customer').val(kodeCustomer);
            $('#customer').val(`${kodeCustomer} - ${namaCustomer}`);
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
            const kodeSales = $(this).data('kode');
            const namaSales = $(this).data('name');
            $('#kode_sales').val(kodeSales);
            $('#sales').val(`${kodeSales}`);
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

        // Update transaction
        $('#updateTransaction').click(function() {
            if (confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
                if (!$('#kode_customer').val()) {
                    alert('Pilih customer dari daftar yang tersedia!');
                    return;
                }

                if (items.length === 0) {
                    alert('Tidak ada barang yang ditambahkan!');
                    return;
                }
                
                if (!$('#edit_reason').val().trim()) {
                    alert('Alasan edit harus diisi!');
                    return;
                }

                const transactionData = {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    tanggal: $('#tanggal').val(),
                    kode_customer: $('#kode_customer').val(),
                    sales: $('#sales').val(),
                    pembayaran: $('#metode_pembayaran').val(),
                    cara_bayar: $('#cara_bayar').val(),
                    tanggal_jadi: $('#tanggal_jadi').val(),
                    items: items,
                    subtotal: $('#total').val().replace(/\./g, ''),
                    discount: $('#discount_amount').val().replace(/\./g, ''),
                    disc_rupiah: $('#disc_rp').val(),
                    ppn: $('#ppn_amount').val().replace(/\./g, ''),
                    dp: $('#dp_amount').val(),
                    grand_total: grandTotal,
                    edit_reason: $('#edit_reason').val()
                };

                showLoading();

                // Send data to backend
                $.ajax({
                    url: "{{ route('transaksi.update', $transaction->id) }}",
                    method: "POST",
                    data: transactionData,
                    success: function(response) {
                        hideLoading();
                        alert(response.message || 'Transaksi berhasil diperbarui!');
                        
                        // Use the redirect URL from the response
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = "{{ route('transaksi.shownota', $transaction->id) }}";
                        }
                    },
                    error: function(xhr) {
                        hideLoading();
                        alert('Terjadi kesalahan: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText));
                    }
                });
            }
        });
    });
</script>
@endsection