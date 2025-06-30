@php
use Riskihajar\Terbilang\Facades\Terbilang;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi</title>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // If there's a query ?print=1, print immediately
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
            size: 21.59cm 13.97cm; /* Exact match with Faktur paper size */
            /* * IMPORTANT: Set to 0. You MUST also set Margins to "None" or "Minimum"
             * in your browser's print preview dialog for this to work effectively.
             */
            margin: 0mm;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 9px; /* Further reduced font size */
            line-height: 1.0; /* Tighter line height */
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
            margin-bottom: 6px; /* Reduced margin */
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px; /* Further reduced margin */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px; /* Reduced margin */
            margin-bottom: 3px;
            table-layout: auto;
        }

        th, td {
            border: 1px solid black;
            padding: 1.5px; /* Further reduced padding */
            font-size: 8px; /* Smaller font for table */
            word-wrap: break-word;
        }
        
        /* Optimize column widths for better space utilization */
        th:nth-child(1), td:nth-child(1) { width: 5%; } /* No. */
        th:nth-child(2), td:nth-child(2) { width: 12%; } /* Kode Barang */
        th:nth-child(3), td:nth-child(3) { width: 25%; } /* Nama Barang */
        th:nth-child(4), td:nth-child(4) { width: 8%; } /* Qty */
        th:nth-child(5), td:nth-child(5) { width: 15%; } /* Harga Satuan */
        th:nth-child(6), td:nth-child(6) { width: 8%; } /* Disc % */
        th:nth-child(7), td:nth-child(7) { width: 12%; } /* Disc Rp */
        th:nth-child(8), td:nth-child(8) { width: 15%; } /* Sub Total */

        .right { text-align: right; }
        .center { text-align: center; }

        .note {
            font-size: 8px; /* Further reduced font size */
            margin-top: 5px; /* Reduced margin */
            margin-bottom: 3px;
            line-height: 1.0;
        }

        .signature {
            margin-top: 10px; /* Reduced margin for smaller height */
            display: flex;
            justify-content: space-between;
            font-size: 8px;
        }

        .page-break {
            page-break-after: always;
        }

        /* Compact spacing for specific elements */
        .header strong {
            font-size: 11px; /* Slightly smaller */
        }

        .header br {
            line-height: 0.7; /* Tighter line spacing */
        }

        /* Make the summary table more compact */
        table:last-of-type th,
        table:last-of-type td {
            padding: 1px; /* Minimal padding */
            font-size: 8px;
        }

        /* Reduce spacing in customer info */
        .row div {
            line-height: 1.0;
        }

        /* Status badges - make them smaller */
        .status-badge {
            font-size: 7px;
            padding: 1px 3px;
            margin-top: 2px;
        }

        .edit-info-box {
            font-size: 7px;
            margin-top: 3px;
            padding: 2px;
            border: 1px solid #ccc;
            line-height: 1.0;
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
        
        /* Terbilang section styling */
        .terbilang-section {
            margin-top: 3px; 
            margin-bottom: 5px; 
            margin-right: 5px; 
            font-style: italic; 
            text-align: right;
            font-size: 8px;
        }
        
        /* Payment info section */
        .payment-info {
            margin-bottom: 5px;
            font-size: 8px;
        }
        
        @media print {
            .top-right-buttons {
                display: none !important;
            }
            
            /* Force full width utilization in print */
            body {
                font-size: 8px; /* Even smaller for print */
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
                /* Padding should be maintained to create space from the physical edge */
                padding: 3mm !important;
            }
            
            /* Force table to use full available width */
            table {
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            
            /* Ensure content spans full width */
            .header, .row, .note, .signature, .terbilang-section, .payment-info {
                width: 100% !important;
                max-width: 100% !important;
            }
            
            /* Ensure content fits within page boundaries */
            .signature {
                margin-top: 8px;
            }
            
            .note {
                margin-top: 3px;
                margin-bottom: 2px;
            }
            
            table {
                margin-top: 2px;
                margin-bottom: 2px;
            }
        }
    </style>
</head>
<body>

<div class="top-right-buttons">
    <a href="{{ route('transaksi.listnota') }}" class="btn btn-secondary">Kembali</a>
    <button onclick="window.print()">Print</button>
    @if($transaction->status != 'canceled')
        <a href="{{ route('transaksi.edit', $transaction->id) }}" class="btn btn-warning">Edit</a>
    @endif
</div>

@php
    $totalPages = $groupedItems->count();
    $pageNum = 1;
    $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
@endphp

@foreach ($groupedItems as $chunk)
    <div class="page">
        {{-- HEADER --}}
        <div class="header">
            <strong>{{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}</strong><br>
            {{ $defaultCompany->alamat ?? 'JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008' }}<br>
            {{ $defaultCompany->kota ?? '8 ILIR' }}, {{ $defaultCompany->kode_pos ?? 'ILIR TIMUR II' }}<br>
            TELP. {{ $defaultCompany->telepon ?? '(0711) 311158' }} &nbsp;&nbsp; FAX {{ $defaultCompany->fax ?? '(0711) 311158' }}<br>
            NO FAKTUR: {{ $transaction->no_transaksi }}
            
            @if($transaction->status == 'canceled')
                <div class="status-badge status-canceled">DIBATALKAN</div>
            @endif
        </div>

        {{-- CUSTOMER --}}
        <div class="row">
            <div>
                <strong>Kpd Yth:</strong><br>
                Nama: {{ $transaction->customer->nama ?? '-' }}<br>
                Telp: {{ $transaction->customer->telepon ?? '-' }}<br>
                Alamat: {{ $transaction->customer->alamat ?? '-' }}
            </div>
            <div class="right">
                {{ \Carbon\Carbon::parse($transaction->tanggal)->format('d M Y') }}<br>
                HALAMAN: {{ $pageNum }} / {{ $totalPages }}
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
                @foreach ($chunk as $i => $item)
                <tr>
                    <td class="center">{{ (($pageNum - 1) * 10) + $i + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="center">{{ $item->diskon_persen ?? $item->diskon ?? 0 }}</td>
                    <td class="right">Rp {{ number_format($item->diskon ?? 0, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- FOOTER (ONLY LAST PAGE) --}}
        @if ($loop->last)
        <div class="note">
            <strong>PERHATIAN !!!</strong><br>
            Barang masih titipan dari {{ $defaultCompany->nama ?? 'CV. Alumka Cipta Prima' }}, bila belum dilunasi.<br>
            {{ $defaultCompany->catatan_nota ?? 'Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.' }}
        </div>
        
        <table>
            <tr>
                <th style="width: 70%">TOTAL</th>
                <td class="right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>DISCOUNT (%)</th>
                <td class="right">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>PPN</th>
                <td class="right">Rp {{ number_format($transaction->ppn, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>GRAND TOTAL</th>
                <td class="right"><strong>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
        </table>

        {{-- Terbilang --}}
        <div class="terbilang-section">
            <em>Terbilang: {{ ucwords(Terbilang::make($transaction->grand_total, ' rupiah')) }}</em>
        </div>

        {{-- Payment Info --}}
        <div class="payment-info">
            <div>Titipan Uang: Rp {{ number_format($transaction->dp, 0, ',', '.') }}</div>
            <div>Sisa Piutang: Rp {{ number_format($transaction->grand_total - $transaction->dp, 0, ',', '.') }}</div>
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

        @if($transaction->is_edited)
        <div class="edit-info-box">
            <strong>Informasi Edit:</strong><br>
            Diedit oleh: {{ $transaction->edited_by }} pada {{ \Carbon\Carbon::parse($transaction->edited_at)->format('d M Y H:i') }}<br>
            Alasan: {{ $transaction->edit_reason }}
        </div>
        @endif

        @if($transaction->status == 'canceled')
        <div class="edit-info-box">
            <strong>Informasi Pembatalan:</strong><br>
            Dibatalkan oleh: {{ $transaction->canceled_by }} pada {{ \Carbon\Carbon::parse($transaction->canceled_at)->format('d M Y H:i') }}<br>
            Alasan: {{ $transaction->cancel_reason }}
        </div>
        @endif
        
        @endif

    </div>

    {{-- BREAK PAGE KECUALI TERAKHIR --}}
    @if (!$loop->last)
    <div class="page-break"></div>
    @endif

    @php $pageNum++; @endphp
@endforeach


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