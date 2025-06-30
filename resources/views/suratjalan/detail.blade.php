<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan</title>
    <style>
        /* Universal rule for more predictable layouts */
        * {
            box-sizing: border-box;
        }

        @page {
            size: 21.59cm 13.97cm; /* Standardized paper size */
            /* * IMPORTANT: Set to 0. You MUST also set Margins to "None" or "Minimum"
             * in your browser's print preview dialog for this to work effectively.
             */
            margin: 0mm;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.1;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 100%;
            height: 100%;
            padding: 4mm; /* Internal padding for content */
            display: flex;
            flex-direction: column;
        }

        .header {
            text-align: center;
            line-height: 1;
        }
        .header strong { font-size: 11px; }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 9px;
        }

        table.item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table.item-table th, 
        table.item-table td {
            border: 1px solid #000;
            padding: 1.5px 2px;
            font-size: 8px;
        }
        
        table.item-table th {
            font-weight: bold;
        }

        .notes-section {
            font-size: 8px;
            line-height: 1.2;
            margin-top: auto; /* Pushes notes and signatures to the bottom */
            padding-top: 5px;
        }

        table.signature-table {
            width: 100%;
            margin-top: 10px;
        }

        table.signature-table td {
            border: none;
            text-align: center;
            font-size: 9px;
        }
        
        .right { text-align: right; }
        .center { text-align: center; }

        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 999;
        }
        .no-print a, .no-print button {
            display:inline-block;
            margin-left: 8px;
            padding: 6px 16px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            background: #007bff;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .no-print a { background: #6c757d; }
        .no-print a:hover { background: #5a6268; }
        .no-print button:hover { background: #0056b3; }

        @media print {
            .no-print { display: none !important; }
            body { font-size: 9px; }
        }
    </style>
</head>
<body>
    @php
        $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
    @endphp
    
    <div class="no-print">
        <a href="{{ route('suratjalan.history') }}">&#8592; Kembali</a>
        <button onclick="window.print()">&#128424; Print</button>
    </div>

    <div class="page">
        <div class="header">
            <strong>{{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}</strong><br>
            {{ $defaultCompany->alamat ?? 'JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008' }}<br>
            {{ $defaultCompany->kota ?? '8 ILIR' }}, {{ $defaultCompany->kode_pos ?? 'ILIR TIMUR II' }}<br>
            TELP. {{ $defaultCompany->telepon ?? '(0711) 311158' }} &nbsp;&nbsp; FAX {{ $defaultCompany->fax ?? '(0711) 311158' }}<br>
        </div>

        <div class="row" style="margin-top: 5px;">
            <div>
                <strong>No. Surat Jalan:</strong> {{ $suratJalan->no_suratjalan ?? '-' }}<br>
                <strong>No. Pesanan:</strong> {{ $suratJalan->no_transaksi ?? '-' }}
            </div>
            <div class="right">
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($suratJalan->tanggal)->format('d M Y') }}<br>
                <strong>Waktu:</strong> {{ \Carbon\Carbon::parse($suratJalan->transaksi->created_at)->format('H:i:s') }}
            </div>
        </div>
        <div class="row">
            <div>
                <strong>Kepada Yth:</strong> {{ $suratJalan->customer->nama ?? '-' }}<br>
                <strong>Alamat:</strong> {{ $suratJalan->alamat_suratjalan ?? '-' }}<br>
                <strong>Telp:</strong> {{ $suratJalan->customer->telepon ?? '-' }}
            </div>
        </div>

        <table class="item-table">
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
                {{-- The @for loop that created empty rows has been removed. --}}
            </tbody>
        </table>

        <div class="notes-section">
            <strong>Catatan:</strong><br>
            Mohon periksa barang sebelum menandatangani. Kerusakan setelah tanda tangan bukan tanggung jawab pengirim.<br>
            Barang masih titipan dari {{ $defaultCompany->nama ?? 'CV. Alumka Cipta Prima' }}, bila belum dilunasi.<br>
            Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.<br>
            <br><strong>Catatan Toko:</strong><br>
            {{ $defaultCompany->catatan_nota ?? 'Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.' }}
        </div>

        <table class="signature-table">
            <tr>
                <td>
                    Dibuat Oleh<br><br><br><br>
                    (_____________)
                </td>
                <td>
                    Diantar Oleh<br><br><br><br>
                    (_____________)
                </td>
                <td>
                    Diterima Oleh<br><br><br><br>
                    (_____________)
                </td>
            </tr>
        </table>
    </div>

    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_print') === '1') {
                window.print();
            }
        };
    </script>
</body>
</html>