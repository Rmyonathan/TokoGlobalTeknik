@extends('layout.Nav')

@section('content')
<div class="container">

    <h2 class="title-box">Kas Perusahaan</h2>
     <!-- Filter -->
     <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter mr-2"></i>Filter
            <a href="{{ route('kas.create') }}" class="btn btn-success mb-3"> <i class="fas fa-plus"></i> Tambah Kas Baru</a>

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
                </div>
            </form>
        </div>
    </div>
    
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    <!-- Error Message -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gabungan as $gab)
                            <tr>
                                <td>{{ $gab['Name'] }}</td>
                                <td>{!! $gab['Deskripsi'] !!}</td>
                                @if($gab['Type'] == 'Kredit')
                                    <td>{{ $gab['Grand total'] }}</td>
                                    <td></td>
                                @else
                                    <td></td>
                                    <td>{{ $gab['Grand total'] }}</td>
                                @endif
                                <td>{{ $gab['Date'] }}</td>
                                <td>{{ $gab['Saldo'] }}</td>
                                <td>
                                    @if(isset($gab['id']) && isset($gab['is_manual']) && $gab['is_manual'] == true) 
                                        <div class="btn-group">
                                            <form action="/delete_kas" method="POST" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="kas_id" value="{{ $gab['id'] }}">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Yakin mau menghapus kas ini?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                            <form action="/cancel_kas" method="POST" class="ml-1" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="kas_id" value="{{ $gab['id'] }}">
                                                <button type="submit" class="btn btn-warning btn-sm" 
                                                    onclick="return confirm('Yakin mau membatalkan kas ini?')">
                                                    <i class="fas fa-ban"></i> Cancel
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
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
