\@extends('layout.Nav')

@section('content')
<div class="container">
    <h2 class="title-box">Mutasi Stock Barang</h2>

    <!-- Display Barang -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-layer-group mr-2"></i>Display Barang
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
\                            <th>Kode Barang</th>
                            <th>Nama</th>
                            <th>Good Stock</th>
                            <th>Satuan</th>
                            <th>Bad Stock</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocks as $stock)
                            <tr>
                                <td>{{ $stock->kode_barang }}</td>
                                <td>{{ $stock->nama_barang }}</td>
                                <td>{{ number_format($stock->good_stock) }}</td>
                                <td>{{ $stock->satuan }}</td>
                                <td>{{ number_format($stock->bad_stock) }}</td>
                                <td>
                                    <a href="{{ route('stock.mutasi', [
                                        'kolom' => $kolom, 
                                        'value' => $value,
                                        'tanggal_awal' => $tanggal_awal,
                                        'tanggal_akhir' => $tanggal_akhir,
                                        'selected_kode_barang' => $stock->kode_barang
                                    ]) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search mr-1"></i> Lihat Mutasi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data stock</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter mr-2"></i>Filter
        </div>
        <div class="card-body">
            <form class="form-inline" method="GET" action="{{ route('stock.mutasi') }}">
                <div class="form-group mr-2 mb-2">
                    <label class="mr-2">Kolom</label>
                    <select class="form-control" name="kolom">
                        <option value="kode_barang" {{ $kolom == 'kode_barang' ? 'selected' : '' }}>Kode Barang</option>
                        <option value="nama" {{ $kolom == 'nama' ? 'selected' : '' }}>Nama Barang</option>
                    </select>
                </div>
                <div class="form-group mx-2 mb-2">
                    <label class="mr-2">Value</label>
                    <input type="text" name="value" class="form-control" placeholder="Value" value="{{ $value ?? '' }}">
                </div>
                <div class="form-group mx-2 mb-2">
                    <label class="mr-2">Tanggal</label>
                    <input type="date" name="tanggal_awal" class="form-control" value="{{ $tanggal_awal ?? '' }}">
                    <label class="mx-2">s/d</label>
                    <input type="date" name="tanggal_akhir" class="form-control" value="{{ $tanggal_akhir ?? '' }}">
                </div>
                <div class="form-group mb-2">
                    <button type="submit" class="btn btn-primary mx-1">
                        <i class="fas fa-search mr-1"></i> Cari
                    </button>
                    <button type="reset" class="btn btn-secondary mx-1" onclick="window.location='{{ route('stock.mutasi') }}'">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                    <a href="{{ route('stock.mutasi') }}" class="btn btn-danger mx-1">
                        <i class="fas fa-times mr-1"></i> Exit
                    </a>
                    <a href="{{ route('stock.print.good') }}?kolom={{ $kolom }}&value={{ $value }}" target="_blank" class="btn btn-success mx-1">
                        <i class="fas fa-print mr-1"></i> Cetak Good Stock
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Mutasi Stock -->
    @if(isset($selectedStock))
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exchange-alt mr-2"></i>Mutasi Stock - {{ $selectedStock->kode_barang }} ({{ $selectedStock->nama_barang }})
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>No.</th>
                                <th>No Transaksi</th>
                                <th>Tanggal</th>
                                <th>No Nota/No Order</th>
                                <th>Supp./Cust.</th>
                                <th>+</th>
                                <th>-</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($tanggal_awal)
                                <tr class="table-danger font-weight-bold">
                                    <td colspan="7">Saldo Awal</td>
                                    <td class="text-right">{{ number_format($openingBalance, 2) }}</td>
                                </tr>
                            @endif
                            
                            @php $runningTotal = $openingBalance; @endphp
                            
                            @forelse($mutations as $index => $mutation)
                                @php 
                                    $runningTotal = $mutation->total;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $mutation->no_transaksi }}</td>
                                    <td>{{ \Carbon\Carbon::parse($mutation->tanggal)->format('d M Y H:i') }}</td>
                                    <td>{{ $mutation->no_nota ?: '-' }}</td>
                                    <td>{{ $mutation->supplier_customer }}</td>
                                    <td>{{ number_format($mutation->plus) }}</td>
                                    <td>{{ number_format($mutation->minus) }}</td>
                                    <td>{{ number_format($mutation->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada data mutasi untuk barang ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection