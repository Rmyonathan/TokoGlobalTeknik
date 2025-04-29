<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        @page {
            size: 8.5in 11in;
            margin: 0.5in;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
        .header, .document-title, .info-section, table, .note-box, .delivery-info, .signature-section {
            page-break-inside: avoid;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 3px;
        }
        .header p {
            font-size: 12px;
            margin: 2px 0;
        }
        .document-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .document-number p, .document-title p {
            font-size: 12px;
            margin: 2px 0;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-box {
            width: 32%;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 3px;
            background-color: #f8f9fa;
            font-size: 11px;
        }
        .info-box h3 {
            font-size: 11px;
            margin-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 5px;
            font-size: 10px;
        }
        th {
            background-color: #2c3e50;
            color: #fff;
            text-align: center;
        }
        .note-box {
            font-size: 10px;
            margin-top: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .delivery-info {
            margin-top: 10px;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 10px;
        }
        .delivery-grid {
            display: flex;
            justify-content: space-between;
        }
        .delivery-grid div {
            width: 48%;
        }
        .signature-section {
            display: flex;
            justify-content: space-around;
            margin-top: 30px;
        }
        .signature-box {
            text-align: center;
            font-size: 11px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>

</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CV. ALUMKA CIPTA PRIMA</h1>
            <p>JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008</p>
            <p>8 ILIR, ILIR TIMUR II</p>
            <p>TELP. (0711) 311158 &nbsp;&nbsp; FAX (0711) 311158</p>
        </div>

        <div class="document-title">
            <div class="judul">SURAT JALAN</div>
            <p>No. Surat Jalan: {{ $suratJalan->no_suratjalan }}</p>
            <p>No. Pesanan: {{ $suratJalan->no_transaksi }}</p>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>Pengirim:</h3>
                <p><strong>CV. ALUMKA CIPTA PRIMA</strong></p>
                <p>JL. SINAR RAGA ABI HASAN NO.1553</p>
                <p>8 ILIR, ILIR TIMUR II</p>
                <p>TELP. (0711) 311158</p>
            </div>
            <div class="info-box">
                <h3>Penerima:</h3>
                <p><strong>Nama: </strong><strong>{{ $suratJalan->customer->nama ?? '-' }}</strong></p>
                <p><strong>Alamat: </strong>{{ $suratJalan->alamat_suratjalan ?? '-' }}</p>
                <p><strong>Telp: </strong>{{ $suratJalan->customer->telepon ?? '-' }}</p>
            </div>
            <div class="info-box">
                <h3>Informasi Pengiriman:</h3>
                <p><strong>Tanggal: </strong> {{ \Carbon\Carbon::parse($suratJalan->tanggal)->format('d M Y') }}</p>
                <p><strong>Waktu: </strong> {{ \Carbon\Carbon::parse($suratJalan->transaksi->created_at)->format('H:i:s') }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="2.5%">No.</th>
                    <th width="10%">Kode Barang</th>
                    <th width="17.5%">Nama Barang</th>
                    <th width="25%">Keterangan</th>
                    <th width="7.5%">Panjang (m)</th>
                    <th width="10%">Harga Satuan</th>
                    <th width="5%">Jumlah</th>
                    <th width="5%">Satuan</th>
                    <th width="12.5%">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suratJalan->transaksi->items as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td>{{ $item->panjang }}</td>
                    <td class= "text-right">Rp {{ number_format($item->harga, 2, ' ,' , '.') }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-center">Pcs</td>
                    <td class="text-right">Rp {{ number_format($item->total, 2, ' ,' , '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="note-box">
            <h4>CATATAN PENGIRIMAN</h4>
            <p>Mohon periksa barang sebelum menandatangani. Kerusakan setelah tanda tangan bukan tanggung jawab pengirim.</p>
            <p>Barang masih titipan dari CV. Alumka Cipta Prima, bila belum dilunasi.</p>
            <p>Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.</p>
        </div>

        <div class="delivery-info">
            <h3>KONFIRMASI PENGIRIMAN</h3>
            <div class="delivery-grid">
                <div>
                    <p><strong>Kurir:</strong> ___________________</p>
                    <p><strong>Plat Kendaraan:</strong> ___________________</p>
                </div>
                <div>
                    <p><strong>Kondisi Barang:</strong> [   ] Baik  [   ] Rusak</p>
                    <p><strong>Kelengkapan:</strong> [   ] Lengkap  [   ] Tidak Lengkap</p>
                </div>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <p>Dibuat Oleh</p>
                <div class="signature-line">
                    (_______________)
                </div>
                <p>Admin</p>
            </div>
            <div class="signature-box">
                <p>Diantar Oleh</p>
                <div class="signature-line">
                    (_______________)
                </div>
                <p>Kurir</p>
            </div>
            <div class="signature-box">
                <p>Diterima Oleh</p>
                <div class="signature-line">
                    (_______________)
                </div>
                <p>Penerima</p>
            </div>
        </div>
    </div>
</body>
</html>