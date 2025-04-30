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
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Kredit</th>
                            <th>Debit</th>
                            <th>Date</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gabungan as $gab)
                            <tr>
                                <td>{{ $gab['Name'] }}</td>
                                <td>{{!! $gab['Deskripsi'] !!}}</td>
                                @if($gab['Type'] == 'Kredit')
                                    <td>{{ $gab['Grand total'] }}</td>
                                    <td></td>
                                @else
                                    <td></td>
                                    <td>{{ $gab['Grand total'] }}</td>
                                @endif
                                <td>{{ $gab['Date'] }}</td>
                                <td>{{ $gab['Saldo'] }}</td>
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
            <form class="form-inline" method="GET" action="/viewKas">
                <div class="form-group mx-2 mb-2">
                    <label class="mr-2">Name</label>
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
                    {{-- <button type="reset" class="btn btn-secondary mx-1" onclick="window.location='{{ route('stock.mutasi') }}'">
                        <i class="fas fa-sync-alt mr-1"></i> Refresh
                    </button>
                    <a href="{{ route('stock.mutasi') }}" class="btn btn-danger mx-1">
                        <i class="fas fa-times mr-1"></i> Exit
                    </a>
                    <a href="{{ route('stock.print.good') }}?kolom={{ $kolom }}&value={{ $value }}" target="_blank" class="btn btn-success mx-1">
                        <i class="fas fa-print mr-1"></i> Cetak Good Stock
                    </a> --}}
                </div>
            </form>
        </div>
    </div>

    {{-- <!-- Mutasi Stock -->
    @if(isset($gabungan))
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exchange-alt mr-2"></i>Mutasi Stock - {{ $gabungan->kode_barang }} ({{ $selectedStock->nama_barang }})
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
                                    <td>{{ number_format($mutation->plus, 2) }}</td>
                                    <td>{{ number_format($mutation->minus, 2) }}</td>
                                    <td>{{ number_format($mutation->total, 2) }}</td>
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
    @endif --}}
</div>
@endsection
