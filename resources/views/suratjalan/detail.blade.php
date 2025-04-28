<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        .header, .footer, .sub-header {
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
        table th, table td {
            border: 1px solid black;
            padding: 4px;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .note {
            font-size: 11px;
            margin-top: 10px;
        }
        .signature {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <div class="header">
        <strong>CV. ALUMKA CIPTA PRIMA</strong><br>
        JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008<br>
        8 ILIR, ILIR TIMUR II<br>
        TELP. (0711) 311158 &nbsp;&nbsp; FAX (0711) 311158<br>
        NO SURAT JALAN: {{ $suratJalan->no_suratjalan }}<br>
        NO FAKTUR: {{ $suratJalan->no_transaksi }}
    </div>

    <br>

    <div class="row">
        <div>
            <strong>Kpd Yth:</strong><br>
            Nama: {{ $suratJalan->customer->nama ?? '-' }}<br>
            Telp: {{ $suratJalan->customer->telepon ?? '-' }}<br>
            Alamat: {{ $suratJalan->alamat_suratjalan ?? '-' }}
        </div>
        <div class="right">
            {{ \Carbon\Carbon::parse($suratJalan->tanggal)->format('d M Y H:i:s') }}<br>
            HALAMAN: 1 - 1
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Disc %</th>
                <th>Disc Rp</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suratJalan->transaksi->items as $i => $item)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="center">{{ $item->qty }}</td>
                <td class="right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="center">{{ $item->diskon_persen ?? 0 }}</td>
                <td class="right">Rp {{ number_format($item->diskon, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="note">
        <strong>PERHATIAN !!!</strong><br>
        Barang masih titipan dari CV. Alumka Cipta Prima, bila belum dilunasi.<br>
        Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.
    </div>

    <br>

    <table>
        <tr>
            <th style="width: 70%">TOTAL</th>
            <td class="right">Rp {{ number_format($suratJalan->transaksi->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>DISCOUNT (%)</th>
            <td class="right">Rp {{ number_format($suratJalan->transaksi->discount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>PPN</th>
            <td class="right">Rp {{ number_format($suratJalan->transaksi->ppn, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>GRAND TOTAL</th>
            <td class="right"><strong>Rp {{ number_format($suratJalan->transaksi->grand_total, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <th>TITIPAN UANG</th>
            <td class="right">Rp {{ number_format($suratJalan->titipan_uang, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>SISA PIUTANG</th>
            <td class="right">Rp {{ number_format($suratJalan->sisa_piutang, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="signature">
        <div>
            HORMAT KAMI<br><br><br><br>
            (_____________)
        </div>
        <div class="right">
            PENERIMA<br><br><br><br>
            (_____________)
        </div>
    </div>

</body>
</html>
