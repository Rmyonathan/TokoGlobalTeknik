<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembelian #{{ $purchase->nota }}</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Jika ada query ?print=1, langsung print
            if (window.location.search.includes('print=1')) {
                window.print();
            }
        });
    </script>
    <style>
        @page {
            size: 21.59cm 14cm;
            margin: 8mm;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }

        .header, .footer {
            text-align: center;
            margin-bottom: 8px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        th, td {
            border: 1px solid black;
            padding: 2px;
            font-size: 9px;
        }

        .right { text-align: right; }
        .center { text-align: center; }

        .note {
            font-size: 9px;
            margin-top: 8px;
            margin-bottom: 5px;
            line-height: 1.1;
        }

        .signature {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }

        .page-break {
            page-break-after: always;
        }

        /* Status badges styling */
        .status-badge {
            font-size: 8px;
            padding: 2px 4px;
            margin-top: 3px;
            border: 1px solid #000;
            display: inline-block;
            margin-bottom: 5px;
        }

        .status-canceled {
            background-color: #ffdddd;
            color: #cc0000;
        }

        .status-edited {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Header styling to match sales nota */
        .header strong {
            font-size: 11px;
        }

        .header br {
            line-height: 0.8;
        }

        /* Make supplier info section more compact */
        .row div {
            line-height: 1.1;
        }

        /* Make the summary table more compact */
        table:last-of-type th,
        table:last-of-type td {
            padding: 1.5px;
            font-size: 9px;
        }

        .edit-info-box {
            font-size: 8px;
            margin-top: 5px;
            padding: 3px;
            border: 1px solid #ccc;
            line-height: 1.1;
        }

        /* Tombol di kanan atas, hanya tampil di layar */
        .top-right-buttons {
            position: fixed;
            top: 18px;
            right: 30px;
            z-index: 999;
        }
        .top-right-buttons button, .top-right-buttons a {
            margin-left: 8px;
            padding: 6px 16px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            background: #007bff;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
        }
        .top-right-buttons button:hover, .top-right-buttons a:hover {
            background: #0056b3;
        }
        @media print {
            .top-right-buttons {
                display: none !important;
            }
            
            /* Additional print-specific adjustments */
            body {
                font-size: 9px;
            }
            
            .page {
                height: auto;
                overflow: visible;
            }
        }
    </style>
</head>
<body>

<div class="top-right-buttons">
    <a href="{{ route('pembelian.nota.list') }}" class="btn btn-secondary">Kembali</a>
    <button onclick="window.print()">Print</button>
</div>

@php
    $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
@endphp

<div class="page">
    {{-- HEADER --}}
    <div class="header">
        <strong>{{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}</strong><br>
        {{ $defaultCompany->alamat ?? 'JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008' }}<br>
        {{ $defaultCompany->kota ?? '8 ILIR' }}, {{ $defaultCompany->kode_pos ?? 'ILIR TIMUR II' }}<br>
        TELP. {{ $defaultCompany->telepon ?? '(0711) 311158' }} &nbsp;&nbsp; FAX {{ $defaultCompany->fax ?? '(0711) 311158' }}<br>
        NO FAKTUR PEMBELIAN: {{ $purchase->nota }}
        
        @if($purchase->status == 'canceled')
            <div class="status-badge status-canceled">DIBATALKAN</div>
        
        @endif
    </div>

    {{-- SUPPLIER INFO --}}
    <div class="row">
        <div>
            <strong>Dari:</strong><br>
            Supplier: {{ $purchase->kode_supplier }} - {{ $purchase->supplierRelation->nama ?? '-' }}<br>
            Telp: {{ $purchase->supplierRelation->telepon_fax ?? '-' }}<br>
            Alamat: {{ $purchase->supplierRelation->alamat ?? '-' }}<br>
            <strong>No. Surat Jalan:</strong> {{ $purchase->no_surat_jalan ?: '-' }}
        </div>
        <div class="right">
            {{ \Carbon\Carbon::parse($purchase->created_at)->format('d M Y H:i') }}
        </div>
    </div>

    {{-- TABLE --}}
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
            @foreach($purchase->items as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td class="center">{{ $item->qty }}</td>
                <td class="right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="center">{{ $item->diskon ?? 0 }}</td>
                <td class="right">Rp {{ number_format(($item->harga * $item->qty * ($item->diskon ?? 0) / 100), 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="note">
        <strong>PERHATIAN !!!</strong><br>
        Barang yang sudah dibeli dari {{ $defaultCompany->nama ?? 'CV. Alumka Cipta Prima' }} tidak bisa dikembalikan.<br>
        {{ $defaultCompany->catatan_nota ?? 'Pembayaran dengan Cek, Giro, BG, dan sejenisnya dianggap sah setelah dananya cair.' }}
    </div>

    <br>

    <table>
        <tr>
            <th style="width: 70%">TOTAL</th>
            <td class="right">Rp {{ number_format($purchase->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>DISCOUNT (%)</th>
            <td class="right">Rp {{ number_format($purchase->diskon, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>PPN</th>
            <td class="right">Rp {{ number_format($purchase->ppn, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>GRAND TOTAL</th>
            <td class="right"><strong>Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</strong></td>
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

    @if(isset($purchase->is_edited) && $purchase->is_edited)
    <div class="edit-info-box">
        <strong>Informasi Edit:</strong><br>
        Diedit oleh: {{ $purchase->edited_by ?? 'Unknown' }} pada {{ isset($purchase->edited_at) ? \Carbon\Carbon::parse($purchase->edited_at)->format('d M Y H:i') : 'Unknown' }}<br>
        Alasan: {{ $purchase->edit_reason ?? 'No reason provided' }}
    </div>
    @endif

    @if($purchase->status == 'canceled')
    <div class="edit-info-box">
        <strong>Informasi Pembatalan:</strong><br>
        Dibatalkan oleh: {{ $purchase->canceled_by }} pada {{ \Carbon\Carbon::parse($purchase->canceled_at)->format('d M Y H:i') }}<br>
        Alasan: {{ $purchase->cancel_reason }}
    </div>
    @endif
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