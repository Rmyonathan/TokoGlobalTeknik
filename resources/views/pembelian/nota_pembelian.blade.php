@php
use Riskihajar\Terbilang\Facades\Terbilang;
@endphp

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
        /* Universal rule for more predictable layouts */
        * {
            box-sizing: border-box;
        }

        @page {
            size: 21.59cm 13.97cm; /* Standardized to match sales nota */
            /* * IMPORTANT: Set to 0. You MUST also set Margins to "None" or "Minimum"
             * in your browser's print preview dialog for this to work effectively.
             */
            margin: 0mm;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.0;
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: none;
        }
        
        .page {
            width: 100%;
            max-width: 100%;
            padding: 3mm; /* Add padding here to create an internal margin */
            box-sizing: border-box;
        }

        .header, .footer {
            text-align: center;
            margin-bottom: 6px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            margin-bottom: 3px;
            table-layout: auto;
        }

        th, td {
            border: 1px solid black;
            padding: 1.5px;
            font-size: 8px;
            word-wrap: break-word;
        }
        
        .right { text-align: right; }
        .center { text-align: center; }

        .note {
            font-size: 8px;
            margin-top: 5px;
            margin-bottom: 3px;
            line-height: 1.0;
        }

        .signature {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
        }

        .page-break {
            page-break-after: always;
        }

        .header strong {
            font-size: 11px;
        }

        .header br {
            line-height: 0.7;
        }

        table:last-of-type th,
        table:last-of-type td {
            padding: 1px;
            font-size: 8px;
        }

        .row div {
            line-height: 1.0;
        }

        .edit-info-box {
            font-size: 7px;
            margin-top: 3px;
            padding: 2px;
            border: 1px solid #ccc;
            line-height: 1.0;
        }

        .status-badge {
            font-size: 7px;
            padding: 1px 3px;
            margin-top: 2px;
            border: 1px solid #000;
            display: inline-block;
        }
        .status-canceled {
            background-color: #ffdddd;
            color: #cc0000;
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
            
            body {
                font-size: 8px;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .page {
                height: auto;
                overflow: visible;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 3mm !important;
            }
            
            table {
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            
            .header, .row, .note, .signature, .terbilang-section {
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .signature { margin-top: 8px; }
            .note { margin-top: 3px; margin-bottom: 2px; }
            table { margin-top: 2px; margin-bottom: 2px; }
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

    <div class="terbilang-section" style="margin-top: 3px; margin-bottom: 5px; margin-right: 5px; font-style: italic; text-align: right; font-size: 8px;">
        Terbilang: {{ ucwords(Terbilang::make($purchase->grand_total, ' rupiah')) }}
    </div>

    <div class="signature">
        <div>
            HORMAT KAMI<br><br><br>
            (_____________)
        </div>
        <div class="right">
            PENERIMA<br><br><br>
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