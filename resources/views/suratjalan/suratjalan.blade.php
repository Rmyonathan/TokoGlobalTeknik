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
            <!-- Step 1: Pilih Faktur -->
            <div id="step1" class="step-content">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Langkah 1: Pilih Faktur</h5>
                    <p class="mb-0">Pilih faktur yang akan dibuatkan Surat Jalan. Hanya faktur yang belum memiliki Surat Jalan yang ditampilkan.</p>
                </div>
                
                <div class="form-group">
                    <label for="select_faktur">Pilih Faktur <span class="text-danger">*</span></label>
                    <select class="form-control" id="select_faktur" name="select_faktur" required>
                        <option value="">-- Pilih Faktur --</option>
                        @foreach($availableTransactions as $transaction)
                            <option value="{{ $transaction->id }}" 
                                    data-no-transaksi="{{ $transaction->no_transaksi }}"
                                    data-kode-customer="{{ $transaction->kode_customer }}"
                                    data-customer-name="{{ $transaction->customer->nama ?? 'N/A' }}"
                                    data-customer-alamat="{{ $transaction->customer->alamat ?? 'N/A' }}"
                                    data-customer-hp="{{ $transaction->customer->hp ?? 'N/A' }}"
                                    data-customer-telp="{{ $transaction->customer->telepon ?? 'N/A' }}"
                                    data-tanggal="{{ $transaction->tanggal }}"
                                    data-grand-total="{{ $transaction->grand_total }}">
                                {{ $transaction->no_transaksi }} - {{ $transaction->customer->nama ?? 'N/A' }} ({{ \Carbon\Carbon::parse($transaction->tanggal)->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group text-right">
                    <button type="button" class="btn btn-primary" id="nextToStep2" disabled>
                        <i class="fas fa-arrow-right"></i> Lanjut ke Langkah 2
                    </button>
                </div>
            </div>

            <!-- Step 2: Form Surat Jalan -->
            <div id="step2" class="step-content" style="display: none;">
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Langkah 2: Data Surat Jalan</h5>
                    <p class="mb-0">Lengkapi data Surat Jalan berdasarkan faktur yang dipilih.</p>
                </div>
                
                <form id="suratjalanForm">
                    @csrf
                    <div class="row">
                        <!-- Kiri -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_suratjalan">No. Referensi</label>
                                <input type="text" class="form-control" id="no_suratjalan" name="no_suratjalan" value="{{ $noSuratJalan ?? 'SJ-001-00001' }}" readonly style="background-color: #ffc107; color: #000; font-weight: bold;">
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
                            <div class="form-group">
                                <label for="customer_display">Customer</label>
                                <input type="text" id="customer_display" name="customer_display" class="form-control" readonly>
                                <input type="hidden" id="kode_customer" name="kode_customer">
                            </div>

                            <div class="form-group">
                                <label for="alamatCustomer">Alamat Customer</label>
                                <input type="text" id="alamatCustomer" name="customer-alamat" class="form-control" readonly>
                            </div>

                            <div class="form-group">
                                <label for="hpCustomer">No HP / Telp Customer</label>
                                <input type="text" id="hpCustomer" name="customer-hp" class="form-control" readonly>
                            </div>

                            <div class="form-group">
                                <label for="no_transaksi_display">No Faktur</label>
                                <input type="text" id="no_transaksi_display" name="no_transaksi_display" class="form-control" readonly>
                                <input type="hidden" id="no_transaksi" name="no_transaksi">
                                <input type="hidden" id="transaksi_id" name="transaksi_id">
                            </div>

                            <div class="form-group">
                                <label for="tanggal_transaksi">Tanggal Transaksi</label>
                                <input type="date" class="form-control" id="tanggal_transaksi" name="tanggal_transaksi" readonly>
                            </div>
                        </div>
                    </div>
                </form>
                
                <div class="form-group text-right">
                    <button type="button" class="btn btn-secondary" id="backToStep1">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-primary" id="nextToStep3">
                        <i class="fas fa-arrow-right"></i> Lanjut ke Langkah 3
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Rincian Transaksi & Items Section -->
    <div id="step3" class="step-content" style="display: none;">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Langkah 3: Rincian Transaksi
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Perhatian:</strong> Surat Jalan ini dibuat berdasarkan faktur yang dipilih. Data barang akan diambil otomatis dari faktur tersebut.
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="itemsTable">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody id="itemsList">
                            <!-- Dynamic items will be added here -->
                        </tbody>
                    </table>
                </div>
                
                <div class="form-group text-right mt-4">
                    <button type="button" class="btn btn-secondary" id="backToStep2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-success" id="saveSuratJalan">
                        <i class="fas fa-save"></i> Simpan Surat Jalan
                    </button>
                    <button type="button" class="btn btn-warning" id="resetForm">
                        <i class="fas fa-times"></i> Reset
                    </button>
                </div>
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
    let selectedTransaction = null;

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

    // Step 1: Handle faktur selection
    $('#select_faktur').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            // Enable next button
            $('#nextToStep2').prop('disabled', false);
            
            // Store selected transaction data
            selectedTransaction = {
                id: selectedOption.val(),
                no_transaksi: selectedOption.data('no-transaksi'),
                kode_customer: selectedOption.data('kode-customer'),
                customer_name: selectedOption.data('customer-name'),
                customer_alamat: selectedOption.data('customer-alamat'),
                customer_hp: selectedOption.data('customer-hp'),
                customer_telp: selectedOption.data('customer-telp'),
                tanggal: selectedOption.data('tanggal'),
                grand_total: selectedOption.data('grand-total')
            };
        } else {
            $('#nextToStep2').prop('disabled', true);
            selectedTransaction = null;
        }
    });

    // Step 1 to Step 2 navigation
    $('#nextToStep2').on('click', function() {
        if (selectedTransaction) {
            // Fill form data
            $('#customer_display').val(`${selectedTransaction.kode_customer} - ${selectedTransaction.customer_name}`);
            $('#kode_customer').val(selectedTransaction.kode_customer);
            $('#alamatCustomer').val(selectedTransaction.customer_alamat);
            $('#hpCustomer').val(`${selectedTransaction.customer_hp} / ${selectedTransaction.customer_telp}`);
            $('#alamat_suratjalan').val(selectedTransaction.customer_alamat);
            $('#no_transaksi_display').val(selectedTransaction.no_transaksi);
            $('#no_transaksi').val(selectedTransaction.no_transaksi);
            $('#transaksi_id').val(selectedTransaction.id);
            $('#tanggal_transaksi').val(formatDateForInput(selectedTransaction.tanggal));
            
            // Show step 2
            $('#step1').hide();
            $('#step2').show();
        }
    });

    // Step 2 to Step 1 navigation (back)
    $('#backToStep1').on('click', function() {
        $('#step2').hide();
        $('#step1').show();
    });

    // Step 2 to Step 3 navigation
    $('#nextToStep3').on('click', function() {
        if (selectedTransaction) {
            // Load transaction items
            loadTransactionItems(selectedTransaction.id);
            
            // Show step 3
            $('#step2').hide();
            $('#step3').show();
        }
    });

    // Step 3 to Step 2 navigation (back)
    $('#backToStep2').on('click', function() {
        $('#step3').hide();
        $('#step2').show();
    });

    // Function to load transaction items
    function loadTransactionItems(transaksiId) {
        $.ajax({
            url: "{{ route('api.transaksi.items', '') }}/" + transaksiId,
            method: "GET",
            success: function (data) {
                console.log('Transaction items loaded:', data);
                
                items = data.map(item => {
                    console.log('Raw item from API:', item);
                    return {
                        kodeBarang: item.kode_barang,
                        namaBarang: item.nama_barang,
                        qty: item.qty,
                        satuan: 'Pcs', // Default satuan untuk Surat Jalan
                        total: 0 // No price in Surat Jalan
                    };
                });
                
                updateItemsTable();
            },
            error: function (xhr, status, error) {
                console.error('Error loading transaction items:', error);
                alert('Terjadi kesalahan saat memuat data barang transaksi.');
            }
        });
    }

    // Function to update items table
    function updateItemsTable() {
        const tbody = $('#itemsList');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center text-muted">Tidak ada barang</td></tr>');
            return;
        }
        
        items.forEach((item, index) => {
            const row = `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${item.kodeBarang}</td>
                    <td>${item.namaBarang}</td>
                    <td class="text-center">${item.qty}</td>
                    <td class="text-center">${item.satuan}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Reset form function
    function resetForm() {
        // Reset all form fields
        $('#select_faktur').val('').trigger('change');
        $('#customer_display').val('');
        $('#kode_customer').val('');
        $('#alamatCustomer').val('');
        $('#hpCustomer').val('');
        $('#alamat_suratjalan').val('');
        $('#no_transaksi_display').val('');
        $('#no_transaksi').val('');
        $('#transaksi_id').val('');
        $('#tanggal_transaksi').val('');
        $('#titipan_uang').val('0');
        $('#sisa_piutang').val('0');
        
        // Reset variables
        items = [];
        selectedTransaction = null;
        transaksiGrandTotal = 0;
        selectedtransaksiId = 0;
        
        // Reset UI
        $('#itemsList').empty();
        $('#itemsList').append('<tr><td colspan="5" class="text-center text-muted">Tidak ada barang</td></tr>');
        
        // Go back to step 1
        $('#step2').hide();
        $('#step3').hide();
        $('#step1').show();
    }

    // Reset button handler
    $('#resetForm').on('click', function() {
        if (confirm('Apakah Anda yakin ingin mereset form? Semua data yang sudah diisi akan hilang.')) {
            resetForm();
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
                console.log('Processing item:', item);
                console.log('item.kodeBarang type:', typeof item.kodeBarang, 'value:', item.kodeBarang);
                
                const formattedItem = {
                    transaksi_id: parseInt($('#transaksi_id').val()),
                    no_transaksi: $('#no_transaksi').val(),
                    kode_barang: item.kodeBarang,
                    nama_barang: item.namaBarang,
                    qty: parseFloat(item.qty),
                    satuan: item.satuan
                };
                
                console.log('Formatted item:', formattedItem);
                return formattedItem;
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
                items: formattedItems
            };

            console.log('Saving surat jalan data:', suratjalanData);
            console.log('Formatted items:', formattedItems);

            $.ajax({
                url: "{{ route('suratjalan.store') }}",
                method: "POST",
                data: suratjalanData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Save response:', response);
                    
                    $('#suratjalanNo').text(response.no_suratjalan);
                    $('#suratjalanNoTransaksi').text(response.no_transaksi);
                    $('#suratjalanTanggal').text(response.tanggal);
                    $('#suratjalanCustomer').text(response.kode_customer);
                    $('#suratjalanAlamat').text(response.alamat_suratjalan);

                    let suratJalanId = response.id;

                    $('#printInvoiceBtn').off('click').on('click', function() {
                        const printUrl = `{{ url('suratjalan/detail') }}/${suratJalanId}?auto_print=1`;
                        window.open(printUrl, '_blank');
                    });

                    $('#backToFormBtn').off('click').on('click', function(){
                        window.location.href = "{{ route('suratjalan.create') }}";
                    });

                    $('#printsuratjalanModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Save error:', xhr);
                    console.error('Response text:', xhr.responseText);
                    console.error('Status:', xhr.status);
                    console.error('Status text:', xhr.statusText);
                    
                    let errorMessage = 'Terjadi kesalahan saat menyimpan surat jalan.';
                    
                    if (xhr.responseJSON) {
                        console.error('Response JSON:', xhr.responseJSON);
                        if (xhr.responseJSON.message) {
                            errorMessage += ' ' + xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            errorMessage += '\n\nValidation errors:\n';
                            for (let field in xhr.responseJSON.errors) {
                                errorMessage += field + ': ' + xhr.responseJSON.errors[field].join(', ') + '\n';
                            }
                        }
                    } else if (xhr.responseText) {
                        errorMessage += ' ' + xhr.responseText;
                    } else {
                        errorMessage += ' ' + error;
                    }
                    
                    alert(errorMessage);
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