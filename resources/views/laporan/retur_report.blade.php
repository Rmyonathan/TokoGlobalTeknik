@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-undo-alt"></i> Laporan Retur Barang
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Tanggal Mulai:</label>
                            <input type="date" class="form-control" id="tanggal_mulai" value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="col-md-3">
                            <label>Tanggal Selesai:</label>
                            <input type="date" class="form-control" id="tanggal_selesai" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label>Tipe Retur:</label>
                            <select class="form-control" id="tipe_retur">
                                <option value="">Semua</option>
                                <option value="penjualan">Retur Penjualan</option>
                                <option value="pembelian">Retur Pembelian</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control" onclick="loadReport()">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>

                    <!-- Single Table for All Returns -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="returTable">
                            <thead>
                                <tr>
                                    <th>Tipe Retur</th>
                                    <th>No Dokumen</th>
                                    <th>Customer/Supplier</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Qty Asli</th>
                                    <th>Qty Return</th>
                                    <th>Qty Sisa</th>
                                    <th>Harga</th>
                                    <th>Total Return</th>
                                    <th>% Return</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data akan diisi via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadReport() {
    const tanggalMulai = document.getElementById('tanggal_mulai').value;
    const tanggalSelesai = document.getElementById('tanggal_selesai').value;
    const tipeRetur = document.getElementById('tipe_retur').value;

    // Load combined retur data
    loadCombinedReturData(tanggalMulai, tanggalSelesai, tipeRetur);
}

function loadCombinedReturData(tanggalMulai, tanggalSelesai, tipeRetur) {
    console.log('Loading combined retur data...', tanggalMulai, tanggalSelesai, tipeRetur);
    
    const tbody = document.querySelector('#returTable tbody');
    tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted">Loading data...</td></tr>';
    
    // Load both retur penjualan and retur pembelian data
    const promises = [];
    
    // Load retur penjualan if tipeRetur is empty or 'penjualan'
    if (!tipeRetur || tipeRetur === 'penjualan') {
        promises.push(
            fetch(`/api/laporan/retur-penjualan?tanggal_mulai=${tanggalMulai}&tanggal_selesai=${tanggalSelesai}`)
                .then(response => {
                    console.log('Retur penjualan response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Raw retur penjualan data:', data);
                    return data.map(item => {
                        const mappedItem = {
                            ...item,
                            tipe: 'Retur Penjualan',
                            no_dokumen: item.no_transaksi,
                            customer_supplier: item.customer_name || 'N/A',
                            qty_sisa: item.qty_sisa || (item.qty - item.qty_return),
                            harga: item.harga || 0,
                            total: item.total || 0
                        };
                        console.log('Mapped retur penjualan item:', mappedItem);
                        return mappedItem;
                    });
                })
                .catch(error => {
                    console.error('Error loading retur penjualan:', error);
                    return [];
                })
        );
    }
    
    // Load retur pembelian if tipeRetur is empty or 'pembelian'
    if (!tipeRetur || tipeRetur === 'pembelian') {
        promises.push(
            fetch(`/api/laporan/retur-pembelian?tanggal_mulai=${tanggalMulai}&tanggal_selesai=${tanggalSelesai}`)
                .then(response => response.json())
                .then(data => data.map(item => ({
                    ...item,
                    tipe: 'Retur Pembelian',
                    no_dokumen: item.no_pembelian,
                    customer_supplier: item.supplier_name || 'N/A',
                    qty_sisa: item.qty_asli - item.qty_retur,
                    harga: item.harga || 0,
                    total: item.total || 0
                })))
                .catch(error => {
                    console.error('Error loading retur pembelian:', error);
                    return [];
                })
        );
    }
    
    // Wait for all promises to complete
    Promise.all(promises)
        .then(results => {
            // Flatten the results
            const allData = results.flat();
            console.log('Combined data:', allData);
            
            tbody.innerHTML = '';
            
            if (allData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="12" class="text-center text-muted">Tidak ada data retur</td></tr>';
                return;
            }
            
            // Sort by date (newest first)
            allData.sort((a, b) => new Date(b.tanggal || b.tanggal_transaksi) - new Date(a.tanggal || a.tanggal_transaksi));
            
            allData.forEach((item, index) => {
                const persentase = item.qty > 0 ? ((item.qty_return / item.qty) * 100).toFixed(1) : 0;
                const row = `
                    <tr>
                        <td>
                            <span class="badge badge-${item.tipe === 'Retur Penjualan' ? 'primary' : 'info'}">
                                ${item.tipe}
                            </span>
                        </td>
                        <td>${item.no_dokumen}</td>
                        <td>${item.customer_supplier}</td>
                        <td>${item.kode_barang}</td>
                        <td>${item.nama_barang}</td>
                        <td class="text-right">${item.qty || item.qty_asli}</td>
                        <td class="text-right text-warning">${item.qty_return}</td>
                        <td class="text-right text-success">${item.qty_sisa}</td>
                        <td class="text-right">${item.harga > 0 ? 'Rp ' + parseFloat(item.harga).toLocaleString() : '-'}</td>
                        <td class="text-right">${item.total > 0 ? 'Rp ' + parseFloat(item.total).toLocaleString() : '-'}</td>
                        <td class="text-right">${persentase}%</td>
                        <td>
                            <span class="badge badge-${item.status === 'processed' ? 'success' : 'warning'}">
                                ${item.status || 'N/A'}
                            </span>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            
            console.log('Loaded', allData.length, 'retur records');
        })
        .catch(error => {
            console.error('Error loading combined retur data:', error);
            tbody.innerHTML = '<tr><td colspan="12" class="text-center text-danger">Error: ' + error.message + '</td></tr>';
        });
}

// Load data saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, starting loadReport...');
    loadReport();
});
</script>
@endsection
