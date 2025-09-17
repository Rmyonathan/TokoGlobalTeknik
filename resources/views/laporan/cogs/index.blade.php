@extends('layout.Nav')

@section('title', 'Laporan COGS/HPP')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator mr-2"></i>
                        Laporan COGS (Cost of Goods Sold) / HPP (Harga Pokok Penjualan)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Informasi:</strong> Laporan COGS/HPP menggunakan metode FIFO (First In First Out) untuk perhitungan harga pokok penjualan yang akurat.
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Laporan COGS Per Periode -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Laporan COGS Per Periode</h5>
                                    <p class="card-text">Analisis COGS dan margin untuk periode tertentu dengan breakdown per barang.</p>
                                    <button class="btn btn-primary" onclick="openCogsReport()">
                                        <i class="fas fa-eye mr-1"></i> Lihat Laporan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Laporan COGS Per Transaksi -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-invoice fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Laporan COGS Per Transaksi</h5>
                                    <p class="card-text">Detail COGS untuk setiap transaksi penjualan dengan analisis margin.</p>
                                    <button class="btn btn-success" onclick="openTransactionReport()">
                                        <i class="fas fa-list mr-1"></i> Lihat Laporan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Laporan COGS Per Barang -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-box fa-3x text-warning mb-3"></i>
                                    <h5 class="card-title">Laporan COGS Per Barang</h5>
                                    <p class="card-text">Analisis COGS dan margin untuk barang tertentu dengan detail batch.</p>
                                    <button class="btn btn-warning" onclick="openProductReport()">
                                        <i class="fas fa-search mr-1"></i> Lihat Laporan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Nilai Persediaan Saat Ini -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-warehouse fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Nilai Persediaan Saat Ini</h5>
                                    <p class="card-text">Hitung nilai persediaan berdasarkan FIFO untuk semua barang atau barang tertentu.</p>
                                    <button class="btn btn-info" onclick="openInventoryValue()">
                                        <i class="fas fa-calculator mr-1"></i> Hitung Nilai
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Grafik COGS -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-3x text-danger mb-3"></i>
                                    <h5 class="card-title">Grafik COGS</h5>
                                    <p class="card-text">Visualisasi trend COGS, penjualan, dan margin dalam bentuk grafik.</p>
                                    <button class="btn btn-danger" onclick="openCogsChart()">
                                        <i class="fas fa-chart-bar mr-1"></i> Lihat Grafik
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Analysis -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-tachometer-alt fa-3x text-secondary mb-3"></i>
                                    <h5 class="card-title">Quick Analysis</h5>
                                    <p class="card-text">Analisis cepat margin dan profitabilitas untuk periode terakhir.</p>
                                    <button class="btn btn-secondary" onclick="openQuickAnalysis()">
                                        <i class="fas fa-bolt mr-1"></i> Analisis Cepat
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal COGS Report -->
<div class="modal fade" id="cogsReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Laporan COGS Per Periode</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cogsReportForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="end_date">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="kode_barang">Barang (Opsional)</label>
                                <select class="form-control select2" id="kode_barang" name="kode_barang">
                                    <option value="">Semua Barang</option>
                                </select>
                                <small class="form-text text-muted">Ketik untuk mencari barang</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i> Generate Laporan
                        </button>
                    </div>
                </form>
                <div id="cogsReportResult" class="mt-4" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Transaction Report -->
<div class="modal fade" id="transactionReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Laporan COGS Per Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="transactionReportForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="trans_start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="trans_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="trans_end_date">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="trans_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-search mr-1"></i> Generate Laporan
                        </button>
                    </div>
                </form>
                <div id="transactionReportResult" class="mt-4" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Product Report -->
<div class="modal fade" id="productReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Laporan COGS Per Barang</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="productReportForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="prod_start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="prod_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="prod_end_date">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="prod_end_date" name="end_date" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="prod_kode_barang">Barang</label>
                                <select class="form-control select2" id="prod_kode_barang" name="kode_barang" required>
                                    <option value="">Pilih Barang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-search mr-1"></i> Generate Laporan
                        </button>
                    </div>
                </form>
                <div id="productReportResult" class="mt-4" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Inventory Value -->
<div class="modal fade" id="inventoryValueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nilai Persediaan Saat Ini</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="inventoryValueForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inv_kode_barang">Barang (Opsional)</label>
                                <select class="form-control select2" id="inv_kode_barang" name="kode_barang">
                                    <option value="">Semua Barang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-calculator mr-1"></i> Hitung Nilai
                        </button>
                    </div>
                </form>
                <div id="inventoryValueResult" class="mt-4" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal COGS Chart -->
<div class="modal fade" id="cogsChartModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Grafik COGS</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cogsChartForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="chart_start_date">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="chart_start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="chart_end_date">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="chart_end_date" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-chart-bar mr-1"></i> Generate Grafik
                        </button>
                    </div>
                </form>
                <div id="cogsChartResult" class="mt-4" style="display: none;">
                    <canvas id="cogsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 with error handling
    try {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                placeholder: 'Pilih barang...',
                allowClear: true,
                ajax: {
                    url: '{{ route("api.cogs.products") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.data.map(function(item) {
                                return {
                                    id: item.kode_barang,
                                    text: item.kode_barang + ' - ' + item.nama_barang
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        } else {
            console.warn('Select2 library not loaded, using regular select');
            // Fallback to regular select if Select2 is not available
            $('.select2').addClass('form-control');
            // Load products for regular select
            loadProductsForSelect();
        }
    } catch (error) {
        console.error('Error initializing Select2:', error);
        // Fallback to regular select
        $('.select2').addClass('form-control');
        // Load products for regular select
        loadProductsForSelect();
    }

    // Set default dates
    const today = new Date();
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
    
    $('#start_date, #trans_start_date, #prod_start_date, #chart_start_date').val(lastMonth.toISOString().split('T')[0]);
    $('#end_date, #trans_end_date, #prod_end_date, #chart_end_date').val(today.toISOString().split('T')[0]);

    // COGS Report Form
    $('#cogsReportForm').on('submit', function(e) {
        e.preventDefault();
        generateCogsReport();
    });

    // Transaction Report Form
    $('#transactionReportForm').on('submit', function(e) {
        e.preventDefault();
        generateTransactionReport();
    });

    // Product Report Form
    $('#productReportForm').on('submit', function(e) {
        e.preventDefault();
        generateProductReport();
    });

    // Inventory Value Form
    $('#inventoryValueForm').on('submit', function(e) {
        e.preventDefault();
        generateInventoryValue();
    });

    // COGS Chart Form
    $('#cogsChartForm').on('submit', function(e) {
        e.preventDefault();
        generateCogsChart();
    });
});

function openCogsReport() {
    $('#cogsReportModal').modal('show');
}

function openTransactionReport() {
    $('#transactionReportModal').modal('show');
}

function openProductReport() {
    $('#productReportModal').modal('show');
}

function openInventoryValue() {
    $('#inventoryValueModal').modal('show');
}

function openCogsChart() {
    $('#cogsChartModal').modal('show');
}

function openQuickAnalysis() {
    // Quick analysis for last 30 days
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    $('#start_date').val(startDate.toISOString().split('T')[0]);
    $('#end_date').val(endDate.toISOString().split('T')[0]);
    
    openCogsReport();
}

function generateCogsReport() {
    const formData = $('#cogsReportForm').serialize();
    
    console.log('Generating COGS report with data:', formData);
    console.log('URL:', '{{ route("laporan.cogs.report") }}');
    
    $.ajax({
        url: '{{ route("laporan.cogs.report") }}',
        method: 'GET',
        data: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        beforeSend: function() {
            console.log('AJAX request started');
            $('#cogsReportResult').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memproses laporan...</div>').show();
        },
        success: function(response) {
            console.log('AJAX response received:', response);
            console.log('Response type:', typeof response);
            console.log('Response success:', response.success);
            
            if (response.success) {
                displayCogsReport(response);
            } else {
                $('#cogsReportResult').html('<div class="alert alert-danger">' + (response.message || 'Tidak ada data yang ditemukan.') + '</div>').show();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', xhr, status, error);
            console.error('Response text:', xhr.responseText);
            $('#cogsReportResult').html('<div class="alert alert-danger">Terjadi kesalahan saat memproses laporan. Status: ' + status + ', Error: ' + error + '</div>').show();
        }
    });
}

function generateTransactionReport() {
    const formData = $('#transactionReportForm').serialize();
    
    $.ajax({
        url: '{{ route("laporan.cogs.transaction") }}',
        method: 'GET',
        data: formData,
        beforeSend: function() {
            $('#transactionReportResult').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memproses laporan...</div>').show();
        },
        success: function(response) {
            if (response.success) {
                displayTransactionReport(response);
            } else {
                $('#transactionReportResult').html('<div class="alert alert-danger">' + response.message + '</div>').show();
            }
        },
        error: function(xhr) {
            $('#transactionReportResult').html('<div class="alert alert-danger">Terjadi kesalahan saat memproses laporan.</div>').show();
        }
    });
}

function generateProductReport() {
    const formData = $('#productReportForm').serialize();
    
    $.ajax({
        url: '{{ route("laporan.cogs.product") }}',
        method: 'GET',
        data: formData,
        beforeSend: function() {
            $('#productReportResult').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memproses laporan...</div>').show();
        },
        success: function(response) {
            if (response.success) {
                displayProductReport(response);
            } else {
                $('#productReportResult').html('<div class="alert alert-danger">' + response.message + '</div>').show();
            }
        },
        error: function(xhr) {
            $('#productReportResult').html('<div class="alert alert-danger">Terjadi kesalahan saat memproses laporan.</div>').show();
        }
    });
}

function generateInventoryValue() {
    const formData = $('#inventoryValueForm').serialize();
    
    $.ajax({
        url: '{{ route("laporan.cogs.inventory") }}',
        method: 'GET',
        data: formData,
        beforeSend: function() {
            $('#inventoryValueResult').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Menghitung nilai persediaan...</div>').show();
        },
        success: function(response) {
            if (response.success) {
                displayInventoryValue(response);
            } else {
                $('#inventoryValueResult').html('<div class="alert alert-danger">' + response.message + '</div>').show();
            }
        },
        error: function(xhr) {
            $('#inventoryValueResult').html('<div class="alert alert-danger">Terjadi kesalahan saat menghitung nilai persediaan.</div>').show();
        }
    });
}

function generateCogsChart() {
    const formData = $('#cogsChartForm').serialize();
    
    $.ajax({
        url: '{{ route("api.cogs.chart-data") }}',
        method: 'GET',
        data: formData,
        beforeSend: function() {
            $('#cogsChartResult').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Memproses grafik...</div>').show();
        },
        success: function(response) {
            if (response.success) {
                displayCogsChart(response);
            } else {
                $('#cogsChartResult').html('<div class="alert alert-danger">' + response.message + '</div>').show();
            }
        },
        error: function(xhr) {
            $('#cogsChartResult').html('<div class="alert alert-danger">Terjadi kesalahan saat memproses grafik.</div>').show();
        }
    });
}

function displayCogsReport(data) {
    let html = `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Transaksi</h5>
                        <h3>${data.summary.total_transaksi}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Total Penjualan</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_penjualan)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Total COGS</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_cogs)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Total Margin</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_margin)}</h3>
                        <small>${data.summary.margin_percentage.toFixed(2)}%</small>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (data.barang_summary && data.barang_summary.length > 0) {
        html += `
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Total Qty</th>
                            <th>Total Penjualan</th>
                            <th>Total COGS</th>
                            <th>Total Margin</th>
                            <th>Margin %</th>
                            <th>COGS/Unit</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.barang_summary.forEach(function(item) {
            html += `
                <tr>
                    <td>${item.kode_barang}</td>
                    <td>${item.nama_barang}</td>
                    <td class="text-right">${item.total_qty}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.total_penjualan)}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.total_cogs)}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.total_margin)}</td>
                    <td class="text-right">${item.margin_percentage.toFixed(2)}%</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.cogs_per_unit)}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
    }

    $('#cogsReportResult').html(html).show();
}

function displayTransactionReport(data) {
    let html = `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Transaksi</h5>
                        <h3>${data.summary.total_transaksi}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Total Penjualan</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_penjualan)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Total COGS</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_cogs)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Total Margin</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_margin)}</h3>
                        <small>${data.summary.margin_percentage.toFixed(2)}%</small>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (data.transaksi_details && data.transaksi_details.length > 0) {
        html += `
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Total Penjualan</th>
                            <th>Total COGS</th>
                            <th>Total Margin</th>
                            <th>Margin %</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.transaksi_details.forEach(function(transaksi) {
            html += `
                <tr>
                    <td>${transaksi.no_transaksi}</td>
                    <td>${new Date(transaksi.tanggal).toLocaleDateString('id-ID')}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(transaksi.total_penjualan)}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(transaksi.total_cogs)}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(transaksi.total_margin)}</td>
                    <td class="text-right">${transaksi.margin_percentage.toFixed(2)}%</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewTransactionDetail(${transaksi.transaksi_id})">
                            <i class="fas fa-eye"></i> Detail
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
    }

    $('#transactionReportResult').html(html).show();
}

function displayProductReport(data) {
    let html = `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Qty</h5>
                        <h3>${data.summary.total_qty}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Total Penjualan</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_penjualan)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Total COGS</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_cogs)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Total Margin</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_margin)}</h3>
                        <small>${data.summary.margin_percentage.toFixed(2)}%</small>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (data.batch_details && data.batch_details.length > 0) {
        html += `
            <h5>Detail Batch</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Batch ID</th>
                            <th>Qty Diambil</th>
                            <th>Harga Modal</th>
                            <th>Total COGS</th>
                            <th>Tanggal Masuk</th>
                            <th>Batch Number</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.batch_details.forEach(function(batch) {
            html += `
                <tr>
                    <td>${batch.batch_id}</td>
                    <td class="text-right">${batch.qty_diambil}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(batch.harga_modal)}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(batch.total_cogs)}</td>
                    <td>${batch.tanggal_masuk ? new Date(batch.tanggal_masuk).toLocaleDateString('id-ID') : '-'}</td>
                    <td>${batch.batch_number || '-'}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
    }

    $('#productReportResult').html(html).show();
}

function displayInventoryValue(data) {
    let html = `
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Qty</h5>
                        <h3>${data.summary.total_qty}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Total Nilai</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.total_value)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Rata-rata Cost</h5>
                        <h3>Rp ${new Intl.NumberFormat('id-ID').format(data.summary.average_cost)}</h3>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (data.barang_details && data.barang_details.length > 0) {
        html += `
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Total Qty</th>
                            <th>Total Nilai</th>
                            <th>Rata-rata Cost</th>
                            <th>Detail Batch</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.barang_details.forEach(function(barang) {
            html += `
                <tr>
                    <td>${barang.kode_barang}</td>
                    <td>${barang.nama_barang}</td>
                    <td class="text-right">${barang.total_qty}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(barang.total_value)}</td>
                    <td class="text-right">Rp ${new Intl.NumberFormat('id-ID').format(barang.total_qty > 0 ? barang.total_value / barang.total_qty : 0)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewBatchDetail('${barang.kode_barang}')">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;
    }

    $('#inventoryValueResult').html(html).show();
}

function displayCogsChart(data) {
    const ctx = document.getElementById('cogsChart').getContext('2d');
    
    // Destroy existing chart if any
    if (window.cogsChartInstance) {
        window.cogsChartInstance.destroy();
    }
    
    window.cogsChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.chart_data.map(item => item.tanggal),
            datasets: [{
                label: 'Penjualan',
                data: data.chart_data.map(item => item.penjualan),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'COGS',
                data: data.chart_data.map(item => item.cogs),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }, {
                label: 'Margin',
                data: data.chart_data.map(item => item.margin),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
    
    $('#cogsChartResult').show();
}

function viewTransactionDetail(transaksiId) {
    // Open transaction detail in new window or modal
    window.open('{{ route("laporan.cogs.detail", ":id") }}'.replace(':id', transaksiId), '_blank');
}

function viewBatchDetail(kodeBarang) {
    // This would open a modal or new page showing batch details
    alert('Detail batch untuk ' + kodeBarang + ' akan ditampilkan di sini');
}

// Function to load products for regular select (fallback)
function loadProductsForSelect() {
    $.ajax({
        url: '{{ route("api.cogs.products") }}',
        method: 'GET',
        data: { search: '' },
        success: function(response) {
            if (response.success && response.data) {
                // Add options to all select2 elements
                $('.select2').each(function() {
                    const select = $(this);
                    // Clear existing options except first one
                    select.find('option:not(:first)').remove();
                    
                    // Add new options
                    response.data.forEach(function(item) {
                        select.append(new Option(item.kode_barang + ' - ' + item.nama_barang, item.kode_barang));
                    });
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading products:', xhr);
        }
    });
}
</script>
@endpush
