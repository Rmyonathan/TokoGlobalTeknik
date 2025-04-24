<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembelian #{{ $purchase->nota }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #000;
            padding: 10px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .company-info {
            width: 60%;
        }
        .company-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .customer-info {
            width: 35%;
            border: 1px solid #000;
            padding: 5px;
        }
        .customer-info table {
            width: 100%;
        }
        .customer-info td {
            padding: 2px;
        }
        .faktur-no {
            margin-top: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .items-table th {
            background-color: #f0f0f0;
        }
        .notice {
            margin-top: 20px;
            font-size: 10px;
            font-style: italic;
        }
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .totals-table {
            width: 300px;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 2px 5px;
            text-align: right;
        }
        .print-button {
            margin-bottom: 20px;
            text-align: center;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">Cetak Nota</button>
        <button onclick="window.location.href='{{ route('pembelian.nota.list') }}'">Kembali ke Daftar</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="company-info">
                <div class="company-name">CV. ALUMKA CIPTA PRIMA</div>
                <div>JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008</div>
                <div>8 ILIR, ILIR TIMUR II</div>
                <div>TELP: (0711) 811158</div>
                <div>FAX: (0711) 811158</div>
                <div class="faktur-no">NO FAKTUR: {{ $purchase->nota }}</div>
            </div>
            <div class="customer-info">
                <table>
                    <tr>
                        <td>Kpd Yth:</td>
                        <td>{{ date('d M Y H:i:s', strtotime($purchase->created_at)) }}</td>
                    </tr>
                    <tr>
                        <td>SUPPLIER</td>
                        <td>{{ $purchase->kode_supplier }} </td>
                    </tr>
                    <tr>
                        <td>TELP/HP</td>
                        <td>{{ $purchase->supplierRelation->telepon_fax ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ALAMAT</td>
                        <td>{{ $purchase->supplierRelation->alamat ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>KODE BARANG</th>
                    <th>NAMA BARANG</th>
                    <th>QTY</th>
                    <th>HRG SATUAN</th>
                    <th>DISC %</th>
                    <th>DISC RP</th>
                    <th>SUB TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>{{ $item->diskon }}</td>
                    <td>{{ number_format(($item->harga * $item->qty * $item->diskon / 100), 0, ',', '.') }}</td>
                    <td>{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="notice">
            <p><strong>PERHATIAN !!!</strong> Barang masih diljepas dari CV. Alumka Cipta Prima tidak bisa dikembalikan.</p>
            <p>- Pembayaran dgan Cek, Giro, BG, dan sejenisnya di anggap sah setelah dananya cair.</p>
            <p>Hormat Kami,</p>
        </div>

        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td>TOTAL</td>
                    <td>Rp.</td>
                    <td>{{ number_format($purchase->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>DISCOUNT ( )</td>
                    <td>Rp.</td>
                    <td>{{ number_format($purchase->diskon, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>PPN</td>
                    <td>Rp.</td>
                    <td>{{ number_format($purchase->ppn, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>GRAND TOTAL</td>
                    <td>Rp.</td>
                    <td><strong>{{ number_format($purchase->grand_total, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>