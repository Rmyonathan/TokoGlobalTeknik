@extends('layout.Nav')

@section('title', 'View Kas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cash-register"></i> Riwayat Kas
                    </h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form method="GET" action="/viewKas" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="value">Cari Nama/Transaksi:</label>
                                    <input type="text" class="form-control" id="value" name="value" 
                                           value="{{ request('value') }}" placeholder="Masukkan kata kunci...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tanggal_awal">Tanggal Awal:</label>
                                    <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" 
                                           value="{{ request('tanggal_awal') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tanggal_akhir">Tanggal Akhir:</label>
                                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" 
                                           value="{{ request('tanggal_akhir') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Cari
                                        </button>
                                        <a href="/viewKas" class="btn btn-secondary">
                                            <i class="fas fa-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Kredit</h5>
                                    <h3>Rp {{ number_format($totalKredit, 0, ',', '.') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Debit</h5>
                                    <h3>Rp {{ number_format($totalDebit, 0, ',', '.') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Saldo Saat Ini</h5>
                                    <h3>Rp {{ number_format($saldoSaatIni, 0, ',', '.') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Transaksi</h5>
                                    <h3>{{ $totalTransaksi }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('kas.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Entry Kas Manual
                        </a>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#legendModal">
                            <i class="fas fa-info-circle"></i> Keterangan
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama/Transaksi</th>
                                    <th>Deskripsi</th>
                                    <th>Jumlah</th>
                                    <th>Saldo</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gabungan as $index => $item)
                                <tr>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($item['Date'])->format('d/m/Y') }}</small><br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($item['Date'])->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span {{ isset($item['is_kas_canceled']) && $item['is_kas_canceled'] ? 'style=text-decoration:line-through;opacity:0.7' : '' }}>
                                                {{ $item['Name'] }}
                                            </span>
                                            
                                            @if(isset($item['is_kas_canceled']) && $item['is_kas_canceled'])
                                                <span class="badge badge-dark mt-1">
                                                    <i class="fas fa-ban"></i> Kas Dibatalkan
                                                </span>
                                            @endif
                                            
                                            @if(isset($item['kas_type']))
                                                @if($item['kas_type'] == 'Pembatalan')
                                                    <span class="badge badge-danger mt-1">
                                                        <i class="fas fa-times-circle"></i> Pembatalan Transaksi
                                                    </span>
                                                @elseif($item['kas_type'] == 'Edit Transaksi')
                                                    <span class="badge badge-warning mt-1">
                                                        <i class="fas fa-edit"></i> Edit Transaksi
                                                    </span>
                                                @elseif($item['kas_type'] == 'Manual')
                                                    <span class="badge badge-info mt-1">
                                                        <i class="fas fa-hand-paper"></i> Manual
                                                    </span>
                                                @elseif($item['kas_type'] == 'Sistem')
                                                    <span class="badge badge-secondary mt-1">
                                                        <i class="fas fa-cog"></i> Sistem
                                                    </span>
                                                @endif
                                            @endif
                                            
                                            @if(isset($item['is_edited']) && $item['is_edited'])
                                                <span class="badge badge-warning mt-1">
                                                    <i class="fas fa-edit"></i> Transaksi Diedit
                                                </span>
                                            @endif

                                            @if(isset($item['transaction_status']) && $item['transaction_status'] == 'canceled')
                                                <span class="badge badge-danger mt-1">
                                                    <i class="fas fa-ban"></i> Transaksi Dibatalkan
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if(isset($item['Deskripsi']) && !empty($item['Deskripsi']))
                                            <small>{{ $item['Deskripsi'] }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item['Type'] == 'Kredit')
                                            <span class="text-success font-weight-bold {{ isset($item['is_kas_canceled']) && $item['is_kas_canceled'] ? 'text-muted' : '' }}"
                                                  {{ isset($item['is_kas_canceled']) && $item['is_kas_canceled'] ? 'style=text-decoration:line-through;opacity:0.7' : '' }}>
                                                <i class="fas fa-arrow-up"></i> + Rp {{ number_format($item['Grand total'], 0, ',', '.') }}
                                            </span>
                                        @elseif($item['Type'] == 'Debit')
                                            <span class="text-danger font-weight-bold {{ isset($item['is_kas_canceled']) && $item['is_kas_canceled'] ? 'text-muted' : '' }}"
                                                  {{ isset($item['is_kas_canceled']) && $item['is_kas_canceled'] ? 'style=text-decoration:line-through;opacity:0.7' : '' }}>
                                                <i class="fas fa-arrow-down"></i> - Rp {{ number_format($item['Grand total'], 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="@if($item['Saldo'] >= 0) text-success @else text-danger @endif">
                                            Rp {{ number_format($item['Saldo'], 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        @if(isset($item['is_manual']) && $item['is_manual'] && !$item['is_kas_canceled'])
                                            <div class="btn-group" role="group">
                                                <form action="{{ route('kas.delete') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="kas_id" value="{{ $item['id'] }}">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Yakin ingin menghapus entry kas ini?\n\nEntry: {{ $item['Name'] }}\nJumlah: Rp {{ number_format($item['original_amount'], 0, ',', '.') }}\n\nTindakan ini akan mengubah saldo kas!')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('kas.cancel') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="kas_id" value="{{ $item['id'] }}">
                                                    <button type="submit" class="btn btn-sm btn-warning" 
                                                            onclick="return confirm('Yakin ingin membatalkan entry kas ini?\n\nEntry: {{ $item['Name'] }}\nJumlah: Rp {{ number_format($item['original_amount'], 0, ',', '.') }}\n\nEntry akan ditandai sebagai dibatalkan dan saldo akan diperbarui.')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-lock"></i> Otomatis
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>Belum ada data kas yang tersedia.</p>
                                            <a href="{{ route('kas.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Tambah Entry Pertama
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $gabungan->appends(request()->query())->links() }}
                    </div>

                    @if($gabungan->total() > 0)
                    <div class="mt-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Total Transaksi:</strong> {{ $totalTransaksi }}<br>
                                        <strong>Periode:</strong> 
                                        @if(request('tanggal_awal') || request('tanggal_akhir'))
                                            {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d/m/Y') : 'Awal' }}
                                            s/d 
                                            {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d/m/Y') : 'Sekarang' }}
                                        @else
                                            Semua Data
                                        @endif
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <strong>Saldo Akhir:</strong> 
                                        <span class="h4 @if($saldoSaatIni >= 0) text-success @else text-danger @endif">
                                            Rp {{ number_format($saldoSaatIni, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="legendModal" tabindex="-1" role="dialog" aria-labelledby="legendModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legendModalLabel">Keterangan Entry Kas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6>Jenis Entry:</h6>
                <ul class="list-unstyled">
                    <li><span class="badge badge-success mr-2">Transaksi Tunai</span> - Penjualan dengan pembayaran tunai</li>
                    <li><span class="badge badge-info mr-2">Manual</span> - Entry kas yang dibuat manual</li>
                    <li><span class="badge badge-warning mr-2">Edit Transaksi</span> - Perubahan akibat edit transaksi</li>
                    <li><span class="badge badge-danger mr-2">Pembatalan Transaksi</span> - Pembatalan transaksi tunai</li>
                    <li><span class="badge badge-secondary mr-2">Sistem</span> - Entry otomatis dari sistem</li>
                    <li><span class="badge badge-dark mr-2">Kas Dibatalkan</span> - Entry kas yang dibatalkan</li>
                </ul>
                
                <h6>Status Transaksi:</h6>
                <ul class="list-unstyled">
                    <li><span class="badge badge-warning mr-2">Transaksi Diedit</span> - Transaksi pernah diedit</li>
                    <li><span class="badge badge-danger mr-2">Transaksi Dibatalkan</span> - Transaksi dibatalkan</li>
                </ul>

                <h6>Warna Jumlah:</h6>
                <ul class="list-unstyled">
                    <li><span class="text-success mr-2"><i class="fas fa-arrow-up"></i> Hijau</span> - Kredit (Pemasukan)</li>
                    <li><span class="text-danger mr-2"><i class="fas fa-arrow-down"></i> Merah</span> - Debit (Pengeluaran)</li>
                </ul>

                <div class="alert alert-info mt-3">
                    <h6>Catatan Penting:</h6>
                    <ul class="mb-0">
                        <li>Entry dengan coretan tidak dihitung dalam saldo.</li>
                        <li>Pembatalan dan pengeditan transaksi tunai akan menyesuaikan kas secara otomatis.</li>
                        <li>Entry manual yang dibatalkan akan tampil dengan coretan dan nilainya menjadi nol.</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
});
</script>
@endsection

@section('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: .5rem;
    }
    
    .table th {
        font-weight: 600;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.075);
    }
</style>
@endsection