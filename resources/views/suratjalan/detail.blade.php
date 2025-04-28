<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 100%;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin: 0 0 5px;
            text-transform: uppercase;
            color: #2c3e50;
        }
        .header p {
            margin: 3px 0;
            font-size: 14px;
        }
        .document-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0;
            background-color: #f8f9fa;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .document-title judul {
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .document-title p {
            font-size: 14px;
            font-weight: bold;
        }
        .document-number {
            background-color: #f8f9fa;
            padding: 6px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin: 10px auto;
            width: 80%;
            text-align: center;
        }
        .document-number p {
            margin: 3px 0;
            font-weight: bold;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .info-box {
            width: 30%;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: #f8f9fa;
        }
        .info-box h3 {
            margin: 0 0 8px;
            font-size: 12px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            font-size: 11px;
            text-transform: uppercase;
        }
        table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 11px;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .note-box {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #6c757d;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-size: 11px;
        }
        .note-box h4 {
            margin: 0 0 5px;
            color: #495057;
            font-size: 12px;
        }
        .delivery-info {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: #f8f9fa;
        }
        .delivery-info h3 {
            margin: 0 0 8px;
            font-size: 12px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
            text-transform: uppercase;
        }
        .delivery-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            width: 150px;
            text-align: center;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        @media print {
            body {
                width: 210mm;
                height: 297mm;
            }
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
                    <th width="5%">No.</th>
                    <th width="10%">Kode Barang</th>
                    <th width="15%">Nama Barang</th>
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
                    <td>{{ $item->harga }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-center">Pcs</td>
                    <td>{{ $item->total }}</td>
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
                    <p><strong>Pengirim:</strong> ___________________</p>
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