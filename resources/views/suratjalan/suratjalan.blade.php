@extends('layout.Nav')

<style>
    .form-group.position-relative {
        margin-bottom: 1rem;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        width: 100%;
        padding: 0.5rem 0;
        margin: 0;
        background-color: #fff;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
        max-height: 200px;
        overflow-y: auto;
    }

    .dropdown-item {
        display: block;
        width: 100%;
        padding: 0.5rem 1rem;
        clear: both;
        font-weight: 400;
        color: #212529;
        text-align: inherit;
        white-space: nowrap;
        background-color: transparent;
        border: 0;
        text-decoration: none;
    }

    .dropdown-item:hover {
        color: #16181b;
        text-decoration: none;
        background-color: #f8f9fa;
    }

    .dropdown-item.active,
    .dropdown-item:active {
        color: #fff;
        text-decoration: none;
        background-color: #007bff;
    }
</style>

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Buat Surat Jalan</h2>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Data Transaksi</h5>
        </div>
        <div class="card-body">
            <form id="suratjalanForm">
                @csrf
                <div class="row">
                    <!-- Kiri -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_transaksi">No. Surat Jalan</label>
                            <input type="text" class="form-control" id= "no_suratjalan" name="no_suratjalan" value="{{ $noSuratJalan ?? 'SJ-001-00001' }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
                        </div>

                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" readonly>
                        </div>

                        <div class="form-group">
                            <label for="titipan_uang">Titipan Uang</label>
                            <input type="number" class="form-control" id="titipan_uang" name="titipan_uang" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label for="sisa_piutang">Sisa Piutang</label>
                            <input type="number" class="form-control" id="sisa_piutang" name="sisa_piutang" value="0" min="0">
                        </div>

                        <div class="form-group">
                            <label for="alamat_suratjalan">Alamat di Surat Jalan</label>
                            <textarea class="form-control" id="alamat_suratjalan" name="alamat_suratjalan" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div class="col-md-6">
                        <div class="form-group position-relative">
                            <label for="customer">Customer</label>
                            <input type="text" id="customer" name="customer_display" class="form-control" placeholder="Masukkan kode atau nama customer">
                            <input type="hidden" id="kode_customer" name="kode_customer"> <!-- Hanya kode_customer yang dikirim -->
                            <div id="customerDropdown" class="dropdown-menu" style="display: none; position: absolute; width: 100%;"></div>
                        </div>

                        <div class="form-group">
                            <label for="customer">Alamat Customer</label>
                            <input type="text" id="alamatCustomer" name="customer-alamat" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="customer">No HP / Telp Customer</label>
                            <input type="text" id="hpCustomer" name="customer-hp" class="form-control" readonly>
                        </div>

                        <div class="form-group position-relative">
                            <label for="no_transaksi">No Faktur</label>
                            <input type="text" id="no_faktur" name="no_faktur_display" class="form-control" autocomplete="off" placeholder="Masukkan nomor faktur">
                            <input type="hidden" id="no_transaksi" name="no_transaksi">
                            <input type="hidden" id="transaksi_id" name="transaksi_id">
                            <div id="notransaksiList" class="dropdown-menu" style="display: none; position: absolute; width: 100%;"></div>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_transaksi">Tanggal Transaksi</label>
                            <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Transaksi & Items Section -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Rincian Transaksi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="itemsTable">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody id="itemsList">
                        <!-- Dynamic items will be added here -->
                    </tbody>
                </table>
            </div>
            <div class="form-group text-right mt-4">
                <button type="button" class="btn btn-success" id="saveSuratJalan">
                    <i class="fas fa-save"></i> Simpan Surat Jalan
                </button>
                <button type="button" class="btn btn-secondary" id="resetForm">
                    <i class="fas fa-times"></i> Reset
                </button>
            </div>
        </div>
    </div>

<!-- Modal Cetakan Surat Jalan -->
<div class="modal fade" id="printsuratjalanModal" tabindex="-1" role="dialog" aria-labelledby="printsuratjalanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printsuratjalanModalLabel">Surat Jalan Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Print Surat Jalan Content -->
                <div id="suratjalanContent">
                    <h4>No Surat Jalan: <span id="suratjalanNo"></span></h4>
                    <h5>No Faktur: <span id="suratjalanNoTransaksi"></span></h5>
                    <p>Tanggal: <span id="suratjalanTanggal"></span></p>
                    <p>Customer: <span id="suratjalanCustomer"></span></p>
                    <p>Alamat: <span id="suratjalanAlamat"></span></p>
                    <p>Grand Total: <span id="suratjalanGrandTotal"></span></p>
                    <!-- Tambahkan detail lainnya jika diperlukan -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="printInvoiceBtn">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-secondary" href="{{ route('suratjalan.create') }}" id="backToFormBtn">
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
    let transaksiGrandTotal = 0;
    let selectedtransaksiId = 0;

    // Helper function to format date for HTML input
    function formatDateForInput(dateValue) {
        if (!dateValue) {
            return new Date().toISOString().split('T')[0];
        }

        try {
            let dateObj;
            
            // If already in YYYY-MM-DD format
            if (typeof dateValue === 'string' && dateValue.match(/^\d{4}-\d{2}-\d{2}$/)) {
                return dateValue;
            }
            
            // Convert to Date object
            dateObj = new Date(dateValue);
            
            if (isNaN(dateObj.getTime())) {
                throw new Error('Invalid date');
            }
            
            // Format as YYYY-MM-DD
            const year = dateObj.getFullYear();
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const day = String(dateObj.getDate()).padStart(2, '0');
            
            return `${year}-${month}-${day}`;
        } catch (error) {
            console.error('Date formatting error:', error);
            return new Date().toISOString().split('T')[0];
        }
    }

    // Search customer
    $('#customer').on('input', function() {
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
                            dropdown += `<a href='#' class="dropdown-item customer-item" 
                                data-kode="${customer.kode_customer}" 
                                data-name="${customer.nama}"
                                data-alamat="${customer.alamat}"
                                data-hp="${customer.hp}"
                                data-telp="${customer.telepon}">
                                ${customer.kode_customer} - ${customer.nama} - ${customer.alamat} - ${customer.hp}
                            </a>`;
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

    // Select customer from dropdown
    $(document).on('click', '.customer-item', function(e) {
        e.preventDefault();
        const kodeCustomer = $(this).data('kode');
        const namaCustomer = $(this).data('name');
        const alamatCustomer = $(this).data('alamat');
        const hpCustomer = $(this).data('hp');
        const telpCustomer = $(this).data('telp');
        
        $('#customer').val(`${kodeCustomer} - ${namaCustomer}`);
        $('#kode_customer').val(kodeCustomer);
        $('#alamatCustomer').val(alamatCustomer);
        $('#hpCustomer').val(`${hpCustomer} / ${telpCustomer}`);
        $('#alamat_suratjalan').val(alamatCustomer);
        $('#customerDropdown').hide();

        // Setup transaction search after customer is selected
        setupTransactionSearch();
    });

    // Function to setup transaction search
    function setupTransactionSearch() {
        // Remove previous event handlers to avoid duplicates
        $('#no_faktur').off('input.transactionSearch');
        
        $('#no_faktur').on('input.transactionSearch', function() {
            const keyword = $(this).val();
            const kodeCustomer = $('#kode_customer').val();
            
            if (keyword.length > 0 && kodeCustomer.length > 0) {
                $.ajax({
                    url: "{{ route('api.faktur.search') }}",
                    method: "GET",
                    data: { keyword, kode_customer: kodeCustomer },
                    success: function (data) {
                        console.log('Transaction search response:', data); // Debug log
                        
                        let dropdown = '';
                        if (data.length > 0) {
                            data.forEach(transaksi => {
                                const tgl = new Date(transaksi.tanggal);
                                const bulan = tgl.toLocaleString('id-ID', { month: 'long' });
                                const tglFormatted = `${tgl.getDate()} ${bulan} ${tgl.getFullYear()}`;
                                
                                dropdown += `<a href="#" class="dropdown-item transaksi-item" 
                                    data-transaksi_id="${transaksi.id}"
                                    data-no_transaksi="${transaksi.no_transaksi}" 
                                    data-kode_customer="${transaksi.kode_customer}"
                                    data-tanggal_transaksi="${transaksi.tanggal}"
                                    data-grand_total="${transaksi.grand_total}">
                                ${transaksi.no_transaksi} Tanggal ${tglFormatted}</a>`;
                            });
                        } else {
                            dropdown = '<a class="dropdown-item disabled">Tidak ada transaksi ditemukan</a>';
                        }
                        $('#notransaksiList').html(dropdown).show();
                    },
                    error: function (xhr, status, error) {
                        console.error('Transaction search error:', error);
                        alert('Terjadi kesalahan saat mencari transaksi.');
                    }
                });
            } else {
                $('#notransaksiList').hide();
            }
        });
    }

    // Select transaction from dropdown
    $(document).on('click', '.transaksi-item', function(e) {
        e.preventDefault();
        
        const noTransaksi = $(this).data('no_transaksi');
        const transaksiId = $(this).data('transaksi_id');
        const tanggalTransaksi = $(this).data('tanggal_transaksi');
        const grandTotal = $(this).data('grand_total');
        
        // Debug logging
        console.log('Selected transaction data:', {
            noTransaksi,
            transaksiId,
            tanggalTransaksi,
            grandTotal
        });

        // Update global variables
        transaksiGrandTotal = grandTotal;
        selectedtransaksiId = transaksiId;

        // Format and set the date
        const formattedDate = formatDateForInput(tanggalTransaksi);
        console.log('Formatted date:', formattedDate);

        // Populate form fields
        $('#no_transaksi').val(noTransaksi);
        $('#no_faktur').val(noTransaksi);
        $('#transaksi_id').val(transaksiId);
        
        // Set date with enhanced handling
        const $dateInput = $('#tanggal_transaksi');
        $dateInput.prop('readonly', false); // Temporarily enable if readonly
        $dateInput.val(formattedDate);
        
        // Verify the date was set
        console.log('Date input value after setting:', $dateInput.val());
        
        // Re-enable readonly if it was originally readonly
        // $dateInput.prop('readonly', true);
        
        $('#notransaksiList').hide();

        // Fetch transaction items
        fetchTransactionItems(transaksiId);
    });

    // Function to fetch transaction items
    function fetchTransactionItems(transaksiId) {
        $.ajax({
            url: "{{ url('api/suratjalan/transaksiitem/') }}/" + transaksiId,
            method: "GET",
            success: function(response) {
                console.log('Transaction items response:', response);
                
                let html = '';
                items = [];
                
                response.forEach(function(item, index) {
                    html += `<tr>
                        <td>${index+1}</td>
                        <td>${item.kode_barang}</td>
                        <td class="editable-nama-barang" data-index="${index}">${item.keterangan}</td>
                        <td>${item.qty}</td>
                    </tr>`;
                    
                    items.push({
                        transaksi_item_id: item.id, // ✅ Add this line!
                        kode_barang: item.kode_barang,
                        nama_barang: item.keterangan,
                        panjang: item.panjang,
                        qty: item.qty,
                    });
                });
                
                $('#itemsList').html(html);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching transaction items:', error);
                alert('Terjadi kesalahan saat mengambil detail transaksi.');
            }
        });
    }


    // Editable nama barang functionality
    $(document).on('dblclick', '.editable-nama-barang', function() {
        const $td = $(this);
        const index = $td.data('index');
        const currentValue = $td.text();
        
        if ($td.find('input').length > 0) return;

        const $input = $('<input type="text" class="form-control form-control-sm" />')
            .val(currentValue)
            .css('min-width', '120px');

        $td.html($input);
        $input.focus();

        function saveEdit() {
            const newValue = $input.val();
            $td.text(newValue);

            if (typeof items[index] !== 'undefined') {
                items[index].nama_barang = newValue;
            }

            $(document).off('mousedown.namaBarangEdit');
        }

        $input.on('blur', saveEdit);
        $input.on('keydown', function(e) {
            if (e.key === 'Enter') {
                $input.blur();
            }
        });

        $(document).on('mousedown.namaBarangEdit', function(event) {
            if (!$input.is(event.target) && $input.has(event.target).length === 0) {
                $input.blur();
            }
        });
    });

    // Cleanup event handler
    $(document).on('blur', '.editable-nama-barang input', function() {
        $(document).off('mousedown.namaBarangEdit');
    });

    // Save Surat Jalan
    $('#saveSuratJalan').click(function(){
        if (confirm('Apakah anda yakin ingin menyimpan surat jalan ini?')){
            // Validation
            if(!$('#no_suratjalan').val() || !$('#kode_customer').val() || !$('#no_transaksi').val()) {
                alert('Silakan lengkapi semua field yang diperlukan.');
                return;
            }
            
            if (items.length === 0) {
                alert('Tidak ada barang dalam transaksi.');
                return;
            }

            const formattedItems = items.map(item => {
                return {
                    transaksi_id: item.transaksi_item_id,
                    no_transaksi: $('#no_transaksi').val(),
                    kode_barang: item.kode_barang,
                    nama_barang: item.nama_barang,
                    qty: item.qty
                };
            });

            const suratjalanData = {
                no_suratjalan: $('#no_suratjalan').val(),
                tanggal: $('#tanggal').val(),
                kode_customer: $('#kode_customer').val(),
                alamat_suratjalan: $('#alamat_suratjalan').val() || 'default',
                no_transaksi: $('#no_transaksi').val(),
                tanggal_transaksi: $('#tanggal_transaksi').val(),
                titipan_uang: $('#titipan_uang').val(),
                sisa_piutang: $('#sisa_piutang').val(),
                grand_total: transaksiGrandTotal,
                items: formattedItems
            };

            console.log('Saving surat jalan data:', suratjalanData);

            $.ajax({
                url: "{{ route('suratjalan.store') }}",
                method: "POST",
                data: suratjalanData,
                success: function(response) {
                    console.log('Save response:', response);
                    
                    $('#suratjalanNo').text(response.no_suratjalan);
                    $('#suratjalanNoTransaksi').text(response.no_transaksi);
                    $('#suratjalanTanggal').text(response.tanggal);
                    $('#suratjalanCustomer').text(response.kode_customer);
                    $('#suratjalanAlamat').text(response.alamat_suratjalan);
                    $('#suratjalanGrandTotal').text(formatCurrency(response.grand_total));

                    let suratJalanId = response.id;

                    $('#printInvoiceBtn').off('click').on('click', function() {
                        window.open("{{ url('suratjalan/detail') }}/" + suratJalanId, '_blank');
                    });

                    $('#backToFormBtn').off('click').on('click', function(){
                        window.location.href = "{{ route('suratjalan.create') }}";
                    });

                    $('#printsuratjalanModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Save error:', xhr.responseJSON);
                    alert('Terjadi kesalahan saat menyimpan surat jalan. ' + (xhr.responseJSON?.message || error));
                }
            });
        }
    });

    // Reset form functionality
    $('#resetForm').click(function() {
        if (confirm('Apakah anda yakin ingin mereset form?')) {
            location.reload();
        }
    });
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
    }

    // Hide dropdowns when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.position-relative').length) {
            $('.dropdown-menu').hide();
        }
    });

});
</script>
@endsection