<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $catalogTitle }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 10px;
            color: #555;
            margin-bottom: 3px;
        }
        
        .catalog-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            color: #2c3e50;
            text-transform: uppercase;
        }
        
        .valid-until {
            font-size: 10px;
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background-color: #34495e;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #2c3e50;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #ecf0f1;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .price {
            font-weight: bold;
            color: #27ae60;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #bdc3c7;
            font-size: 9px;
            color: #7f8c8d;
        }
        
        .notes {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin-top: 20px;
            font-size: 10px;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #856404;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if($perusahaan)
            <div class="company-name">{{ $perusahaan->nama_perusahaan ?? 'GLOBAL TEKNIK' }}</div>
            @if($perusahaan->alamat)
                <div class="company-info">{{ $perusahaan->alamat }}</div>
            @endif
            @if($perusahaan->no_telp)
                <div class="company-info">Telp: {{ $perusahaan->no_telp }}</div>
            @endif
            @if($perusahaan->email)
                <div class="company-info">Email: {{ $perusahaan->email }}</div>
            @endif
        @else
            <div class="company-name">NAMA PERUSAHAAN</div>
        @endif
        
        <div class="catalog-title">{{ $catalogTitle }}</div>
        @if($validUntil)
            <div class="valid-until">Berlaku sampai: {{ $validUntil }}</div>
        @endif
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Kode Barang</th>
                <th width="30%">Nama Barang</th>
                <th width="12%">Merek</th>
                <th width="12%">Ukuran/Type</th>
                <th width="8%" class="text-center">Satuan</th>
                <th width="18%" class="text-right">Harga Jual</th>
                @if($showStock)
                    <th width="10%" class="text-center">Stok</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($barangs as $index => $barang)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $barang->kode_barang }}</td>
                    <td>{{ $barang->name }}</td>
                    <td>{{ $barang->merek ?? '-' }}</td>
                    <td>{{ $barang->ukuran ?? '-' }}</td>
                    <td class="text-center">{{ $barang->unit_dasar ?? 'PCS' }}</td>
                    <td class="text-right price">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</td>
                    @if($showStock)
                        <td class="text-center">
                            @php
                                $stock = DB::table('stocks')
                                    ->where('kode_barang', $barang->kode_barang)
                                    ->sum('good_stock');
                            @endphp
                            {{ number_format($stock, 0, ',', '.') }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Notes -->
    @if($notes)
        <div class="notes">
            <div class="notes-title">CATATAN:</div>
            <div>{{ $notes }}</div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>Tanggal Cetak: {{ date('d/m/Y H:i') }}</div>
        <div>Total Item: {{ count($barangs) }}</div>
        @if($perusahaan && $perusahaan->nama_perusahaan)
            <div>© {{ date('Y') }} {{ $perusahaan->nama_perusahaan }}. All rights reserved.</div>
        @endif
    </div>
</body>
</html>

