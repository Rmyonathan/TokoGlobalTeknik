@extends('layout.Nav')
@section('content')

@php
        // Data dummy
        $data = [
            (object)[
                'no_suratjalan' => 'SJ-0425-00140',
                'tanggal' => '2025-04-14',
                'customer' => 'PT. Sumber Rezeki',
                'alamat' => 'Jl. Merpati No. 45, Jakarta',
                'no_faktur' => 'INV-20250414-01',
                'branch' => 'Cabang Jakarta'
            ],
            (object)[
                'no_suratjalan' => 'SJ-0425-00141',
                'tanggal' => '2025-04-14',
                'customer' => 'CV. Makmur Sentosa',
                'alamat' => 'Jl. Rajawali No. 17, Bandung',
                'no_faktur' => 'INV-20250414-02',
                'branch' => 'Cabang Bandung'
            ]
        ];
    @endphp

<div class="container py-4">
    <h4>:: History Surat Jalan ::</h4>

    <form method="GET" action="{{ route('suratjalan.historysuratjalan') }}" class="row mb-3">
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
                    <th>No Surat Jalan</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Alamat</th>
                    <th>No Faktur</th>
                    <th>Cabang</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td>{{ $row->no_suratjalan }}</td>
                        <td>{{ $row->tanggal }}</td>
                        <td>{{ $row->customer }}</td>
                        <td>{{ $row->alamat }}</td>
                        <td>{{ $row->no_faktur }}</td>
                        <td>{{ $row->branch }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection