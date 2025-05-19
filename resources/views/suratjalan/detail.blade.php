<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan</title>
    <style>
        @page {
            size: 21.59cm 14cm;
            margin: 1cm;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header, .footer {
            text-align: center;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 12px;
        }
        th {
            background: none;
            color: #000;
            font-weight: bold;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .no-print {
            position: fixed;
            top: 18px;
            right: 30px;
            z-index: 999;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    @php
        $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
    @endphp
    
    <div class="no-print" style="display:flex;gap:10px;">
        <a href="{{ route('suratjalan.history') }}" 
        style="
                display:inline-block;
                padding:6px 18px;
                background:#6c757d;
                color:#fff;
                border-radius:5px;
                text-decoration:none;
                font-size:14px;
                border:none;
                transition:background 0.2s;
            "
        onmouseover="this.style.background='#495057'"
        onmouseout="this.style.background='#6c757d'">
            &#8592; Kembali
        </a>
        <button onclick="window.print()" 
            style="
                padding:6px 18px;
                background:#007bff;
                color:#fff;
                border-radius:5px;
                border:none;
                font-size:14px;
                cursor:pointer;
                transition:background 0.2s;
            "
            onmouseover="this.style.background='#0056b3'"
            onmouseout="this.style.background='#007bff'">
            &#128424; Print
        </button>
    </div>

    <div class="header">
        <strong>{{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}</strong><br>
        {{ $defaultCompany->alamat ?? 'JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008' }}<br>
        {{ $defaultCompany->kota ?? '8 ILIR' }}, {{ $defaultCompany->kode_pos ?? 'ILIR TIMUR II' }}<br>
        TELP. {{ $defaultCompany->telepon ?? '(0711) 311158' }} &nbsp;&nbsp; FAX {{ $defaultCompany->fax ?? '(0711) 311158' }}<br>
    </div>

    <div class="row" style="margin-bottom:0;">
        <div>
            <strong>No. Surat Jalan:</strong> {{ $suratJalan->no_suratjalan ?? '-' }}<br>
            <strong>No. Pesanan:</strong> {{ $suratJalan->no_transaksi ?? '-' }}
        </div>
        <div class="right">
            <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($suratJalan->tanggal)->format('d M Y') }}<br>
            <strong>Waktu:</strong> {{ \Carbon\Carbon::parse($suratJalan->transaksi->created_at)->format('H:i:s') }}
        </div>
    </div>
    <div class="row" style="margin-bottom:0;">
        <div>
            <strong>Kepada Yth:</strong> {{ $suratJalan->customer->nama ?? '-' }}<br>
            <strong>Alamat:</strong> {{ $suratJalan->alamat_suratjalan ?? '-' }}<br>
            <strong>Telp:</strong> {{ $suratJalan->customer->telepon ?? '-' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="20%">Kode Barang</th>
                <th width="35%">Nama Barang</th>
                <th width="10%">Panjang</th>
                <th width="10%">Jumlah</th>
                <th width="10%">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suratJalan->transaksi->items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="center">{{ $item->panjang }}</td>
                <td class="center">{{ $item->qty }}</td>
                <td class="center">Pcs</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <div>
        <strong>Catatan:</strong>
        <br>
        Mohon periksa barang sebelum menandatangani. Kerusakan setelah tanda tangan bukan tanggung jawab pengirim.<br>
        Barang masih titipan dari {{ $defaultCompany->nama ?? 'CV. Alumka Cipta Prima' }}, bila belum dilunasi.<br>
        Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.<br>
        <br><strong>Catatan Toko:</strong>
        <br>{{ $defaultCompany->catatan_nota ?? 'Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.' }}
    </div>

    <br>
    <table style="width:100%; border:none;">
        <tr>
            <td class="center" style="border:none;">
                Dibuat Oleh<br><br><br><br>
                (_____________)
            </td>
            <td class="center" style="border:none;">
                Diantar Oleh<br><br><br><br>
                (_____________)
            </td>
            <td class="center" style="border:none;">
                Diterima Oleh<br><br><br><br>
                (_____________)
            </td>
        </tr>
    </table>
</body>
</html>