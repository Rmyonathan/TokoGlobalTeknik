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
        .action-buttons {
            margin-bottom: 20px;
            text-align: center;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn-print {
            background-color: #17a2b8;
            color: white;
        }
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        @media print {
            .action-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button class="btn btn-print" onclick="window.print()">Cetak Nota</button>
        <a href="{{ route('pembelian.nota.list') }}" class="btn btn-back">Kembali ke Daftar</a>
    </div>

    @php
    $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
    @endphp

    <div class="container">
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}</div>
                <div>{{ $defaultCompany->alamat ?? 'JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008' }}</div>
                <div>{{ $defaultCompany->kota ?? '8 ILIR' }}, {{ $defaultCompany->kode_pos ?? 'ILIR TIMUR II' }}</div>
                <div>TELP: {{ $defaultCompany->telepon ?? '(0711) 811158' }}</div>
                <div>FAX: {{ $defaultCompany->fax ?? '(0711) 811158' }}</div>
                <div class="faktur-no">NO FAKTUR: {{ $purchase->nota }}</div>
            </div>
            @if($purchase->status == 'canceled')
            <div class="alert alert-danger">
                <strong>DIBATALKAN</strong><br>
                Oleh: {{ $purchase->canceled_by }}<br>
                Pada: {{ date('d/m/Y H:i', strtotime($purchase->canceled_at)) }}<br>
                Alasan: {{ $purchase->cancel_reason }}
            </div>
            @endif
            <div class="customer-info">
                <table>
                    <tr>
                        <td>Kpd Yth:</td>
                        <td>{{ date('d M Y H:i:s', strtotime($purchase->created_at)) }}</td>
                    </tr>
                    <tr>
                        <td>SUPPLIER</td>
                        <td>{{ $purchase->kode_supplier }} - {{ $purchase->supplierRelation->nama ?? '' }}</td>
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
            <p><strong>PERHATIAN !!!</strong> Barang yang sudah dibeli dari {{ $defaultCompany->nama ?? 'CV. Alumka Cipta Prima' }} tidak bisa dikembalikan.</p>
            <p>- {{ $defaultCompany->catatan_nota ?? 'Pembayaran dengan Cek, Giro, BG, dan sejenisnya dianggap sah setelah dananya cair.' }}</p>
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