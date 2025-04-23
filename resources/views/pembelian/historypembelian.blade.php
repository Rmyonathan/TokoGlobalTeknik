@extends('layout.Nav')

@section(section: 'content')

@php
    // Data dummy sementara untuk testing tampilan
    $data = [
        (object)[
            'tanggal' => '2025-04-14',
            'nota' => 'BL/04/25-00001',
            'supplier' => 'PT. Maju Jaya',
            'total' => 240000,
            'branch' => 'Lampung',
        ],
        (object)[
            'tanggal' => '2025-04-13',
            'nota' => 'BL/04/25-00002',
            'supplier' => 'CV. Sumber Rejeki',
            'total' => 175000,
            'branch' => 'Palembang',
        ],
    ];
@endphp


<div class="container py-4">
    <h4>:: History Pembelian ::</h4>

    <form method="GET" action="{{ route('pembelian.historypembelian') }}" class="row mb-3">
        <div class="col-md-3">
            <label>Tanggal Awal</label>
            <input type="date" name="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
        </div>
        <div class="col-md-3">
            <label>Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
        </div>
        <div class="col-md-3">
            <label>&nbsp;</label>
            <button class="btn btn-primary form-control">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>No Nota</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Cabang</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td>{{ $row->tanggal }}</td>
                        <td>{{ $row->nota }}</td>
                        <td>{{ $row->supplier }}</td>
                        <td>Rp{{ number_format($row->total, 0, ',', '.') }}</td>
                        <td>{{ $row->branch }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection