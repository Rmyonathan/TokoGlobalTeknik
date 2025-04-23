@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-shopping-cart mr-2"></i>Transaksi Pembelian</h2>
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
                            <input type="text" class="form-control" id="no_nota" name="nota" value="BL/04/25-00006" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
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
                            <label for="supplier">Supplier</label>
                            <input type="text" id="supplier" name="kode_supplier" class="form-control" placeholder="Masukkan kode atau nama supplier">
                            <input type="hidden" id="kode_supplier" name="kode_supplier">
                            <div class="input-group-append">
                                <a href="#" class="btn btn-outline-secondary" data-toggle="modal" data-target="#addSupplierModal">Baru</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cabang">Cabang</label>
                            <select class="form-control" id="cabang" name="cabang">
                                <option value="LAMPUNG" selected>LAMPUNG</option>
                                <option value="PALEMBANG">PALEMBANG</option>
                                <option value="JAKARTA">JAKARTA</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="pembayaran">Pembayaran</label>
                            <select class="form-control" id="pembayaran" name="pembayaran">
                                <option value="Tunai">Tunai</option>
                                <option value="Transfer">Transfer</option>
                                <option value="Kredit">Kredit</option>
                                <option value="Debit">Debit</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cara_bayar">Cara Bayar</label>
                            <select class="form-control" id="cara_bayar" name="cara_bayar">
                                <option value="Cash">Cash</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Debit">Debit</option>
                                <option value="Cicilan">Cicilan</option>
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
                                    <input type="checkbox" id="ppn_checkbox">
                                </div>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text">PPN</span>
                            </div>
                            <input type="text" class="form-control text-right" id="ppn_amount" value="0" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cara Bayar</label>
                        <select class="form-control" id="cara_bayar_akhir">
                            <option value="Cash">Cash</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Debit">Debit</option>
                            <option value="Cicilan">Cicilan</option>
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
                        <button type="button" class="btn btn-secondary" id="cancelTransaction">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addSupplierForm" action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">Tambah Supplier Baru</h5>
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
                        <input type="text" class="form-control" id="alamat" name="alamat" required>
                    </div>
                    <div class="form-group">
                        <label for="pemilik">Pemilik</label>
                        <input type="text" class="form-control" id="pemilik" name="pemilik" required>
                    </div>
                    <div class="form-group">
                        <label for="telepon_fax">Telepon/Fax</label>
                        <input type="text" class="form-control" id="telepon_fax" name="telepon_fax" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>
                        <input type="text" class="form-control" id="contact_person" name="contact_person" required>
                    </div>
                    <div class="form-group">
                        <label for="hp_contact_person">HP Contact Person</label>
                        <input type="text" class="form-control" id="hp_contact_person" name="hp_contact_person" required>
                    </div>
                    <div class="form-group">
                        <label for="kode_kategori">Kode Kategori</label>
                        <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" required>
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
<script>
$(document).ready(function() {
    // Initialize variables
    let items = [];
    let grandTotal = 0;

    // Search suppliers - This now uses a direct URL rather than a route name
    $('#supplier').on('input', function () {
        const keyword = $(this).val();
        if (keyword.length > 1) {
            $('#supplier').after('<div id="loading" class="spinner-border spinner-border-sm text-primary" role="status"><span class="sr-only">Loading...</span></div>');
            $.ajax({
                url: "/suppliers/search", // Using direct URL
                method: "GET",
                data: { keyword },
                success: function (data) {
                    $('#loading').remove();
                    let dropdown = '<ul class="dropdown-menu" style="display:block; position:absolute;">';
                    if (data.length > 0) {
                        data.forEach(supplier => {
                            dropdown += `<li class="dropdown-item supplier-item" data-kode="${supplier.kode_supplier}" data-name="${supplier.nama}">${supplier.kode_supplier} - ${supplier.nama}</li>`;
                        });
                    } else {
                        dropdown += '<li class="dropdown-item">Tidak ada supplier ditemukan</li>';
                    }
                    dropdown += '</ul>';
                    $('#supplier').after(dropdown);
                },
                error: function () {
                    $('#loading').remove();
                    alert('Terjadi kesalahan saat mencari supplier.');
                }
            });
        }
    });

    // Select supplier from dropdown
    $(document).on('click', '.supplier-item', function () {
        const kodeSupplier = $(this).data('kode');
        const supplierName = $(this).data('name');
        $('#supplier').val(supplierName);
        $('#kode_supplier').val(kodeSupplier);
        $('.dropdown-menu').remove();
    });

    // Hide dropdown when clicking outside
    $(document).click(function (e) {
        if (!$(e.target).closest('#supplier').length) {
            $('.dropdown-menu').remove();
        }
    });

    // Modified to use standard form submission instead of AJAX
    $('#addSupplierForm').on('submit', function (e) {
        // Form submission handled by normal POST - the form now has action and method attributes
        // We don't need to prevent default here, as we want the normal form submission
        // The page will redirect to suppliers.index after submission
    });
    
    // Toggle discount inputs
    $('#discount_checkbox').change(function() {
        $('#discount_percent').prop('disabled', !this.checked);
        calculateTotals();
    });
    
    $('#ppn_checkbox').change(function() {
        calculateTotals();
    });
    
    // Calculate input changes
    $('#discount_percent').on('input', function() {
        calculateTotals();
    });
    
    // Preview item in modal
    $('#harga, #quantity, #diskon').on('input', function() {
        updateItemPreview();
    });
    
    function updateItemPreview() {
        const kodeBarang = $('#kode_barang').val() || '-';
        const keterangan = $('#keterangan').val() || '-';
        const harga = parseInt($('#harga').val()) || 0;
        const quantity = parseInt($('#quantity').val()) || 0;
        const satuan = $('#satuan').val();
        const diskon = parseInt($('#diskon').val()) || 0;
        
        // Calculate values
        const total = harga * quantity;
        const diskonAmount = (total * diskon) / 100;
        const subTotal = total - diskonAmount;
        
        // Update preview
        const tbody = $('#itemPreview');
        tbody.empty();
        
        tbody.append(`
            <tr>
                <td>${kodeBarang}</td>
                <td>${keterangan}</td>
                <td class="text-right">${formatCurrency(harga)}</td>
                <td>${quantity}</td>
                <td class="text-right">${formatCurrency(total)}</td>
                <td>${satuan}</td>
                <td>${diskon}%</td>
                <td class="text-right">${formatCurrency(subTotal)}</td>
            </tr>
        `);
    }
    
    // Add item to the table
    $('#saveItemBtn').click(function() {
        const kodeBarang = $('#kode_barang').val();
        const namaBarang = $('#nama_barang').val();
        const keterangan = $('#keterangan').val();
        const harga = parseInt($('#harga').val()) || 0;
        const qty = parseInt($('#quantity').val()) || 0;
        const diskon = parseInt($('#diskon').val()) || 0;
        
        if (!kodeBarang || !namaBarang || !harga || !qty) {
            alert('Mohon lengkapi data barang!');
            return;
        }
        
        const total = harga * qty;
        
        const newItem = {
            kodeBarang, namaBarang, keterangan, harga, qty, diskon, total
        };
        
        items.push(newItem);
        renderItems();
        calculateTotals();
        
        // Reset form and close modal
        $('#addItemForm')[0].reset();
        $('#itemPreview').empty();
        $('#addItemModal').modal('hide');
    });
    
    // Item search functionality
    $('#findItem').click(function() {
        // Here you would typically have a function to search for items
        alert('Fitur pencarian barang akan diimplementasikan');
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
                    <td class="text-center">${item.qty}</td>
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
    }
    
    // Calculate all totals
    function calculateTotals() {
        // Calculate subtotal
        const subtotal = items.reduce((sum, item) => {
            const itemDiskon = (item.total * item.diskon) / 100;
            return sum + (item.total - itemDiskon);
        }, 0);
        
        $('#total').val(formatCurrency(subtotal));
        
        // Calculate discount
        let discountAmount = 0;
        if ($('#discount_checkbox').is(':checked')) {
            const discountPercent = parseFloat($('#discount_percent').val()) || 0;
            discountAmount = (subtotal * discountPercent) / 100;
        }
        $('#discount_amount').val(formatCurrency(discountAmount));
        
        // Calculate PPN
        let ppnAmount = 0;
        if ($('#ppn_checkbox').is(':checked')) {
            ppnAmount = ((subtotal - discountAmount) * 11) / 100; // Using 11% for PPN
        }
        $('#ppn_amount').val(formatCurrency(ppnAmount));
        
        // Calculate grand total
        grandTotal = subtotal - discountAmount + ppnAmount;
        $('#grand_total').val(formatCurrency(grandTotal));
    }
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID').format(amount);
    }
    
    // Save transaction
    $('#saveTransaction').click(function() {
        if (confirm('Apakah Anda yakin ingin menyimpan?')) {
            if (!$('#kode_supplier').val()) {
                alert('Pilih supplier dari daftar yang tersedia!');
                return;
            }

            if (items.length === 0) {
                alert('Tidak ada barang yang ditambahkan!');
                return;
            }
            
            const transactionData = {
                nota: $('#no_nota').val(),
                tanggal: $('#tanggal').val(),
                kode_supplier: $('#kode_supplier').val(),
                cabang: $('#cabang').val(),
                pembayaran: $('#pembayaran').val(),
                cara_bayar: $('#cara_bayar').val(),
                items: items,
                subtotal: $('#total').val().replace(/\./g, ''),
                discount: $('#discount_amount').val().replace(/\./g, ''),
                ppn: $('#ppn_amount').val().replace(/\./g, ''),
                grand_total: grandTotal
            };
            
            // You would typically send this data to your backend using AJAX
            console.log('Transaction data:', transactionData);
            
            // Reset form
            $('#transactionForm')[0].reset();
            items = [];
            renderItems();
            calculateTotals();
            alert('Transaksi berhasil disimpan!');
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
    
    // Initialize item preview
    updateItemPreview();
});
</script>
@endsection