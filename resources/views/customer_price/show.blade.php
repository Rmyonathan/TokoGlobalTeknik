@extends('layout.Nav')

@section('content')
<div class="container">
    <div class="title-box">
        <h2><i class="fas fa-eye mr-2"></i>Detail Harga Khusus Pelanggan</h2>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Harga Khusus</h5>
                    <div class="btn-group">
                        <a href="{{ route('customer-price.edit', $customerPrice->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                        <a href="{{ route('customer-price.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-user mr-1"></i> Informasi Pelanggan</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Nama:</strong></td>
                                            <td>{{ $customerPrice->customer->nama }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Alamat:</strong></td>
                                            <td>{{ $customerPrice->customer->alamat }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telepon:</strong></td>
                                            <td>{{ $customerPrice->customer->telepon ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $customerPrice->customer->email ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Product Information -->
                        <div class="col-md-6 mb-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-box mr-1"></i> Informasi Barang</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Kode Barang:</strong></td>
                                            <td><span class="badge bg-info">{{ $customerPrice->kodeBarang->kode_barang }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama Barang:</strong></td>
                                            <td>{{ $customerPrice->kodeBarang->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Attribute:</strong></td>
                                            <td>{{ $customerPrice->kodeBarang->attribute ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Satuan Dasar:</strong></td>
                                            <td>{{ $customerPrice->kodeBarang->unit_dasar }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Information -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-tags mr-1"></i> Informasi Harga</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h6 class="text-muted mb-2">Harga Normal</h6>
                                                <h4 class="text-success mb-0">
                                                    Rp {{ number_format($customerPrice->kodeBarang->harga_jual, 0, ',', '.') }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h6 class="text-muted mb-2">Harga Khusus</h6>
                                                <h4 class="text-primary mb-0">
                                                    Rp {{ number_format($customerPrice->harga_khusus, 0, ',', '.') }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-light rounded">
                                                <h6 class="text-muted mb-2">Diskon</h6>
                                                @php
                                                    $diskon = (($customerPrice->kodeBarang->harga_jual - $customerPrice->harga_khusus) / $customerPrice->kodeBarang->harga_jual) * 100;
                                                    $selisih = $customerPrice->kodeBarang->harga_jual - $customerPrice->harga_khusus;
                                                @endphp
                                                <h4 class="text-warning mb-0">
                                                    {{ number_format($diskon, 1) }}%
                                                </h4>
                                                <small class="text-muted">
                                                    Hemat: Rp {{ number_format($selisih, 0, ',', '.') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i> Informasi Tambahan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>Status:</strong></td>
                                                    <td>
                                                        @if($customerPrice->status == 'active')
                                                            <span class="badge bg-success">Aktif</span>
                                                        @else
                                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Mulai:</strong></td>
                                                    <td>
                                                        {{ $customerPrice->tanggal_mulai ? \Carbon\Carbon::parse($customerPrice->tanggal_mulai)->format('d/m/Y') : 'Segera' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tanggal Berakhir:</strong></td>
                                                    <td>
                                                        {{ $customerPrice->tanggal_berakhir ? \Carbon\Carbon::parse($customerPrice->tanggal_berakhir)->format('d/m/Y') : 'Selamanya' }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>Dibuat:</strong></td>
                                                    <td>{{ \Carbon\Carbon::parse($customerPrice->created_at)->format('d/m/Y H:i') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Diperbarui:</strong></td>
                                                    <td>{{ \Carbon\Carbon::parse($customerPrice->updated_at)->format('d/m/Y H:i') }}</td>
                                                </tr>
                                                @if($customerPrice->keterangan)
                                                <tr>
                                                    <td><strong>Keterangan:</strong></td>
                                                    <td>{{ $customerPrice->keterangan }}</td>
                                                </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
