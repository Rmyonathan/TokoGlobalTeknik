@extends('layout.Nav')

@section('content')
<section id="barang">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Barang</h2>
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('panels.create-inventory') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Tambah Barang
            </a>

        </div>
        <div class="card-body">
            {{-- <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th style="border: 1px solid #000;">No</th>
                            <th style="border: 1px solid #000;">Nama Barang</th>
                            <th style="border: 1px solid #000;">Kategori</th>
                            <th style="border: 1px solid #000;">Stok</th>
                            <th style="border: 1px solid #000;">Harga</th>
                            <th style="border: 1px solid #000;">Deskripsi</th>
                            <th style="border: 1px solid #000;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $dummyBarangs = [
                                ['nama' => 'Aluminium Batangan 3m', 'kategori' => 'Material', 'stok' => 20, 'harga' => 125000, 'deskripsi' => 'Batangan aluminium panjang 3 meter cocok untuk kusen.'],
                                ['nama' => 'Pintu Geser Aluminium', 'kategori' => 'Pintu', 'stok' => 5, 'harga' => 450000, 'deskripsi' => 'Pintu geser berbahan aluminium dengan rel atas dan bawah.'],
                                ['nama' => 'Jendela Lipat', 'kategori' => 'Jendela', 'stok' => 12, 'harga' => 325000, 'deskripsi' => 'Jendela lipat minimalis cocok untuk ruang tamu atau dapur.'],
                            ];
                        @endphp

                        @foreach ($dummyBarangs as $index => $barang)
                            <tr>
                                <td style="border: 1px solid #000;">{{ $index + 1 }}</td>
                                <td style="border: 1px solid #000;">{{ $barang['nama'] }}</td>
                                <td style="border: 1px solid #000;">{{ $barang['kategori'] }}</td>
                                <td style="border: 1px solid #000;">{{ $barang['stok'] }}</td>
                                <td style="border: 1px solid #000;">Rp {{ number_format($barang['harga'], 0, ',', '.') }}</td>
                                <td style="border: 1px solid #000;">{{ Str::limit($barang['deskripsi'], 60) }}</td>
                                <td style="border: 1px solid #000;">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-success" disabled>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" disabled>
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-tools"></i> Servis
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div> --}}
            <div class="card-body">
                @if(isset($inventory) && count($inventory['inventory_by_length']) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Group ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Length (meters)</th>
                                    <th>Available Quantity</th>
                                    <th>Total Length (meters)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventory['inventory_by_length'] as $item)
                                    <tr>
                                        <td>{{ $item['id'] }}</td>
                                        <td>{{ $item['group_id'] }}</td>
                                        <td>{{ $item['name'] }}</td>
                                        <td>Rp. {{ number_format($item['price'], 2) }}</td>
                                        <td>{{ number_format($item['length'], 2) }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>{{ number_format($item['length'] * $item['quantity'], 2) }}</td>
                                        <td style="border: 1px solid #000;">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-success" disabled>
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger" disabled>
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="fas fa-tools"></i> Servis
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th>Total</th>
                                    <th>{{ $inventory['total_panels'] }}</th>
                                    <th>
                                        @php
                                            $totalLength = 0;
                                            foreach($inventory['inventory_by_length'] as $item) {
                                                $totalLength += $item['length'] * $item['quantity'];
                                            }
                                            echo number_format($totalLength, 2);
                                        @endphp
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i> No panels currently in inventory.
                    </div>
                    <a href="{{ route('panels.create-inventory') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Add Panels to Inventory
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000 !important;
    }

    .table-bordered {
        border: 2px solid #000;
    }
</style>
@endsection
