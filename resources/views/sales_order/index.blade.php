@extends('layout.Nav')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Sales Order</h3>
                    <div class="card-tools">
                        <a href="{{ route('sales-order.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Buat Sales Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('sales-order.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="customer_id" class="form-control">
                                    <option value="">Semua Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="salesman_id" class="form-control">
                                    <option value="">Semua Salesman</option>
                                    @foreach($salesmen as $salesman)
                                        <option value="{{ $salesman->id }}" {{ request('salesman_id') == $salesman->id ? 'selected' : '' }}>
                                            {{ $salesman->keterangan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="start_date" class="form-control" placeholder="Tanggal Mulai" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="end_date" class="form-control" placeholder="Tanggal Akhir" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-sm">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('sales-order.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Sales Order Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No. SO</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Salesman</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Cara Bayar</th>
                                    <th>Estimasi</th>
                                    <th>Info Kredit</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesOrders as $so)
                                <tr>
                                    <td>
                                        <strong>{{ $so->no_so }}</strong>
                                    </td>
                                    <td>{{ $so->tanggal->format('d/m/Y') }}</td>
                                    <td>{{ $so->customer->nama }}</td>
                                    <td>{{ optional($so->salesman)->keterangan ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $so->getStatusBadge() }}">
                                            {{ $so->getStatusText() }}
                                        </span>
                                    </td>
                                    <td>Rp {{ number_format($so->grand_total, 0, ',', '.') }}</td>
                                    <td>
                                        @if($so->cara_bayar == 'Kredit')
                                            <span class="badge badge-warning">Kredit ({{ $so->hari_tempo }} hari)</span>
                                        @else
                                            <span class="badge badge-success">Tunai</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($so->tanggal_estimasi)
                                            {{ $so->tanggal_estimasi->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $customer = $so->customer;
                                            $limitKredit = $customer->limit_kredit ?? 0;
                                            $sisaPiutang = $customer->sisa_piutang ?? 0;
                                            $sisaLimit = $limitKredit - $sisaPiutang;
                                            
                                            // Hitung persentase penggunaan kredit
                                            $persentasePenggunaan = $limitKredit > 0 ? ($sisaPiutang / $limitKredit) * 100 : 0;
                                            
                                            // Tentukan status kredit
                                            if ($limitKredit == 0) {
                                                $statusKredit = 'Tunai';
                                                $badgeClass = 'badge-success';
                                            } elseif ($persentasePenggunaan >= 100) {
                                                $statusKredit = 'Limit Habis';
                                                $badgeClass = 'badge-danger';
                                            } elseif ($persentasePenggunaan >= 80) {
                                                $statusKredit = 'Limit Kritis';
                                                $badgeClass = 'badge-warning';
                                            } elseif ($persentasePenggunaan >= 50) {
                                                $statusKredit = 'Limit Sedang';
                                                $badgeClass = 'badge-info';
                                            } else {
                                                $statusKredit = 'Limit Aman';
                                                $badgeClass = 'badge-success';
                                            }
                                        @endphp
                                        
                                        <div class="text-center">
                                            <span class="badge {{ $badgeClass }} mb-1">
                                                {{ $statusKredit }}
                                            </span>
                                            <br>
                                            @if($limitKredit > 0)
                                                <small class="text-muted">
                                                    Limit: Rp {{ number_format($limitKredit, 0, ',', '.') }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    Sisa: Rp {{ number_format($sisaLimit, 0, ',', '.') }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    {{ number_format($persentasePenggunaan, 1) }}% terpakai
                                                </small>
                                            @else
                                                <small class="text-muted">Customer Tunai</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('sales-order.show', $so) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($so->canBeCanceled())
                                                <a href="{{ route('sales-order.edit', $so) }}" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            
                                            @if($so->canBeApproved())
                                                @if($so->isCanceled())
                                                    <form action="{{ route('sales-order.reapprove', $so) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve kembali Sales Order ini?')">
                                                            <i class="fas fa-redo"></i> Re-approve
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('sales-order.approve', $so) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Setujui Sales Order ini?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                            
                                            <!-- @if($so->canBeProcessed())
                                                <form action="{{ route('sales-order.process', $so) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Proses Sales Order ini?')">
                                                        <i class="fas fa-cogs"></i>
                                                    </button>
                                                </form>
                                            @endif -->

                                            <!-- {{-- 🔥 Tombol Konversi ke Transaksi (jika status sudah approved) --}}
                                            @if($so->status == 'approved')
                                                <form action="{{ route('sales-order.convert', $so) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-dark btn-sm" onclick="return confirm('Konversi Sales Order ini ke Transaksi Penjualan?')">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </form>
                                            @endif -->
                                            
                                            @if($so->canBeCanceled())
                                                <form action="{{ route('sales-order.cancel', $so) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Batalkan Sales Order ini?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data Sales Order</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $salesOrders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mt-3">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pending</span>
                <span class="info-box-number">{{ $salesOrders->where('status', 'pending')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Approved</span>
                <span class="info-box-number">{{ $salesOrders->where('status', 'approved')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-cogs"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Processed</span>
                <span class="info-box-number">{{ $salesOrders->where('status', 'processed')->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Canceled</span>
                <span class="info-box-number">{{ $salesOrders->where('status', 'canceled')->count() }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
