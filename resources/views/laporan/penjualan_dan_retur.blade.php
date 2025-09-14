@extends('layout.Nav')

@section('content')
<section id="laporan-penjualan-retur">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Laporan Penjualan dan Retur Terpisah</h2>
        <div>
            <button class="btn btn-success" onclick="printReport()">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <button class="btn btn-primary" onclick="exportToExcel()">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.penjualan-dan-retur') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-control" id="customer_id" name="customer_id">
                            <option value="">Semua Customer</option>
                            @foreach(\App\Models\Customer::orderBy('nama')->get() as $customer)
                                <option value="{{ $customer->kode_customer }}" 
                                        {{ request('customer_id') == $customer->kode_customer ? 'selected' : '' }}>
                                    {{ $customer->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Filter
                            </button>
                            <a href="{{ route('laporan.penjualan-dan-retur') }}" class="btn btn-secondary">
                                <i class="fas fa-refresh mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($penjualanSummary['total_faktur']) }}</h4>
                            <p class="mb-0">Total Faktur</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">Rp {{ number_format($penjualanSummary['total_omset'], 0, ',', '.') }}</h4>
                            <p class="mb-0">Total Penjualan</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($returSummary['total_retur']) }}</h4>
                            <p class="mb-0">Total Retur</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-undo fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">Rp {{ number_format($netSales, 0, ',', '.') }}</h4>
                            <p class="mb-0">Net Sales</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calculator fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Penjualan -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-shopping-cart mr-2"></i>Data Penjualan
                <span class="badge badge-primary ml-2">{{ $penjualanData->count() }} transaksi</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>Subtotal</th>
                            <th>Diskon</th>
                            <th>PPN</th>
                            <th>Grand Total</th>
                            <th>Status Piutang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjualanData as $transaksi)
                        <tr>
                            <td>{{ $transaksi->no_transaksi }}</td>
                            <td>{{ $transaksi->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $transaksi->customer->nama ?? 'N/A' }}</td>
                            <td class="text-right">Rp {{ number_format($transaksi->subtotal, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($transaksi->discount + $transaksi->disc_rupiah, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($transaksi->ppn, 0, ',', '.') }}</td>
                            <td class="text-right font-weight-bold">Rp {{ number_format($transaksi->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $transaksi->status_piutang == 'lunas' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaksi->status_piutang)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data penjualan</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="thead-dark">
                        <tr>
                            <th colspan="3">Total</th>
                            <th class="text-right">Rp {{ number_format($penjualanSummary['total_subtotal'], 0, ',', '.') }}</th>
                            <th class="text-right">Rp {{ number_format($penjualanSummary['total_diskon'], 0, ',', '.') }}</th>
                            <th class="text-right">Rp {{ number_format($penjualanSummary['total_ppn'], 0, ',', '.') }}</th>
                            <th class="text-right">Rp {{ number_format($penjualanSummary['total_omset'], 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabel Retur -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-undo mr-2"></i>Data Retur
                <span class="badge badge-danger ml-2">{{ $returData->count() }} retur</span>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>No. Retur</th>
                            <th>Tanggal</th>
                            <th>Customer</th>
                            <th>No. Transaksi</th>
                            <th>Total Retur</th>
                            <th>Status</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returData as $retur)
                        <tr>
                            <td>{{ $retur->no_retur }}</td>
                            <td>{{ $retur->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $retur->customer->nama ?? 'N/A' }}</td>
                            <td>{{ $retur->no_transaksi }}</td>
                            <td class="text-right font-weight-bold">Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $retur->status == 'approved' ? 'success' : 'info' }}">
                                    {{ ucfirst($retur->status) }}
                                </span>
                            </td>
                            <td>{{ $retur->alasan_retur ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data retur</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="thead-dark">
                        <tr>
                            <th colspan="4">Total</th>
                            <th class="text-right">Rp {{ number_format($returSummary['total_nilai_retur'], 0, ',', '.') }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Summary by Customer -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Penjualan per Customer</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-right">Faktur</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penjualanByCustomer as $customer)
                                <tr>
                                    <td>{{ $customer['nama_customer'] }}</td>
                                    <td class="text-right">{{ $customer['jumlah_faktur'] }}</td>
                                    <td class="text-right">Rp {{ number_format($customer['total_omset'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Retur per Customer</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-right">Retur</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returByCustomer as $customer)
                                <tr>
                                    <td>{{ $customer['nama_customer'] }}</td>
                                    <td class="text-right">{{ $customer['jumlah_retur'] }}</td>
                                    <td class="text-right">Rp {{ number_format($customer['total_nilai_retur'], 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function printReport() {
    window.print();
}

function exportToExcel() {
    // Implementasi export ke Excel
    alert('Fitur export Excel akan segera tersedia');
}
</script>
@endsection
