@extends('layout.Nav')

<style>
    .scroll-table {
        max-height: 400px; /* Increased height to show more data */
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .scroll-table table {
        margin-bottom: 0;
    }

    .scroll-table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }

    #tbodyCustomer tr {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    #tbodyCustomer tr:hover {
        background-color: #ffe8e8;
    }
    
    #tbodyCustomer tr.selected {
        background-color: #f8d7da !important;
        font-weight: bold;
    }
    
    /* Add loading indicator */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 100;
    }
    
    /* Add a counter column for better visual tracking */
    .counter-col {
        width: 50px;
        text-align: center;
        background-color: #f8f9fa;
    }

    /* Transaction status styling */
    .transaction-canceled {
        background-color: #f8d7da !important;
        color: #721c24;
        text-decoration: line-through;
    }

    .transaction-edited {
        background-color: #fff3cd !important;
        color: #856404;
    }

    .transaction-normal {
        background-color: #d1ecf1 !important;
        color: #0c5460;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .badge-canceled {
        background-color: #dc3545;
        color: white;
    }

    .badge-edited {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-normal {
        background-color: #28a745;
        color: white;
    }

    .transaction-details {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    /* Filter buttons */
    .filter-buttons {
        margin-bottom: 1rem;
    }

    .filter-btn {
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .filter-btn.active {
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
</style>

@section('content')
<div class="container py-2">
    <div class="title-box">
        <h2><i class="fas fa-file-invoice mr-2"></i>Data Penjualan Per Customer</h2>
    </div>

    <!-- CUSTOMER TABLE -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">List Customer</h5>
            <div class="input-group w-50">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari Nama atau Kode Customer">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="position-relative">
                <div id="loadingCustomers" class="loading-overlay" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="scroll-table">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="counter-col">No</th>
                                <th>Kode Customer</th>
                                <th>Nama</th>
                                <th>Alamat</th>
                                <th>HP</th>
                                <th>Telepon</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCustomer">
                            @foreach ($customers as $index => $cust)
                                <tr data-kode="{{ $cust->kode_customer }}" data-nama="{{ $cust->nama }}">
                                    <td class="counter-col">{{ $index + 1 }}</td>
                                    <td>{{ $cust->kode_customer }}</td>
                                    <td>{{ $cust->nama }}</td>
                                    <td>{{ $cust->alamat }}</td>
                                    <td>{{ $cust->hp }}</td>
                                    <td>{{ $cust->telepon }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TRANSAKSI TABLE -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">List Transaksi <span id="selectedCustomerTitle" class="text-primary"></span></h5>
            <div>
                <button id="printTransaksi" class="btn btn-sm btn-info ml-2" disabled>
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Status Filter Buttons -->
            <div class="filter-buttons">
                <button type="button" class="btn btn-outline-primary filter-btn active" data-status="all">
                    <i class="fas fa-list"></i> Semua
                </button>
                <button type="button" class="btn btn-outline-success filter-btn" data-status="normal">
                    <i class="fas fa-check-circle"></i> Normal
                </button>
                <button type="button" class="btn btn-outline-warning filter-btn" data-status="edited">
                    <i class="fas fa-edit"></i> Diedit
                </button>
                <button type="button" class="btn btn-outline-danger filter-btn" data-status="canceled">
                    <i class="fas fa-times-circle"></i> Dibatalkan
                </button>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <label for="startDate">Dari Tanggal</label>
                    <input type="date" id="startDate" class="form-control">
                </div>
                <div class="col-md-3 mb-2">
                    <label for="endDate">Sampai Tanggal</label>
                    <input type="date" id="endDate" class="form-control">
                </div>
                <div class="col-md-3 mb-2 d-flex align-items-end">
                    <button id="applyDateFilter" class="btn btn-primary mr-2">Terapkan</button>
                    <button id="resetFilterTanggal" class="btn btn-secondary">Reset</button>
                </div>
            </div>

            <div class="position-relative">
                <div id="loadingTransaksi" class="loading-overlay" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="scroll-table">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>No Transaksi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th width="200px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyTransaksi">
                            <!-- Transactions will be loaded here when a customer is selected -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- TOTAL PENJUALAN -->
            <div id="totalTransaksiCustomer" class="mt-3 text-right font-weight-bold text-danger">
                <!-- Total penjualan customer terpilih akan muncul di sini -->
            </div>

            <!-- SUMMARY STATISTICS -->
            <div id="transactionSummary" class="mt-3 row">
                <!-- Summary will be displayed here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        let selectedCustomerKode = '';
        let selectedCustomerName = '';
        let allTransactions = [];
        let debounceTimer;
        let currentStatusFilter = 'all';
        
        // Format rupiah
        function formatRupiah(angka) {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Format date for display
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID');
        }

        // Debounce function for search
        function debounce(func, delay) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(func, delay);
        }

        // Status filter buttons
        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            currentStatusFilter = $(this).data('status');
            
            if (selectedCustomerKode) {
                renderTransactions(allTransactions);
            }
        });

        // Efficiently filter customers
        $('#searchInput').on('input', function() {
            const input = $(this).val().toLowerCase();
            debounce(function() {
                filterCustomers(input);
            }, 300);
        });
        
        function filterCustomers(keyword) {
            $('#loadingCustomers').show();
            
            setTimeout(() => {
                let counter = 1;
                $('#tbodyCustomer tr').each(function() {
                    const kode = $(this).data('kode').toString().toLowerCase();
                    const nama = $(this).data('nama').toString().toLowerCase();
                    const visible = kode.includes(keyword) || nama.includes(keyword);
                    
                    $(this).toggle(visible);
                    if (visible) {
                        $(this).find('td:first').text(counter++);
                    }
                });
                $('#loadingCustomers').hide();
            }, 0);
        }
        
        // Clear search input
        $('#clearSearch').on('click', function() {
            $('#searchInput').val('');
            filterCustomers('');
        });

        // Handle customer selection
        $('#tbodyCustomer').on('click', 'tr', function() {
            $('#tbodyCustomer tr').removeClass('selected');
            $(this).addClass('selected');
            
            selectedCustomerKode = $(this).data('kode');
            selectedCustomerName = $(this).data('nama');
            
            $('#selectedCustomerTitle').text(': ' + selectedCustomerName);
            $('#printTransaksi').prop('disabled', false);
            
            loadTransactions();
        });

        // Load transactions for selected customer
        function loadTransactions() {
            if (!selectedCustomerKode) return;
            
            $('#loadingTransaksi').show();
            $('#tbodyTransaksi').empty();
            
            setTimeout(() => {
                let filteredTransactions = [];
                
                @foreach($transactions as $transaction)
                    if ('{{ $transaction->kode_customer }}' === selectedCustomerKode) {
                        filteredTransactions.push({
                            id: {{ $transaction->id }},
                            no_transaksi: '{{ $transaction->no_transaksi }}',
                            tanggal: '{{ $transaction->tanggal }}',
                            grand_total: {{ $transaction->grand_total }},
                            status: '{{ $transaction->status ?? "active" }}',
                            is_edited: {{ $transaction->is_edited ? 'true' : 'false' }},
                            canceled_at: '{{ $transaction->canceled_at ?? '' }}',
                            edited_at: '{{ $transaction->edited_at ?? '' }}',
                            edited_by: '{{ $transaction->edited_by ?? '' }}',
                            canceled_by: '{{ $transaction->canceled_by ?? '' }}',
                            edit_reason: '{{ $transaction->edit_reason ?? '' }}',
                            cancel_reason: '{{ $transaction->cancel_reason ?? '' }}'
                        });
                    }
                @endforeach
                
                allTransactions = filteredTransactions;
                
                renderTransactions(allTransactions);
                $('#loadingTransaksi').hide();
            }, 300);
        }

        function getTransactionStatus(transaction) {
            if (transaction.status === 'canceled' || transaction.canceled_at) {
                return 'canceled';
            } else if (transaction.is_edited) {
                return 'edited';
            }
            return 'normal';
        }

        function getStatusBadge(status, transaction) {
            switch(status) {
                case 'canceled':
                    return `<span class="status-badge badge-canceled">Dibatalkan</span>`;
                case 'edited':
                    return `<span class="status-badge badge-edited">Diedit</span>`;
                default:
                    return `<span class="status-badge badge-normal">Normal</span>`;
            }
        }

        function getTransactionDetails(status, transaction) {
            let details = '';
            
            if (status === 'canceled') {
                details = `<div class="transaction-details">
                    <i class="fas fa-user"></i> Dibatalkan oleh: ${transaction.canceled_by || 'Unknown'}<br>
                    <i class="fas fa-calendar"></i> Pada: ${formatDate(transaction.canceled_at)}<br>
                    <i class="fas fa-comment"></i> Alasan: ${transaction.cancel_reason || 'Tidak ada alasan'}
                </div>`;
            } else if (status === 'edited') {
                details = `<div class="transaction-details">
                    <i class="fas fa-user"></i> Diedit oleh: ${transaction.edited_by || 'Unknown'}<br>
                    <i class="fas fa-calendar"></i> Pada: ${formatDate(transaction.edited_at)}<br>
                    <i class="fas fa-comment"></i> Alasan: ${transaction.edit_reason || 'Tidak ada alasan'}
                </div>`;
            }
            
            return details;
        }
        
        function renderTransactions(transactions) {
            let html = '';
            let normalTotal = 0;
            let editedTotal = 0;
            let canceledTotal = 0;
            let displayedTotal = 0;
            let displayedCount = 0;
            
            if (transactions.length === 0) {
                html = '<tr><td colspan="5" class="text-center">Tidak ada transaksi</td></tr>';
            } else {
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();
                
                transactions.forEach(transaction => {
                    if (checkInDateRange(transaction.tanggal, startDate, endDate)) {
                        const status = getTransactionStatus(transaction);
                        const statusBadge = getStatusBadge(status, transaction);
                        const details = getTransactionDetails(status, transaction);
                        
                        // Track totals by status
                        if (status === 'canceled') {
                            canceledTotal += transaction.grand_total;
                        } else if (status === 'edited') {
                            editedTotal += transaction.grand_total;
                        } else {
                            normalTotal += transaction.grand_total;
                        }
                        
                        // Apply status filter
                        if (currentStatusFilter === 'all' || currentStatusFilter === status) {
                            const rowClass = `transaction-${status}`;
                            const totalDisplay = status === 'canceled' ? 
                                `<span style="text-decoration: line-through;">${formatRupiah(transaction.grand_total)}</span>` : 
                                formatRupiah(transaction.grand_total);
                            
                            html += `
                                <tr class="${rowClass}">
                                    <td>
                                        ${transaction.no_transaksi}
                                        ${details}
                                    </td>
                                    <td>${formatDate(transaction.tanggal)}</td>
                                    <td>${statusBadge}</td>
                                    <td class="text-right">${totalDisplay}</td>
                                    <td>
                                        <a href="{{ route('transaksi.nota', '') }}/${transaction.id}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                        ${getActionButtons(status, transaction)}
                                    </td>
                                </tr>
                            `;
                            
                            if (status !== 'canceled') {
                                displayedTotal += transaction.grand_total;
                            }
                            displayedCount++;
                        }
                    }
                });
            }
            
            $('#tbodyTransaksi').html(html);
            
            // Update totals display
            updateTotalDisplay(displayedTotal, displayedCount);
            
            // Update summary statistics
            updateSummaryStatistics(normalTotal, editedTotal, canceledTotal, transactions.length);
        }

        function getActionButtons(status, transaction) {
            if (status === 'canceled') {
                return `<span class="badge badge-secondary">Dibatalkan</span>`;
            }
            
            return `
                <a href="{{ route('transaksi.edit', '') }}/${transaction.id}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="{{ route('transaksi.cancel', '') }}/${transaction.id}" method="POST" style="display:inline;" 
                      onsubmit="return confirm('Batalkan transaksi ${transaction.no_transaksi}?')">
                    @csrf
                    <input type="hidden" name="from_customer_data" value="1">
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </form>
            `;
        }

        function updateTotalDisplay(total, count) {
            const statusText = currentStatusFilter === 'all' ? 'Semua Status' : 
                              currentStatusFilter === 'normal' ? 'Normal' :
                              currentStatusFilter === 'edited' ? 'Diedit' : 'Dibatalkan';
                              
            $('#totalTransaksiCustomer').html(
                `Total Penjualan <strong>${selectedCustomerName}</strong> (${statusText}): ${formatRupiah(total)} <small>(${count} transaksi)</small>`
            );
        }

        function updateSummaryStatistics(normalTotal, editedTotal, canceledTotal, totalTransactions) {
            const normalCount = allTransactions.filter(t => getTransactionStatus(t) === 'normal').length;
            const editedCount = allTransactions.filter(t => getTransactionStatus(t) === 'edited').length;
            const canceledCount = allTransactions.filter(t => getTransactionStatus(t) === 'canceled').length;
            
            $('#transactionSummary').html(`
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Transaksi Normal</h6>
                            <h4>${normalCount}</h4>
                            <small>${formatRupiah(normalTotal)}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h6 class="card-title">Transaksi Diedit</h6>
                            <h4>${editedCount}</h4>
                            <small>${formatRupiah(editedTotal)}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h6 class="card-title">Transaksi Dibatalkan</h6>
                            <h4>${canceledCount}</h4>
                            <small>${formatRupiah(canceledTotal)}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Aktif</h6>
                            <h4>${normalCount + editedCount}</h4>
                            <small>${formatRupiah(normalTotal + editedTotal)}</small>
                        </div>
                    </div>
                </div>
            `);
        }

        // Apply date filter
        $('#applyDateFilter').on('click', function() {
            if (!selectedCustomerKode) return;
            
            $('#loadingTransaksi').show();
            
            setTimeout(() => {
                renderTransactions(allTransactions);
                $('#loadingTransaksi').hide();
            }, 300);
        });

        // Reset filters
        $('#resetFilterTanggal').click(function() {
            $('#startDate, #endDate').val('');
            
            if (selectedCustomerKode) {
                renderTransactions(allTransactions);
            }
        });

        function checkInDateRange(dateStr, start, end) {
            if (!start && !end) return true;
            
            const date = new Date(dateStr);
            const startDate = start ? new Date(start) : null;
            const endDate = end ? new Date(end) : null;
            
            if (startDate && endDate) {
                return date >= startDate && date <= endDate;
            } else if (startDate) {
                return date >= startDate;
            } else if (endDate) {
                return date <= endDate;
            }
            
            return true;
        }
        
        // Enhanced print functionality
        $('#printTransaksi').on('click', function() {
            if (!selectedCustomerName) return;
            
            const startDate = $('#startDate').val();
            const endDate = $('#endDate').val();
            const dateRange = startDate || endDate ? 
                `${startDate || 'Awal'} hingga ${endDate || 'Sekarang'}` : 
                'Semua Periode';
                
            const statusText = currentStatusFilter === 'all' ? 'Semua Status' : 
                              currentStatusFilter === 'normal' ? 'Normal' :
                              currentStatusFilter === 'edited' ? 'Diedit' : 'Dibatalkan';
                
            let printContent = `
                <h3 style="text-align:center">Data Penjualan Customer: ${selectedCustomerName}</h3>
                <p style="text-align:center">Periode: ${dateRange} | Status: ${statusText}</p>
                <table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color:#f2f2f2">
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            let total = 0;
            let count = 0;
            
            allTransactions.forEach(transaction => {
                if (checkInDateRange(transaction.tanggal, startDate, endDate)) {
                    const status = getTransactionStatus(transaction);
                    
                    if (currentStatusFilter === 'all' || currentStatusFilter === status) {
                        const statusLabel = status === 'canceled' ? 'DIBATALKAN' :
                                          status === 'edited' ? 'DIEDIT' : 'NORMAL';
                        const totalDisplay = status === 'canceled' ? 
                            `<s>${formatRupiah(transaction.grand_total)}</s>` : 
                            formatRupiah(transaction.grand_total);
                            
                        printContent += `
                            <tr>
                                <td>${transaction.no_transaksi}</td>
                                <td>${formatDate(transaction.tanggal)}</td>
                                <td>${statusLabel}</td>
                                <td style="text-align:right">${totalDisplay}</td>
                            </tr>
                        `;
                        
                        if (status !== 'canceled') {
                            total += transaction.grand_total;
                        }
                        count++;
                    }
                }
            });
            
            printContent += `
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold; background-color:#f2f2f2">
                            <td colspan="3" style="text-align:right">Total (${count} transaksi):</td>
                            <td style="text-align:right">${formatRupiah(total)}</td>
                        </tr>
                    </tfoot>
                </table>
            `;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Data Penjualan ${selectedCustomerName}</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
                        th { background-color: #f2f2f2; }
                        s { color: #dc3545; }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 300);
        });

        // Auto-select first customer
        setTimeout(function() {
            const firstRow = $('#tbodyCustomer tr:visible').first();
            if (firstRow.length > 0) {
                firstRow.trigger('click');
            }
        }, 300);
    });
</script>
@endsection