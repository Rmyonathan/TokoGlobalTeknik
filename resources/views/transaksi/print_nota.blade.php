<?php
use Riskihajar\Terbilang\Facades\Terbilang;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi Print</title>
    {{-- Changed font to Roboto for better readability and space efficiency --}}
    <link href="https://fonts.googleapis.com/css2?family=DejaVu+Sans+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Global box-sizing for consistent layout */
        * { box-sizing: border-box; }

        /* Define the paper size and margins for printing */
        @page {
            /* Statement size (8.5in x 5.5in) in cm, forced landscape */
            size: 21.59cm 13.97cm landscape;
            margin: 0mm; /* Minimal margins for full control */
        }

        /* Basic body styling for print */
        body {
            font-family: 'DejaVu Sans Mono', monospace; /* Or 'Open Sans', sans-serif; */
            font-size: 8pt; /* Slightly reduced base font size to fit content */
            line-height: 0.8; /* Very tight line height */
            color: #000;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
        }

        /* Page container styling, adjusted for the new paper size */
        .page {
            width: 90%; /* Keeping width at 90% as requested */
            height: 13.97cm; /* Explicitly set height for landscape Statement */
            padding: 5mm 8mm 3mm 8mm; /* Reduced top/bottom padding to gain vertical space */
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            box-sizing: border-box;
            margin: 3mm auto 0 auto; /* Added 2mm margin to the top, still centers horizontally */
        }

        /* Header styling */
        .header { text-align: center; line-height: 1.0; margin-bottom: 2mm; } /* Tighter line height, reduced margin */
        .header strong { font-size: 8.5pt; } /* Slightly reduced header font size */

        /* Information rows (customer, date, page numbers) */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1mm; /* Reduced margin */
            font-size: 6.8pt; /* Consistent font size */
            width: 100%;
        }

        /* Specific widths for the two sections within info-row */
        .info-left-section {
            flex-basis: 65%;
            text-align: left;
            flex-shrink: 0;
        }
        .info-right-section {
            flex-basis: 35%;
            text-align: right;
            flex-shrink: 0;
        }


        /* Item table styling */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5mm; /* Reduced margin */
            table-layout: fixed;
        }
        .item-table th,
        .item-table td {
            border: 1px solid #000;
            padding: 0.5mm 1mm; /* FURTHER REDUCED PADDING for cells */
            font-size: 7.2pt; /* Consistent font size */
            vertical-align: top;
            word-wrap: break-word;
            white-space: normal;
        }
        .item-table th { font-weight: bold; }

        /* ADJUSTED COLUMN WIDTHS FOR BETTER FIT */
        .item-table th:nth-child(1), .item-table td:nth-child(1) { width: 5%; text-align: center; } /* No. */
        .item-table th:nth-child(2), .item-table td:nth-child(2) { width: 12%; } /* Kode Barang */
        .item-table th:nth-child(3), .item-table td:nth-child(3) { width: 32%; } /* Nama Barang */
        .item-table th:nth-child(4), .item-table td:nth-child(4) { width: 5%; text-align: center; } /* Qty */
        .item-table th:nth-child(5), .item-table td:nth-child(5) { width: 13%; text-align: right; } /* Harga Satuan */
        .item-table th:nth-child(6), .item-table td:nth-child(6) { width: 6%; text-align: center; } /* Disc % */
        .item-table th:nth-child(7), .item-table td:nth-child(7) { width: 10%; text-align: right; } /* Disc Rp */
        .item-table th:nth-child(8), .item-table td:nth-child(8) { width: 17%; text-align: right; } /* Sub Total - TAKES MORE SPACE */

        /* Styling for empty rows to maintain table structure */
        tr.empty-row td {
            border: 1px solid #000;
            border-color: #eee;
            color: #fff;
        }
        tr.empty-row td:first-child { border-left-color: #000; }
        tr.empty-row td:last-child { border-right-color: #000; }

        /* Footer container to push to the bottom of the page */
        .footer-container {
            width: 100%;
            margin-top: auto;
            padding-top: 1.5mm; /* Reduced padding */
        }

        /* Summary table styling */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.2pt; /* Consistent font size */
            margin-bottom: 1mm; /* Reduced margin */
        }
        .summary-table th, .summary-table td { border: 1px solid #000; padding: 1mm 1.5mm; } /* Reduced padding */
        .summary-table th { font-weight: bold; text-align: left; }

        /* Wrapper for notes and payment details */
        .notes-details-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            width: 100%;
            margin-top: 1.5mm; /* Reduced margin */
        }

        /* Notes section styling */
        .notes-section {
            flex-basis: 60%;
            padding-right: 2mm; /* Reduced padding */
            font-size: 7pt; /* Consistent font size */
            line-height: 1.05;
            padding: 0.8mm; /* Reduced padding */
        }

        /* Payment details section styling */
        .details-row {
            flex-basis: 40%;
            font-size: 7pt; /* Consistent font size */
            text-align: right;
            padding-left: 2mm; /* Reduced padding */
        }
        .payment-info { margin-bottom: 0.8mm; } /* Reduced margin */
        .terbilang-section { font-style: italic; }

        /* Signature section styling */
        .signature-row {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 4mm; /* Reduced margin */
            font-size: 7.5pt; /* Slightly larger font for signatures */
            padding: 0 5mm;
        }
        .signature-column {
            flex: 1;
            text-align: center;
            padding: 0 5mm;
            line-height: 1.05;
            position: relative;
        }
        .signature-text {
            margin-top: 12px; /* Reduced space for actual signature */
            display: inline-block;
            padding: 0 6px; /* Reduced padding for the line */
            border-bottom: 1px solid #000;
            min-width: 80%;
            text-align: center;
        }
        .signature-label {
            margin-bottom: 10px; /* Reduced space above the signature line */
        }


        /* Edit/Cancellation info box */
        .edit-info-box {
            font-size: 6pt; /* Consistent font size */
            margin-top: 2.5mm; /* Reduced margin */
            padding: 0.5mm;
            border: 1px solid #ccc;
            line-height: 1.0;
            clear: both;
        }

        /* Text alignment utility classes */
        .right { text-align: right; }
        .center { text-align: center; }

        /* Page break for multi-page documents */
        .page-break { page-break-after: always; }

        /* Hide any elements with no-print class when printing */
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

@php
    $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
    $itemsPerPage = 5; // Number of items per page on the invoice
    $groupedItems = $transaction->items->chunk($itemsPerPage);
    $totalPages = $groupedItems->count();
    $pageNum = 0;
@endphp

{{-- Loop through item chunks to create pages --}}
@foreach ($groupedItems as $chunk)
    @php $pageNum++; @endphp
    <div class="page">
        {{-- INVOICE HEADER SECTION --}}
        <div class="header">
            <strong>{{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}</strong><br>
            {{ $defaultCompany->alamat ?? 'JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008' }}<br>
            {{ $defaultCompany->kota ?? '8 ILIR' }}, {{ $defaultCompany->kode_pos ?? 'ILIR TIMUR II' }}<br>
            TELP. {{ $defaultCompany->telepon ?? '(0711) 311158' }} &nbsp;&nbsp; FAX {{ $defaultCompany->fax ?? '(0711) 311158' }}<br>
            NO FAKTUR: {{ $transaction->no_transaksi }}
        </div>

        {{-- CUSTOMER AND DATE INFORMATION --}}
        <div class="info-row">
            <div class="info-left-section">
                <strong>Kpd Yth:</strong><br>
                Nama: {{ $transaction->customer->nama ?? '-' }}<br>
                Telp: {{ $transaction->customer->telepon ?? '-' }}<br>
                Alamat: {{ $transaction->customer->alamat ?? '-' }}
            </div>
            <div class="info-right-section">
                {{ \Carbon\Carbon::parse($transaction->tanggal)->format('d M Y') }}<br>
                HALAMAN: {{ $pageNum }} / {{ $totalPages }}
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <table class="item-table">
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
                @php $rowCount = 0; @endphp
                @foreach ($chunk as $i => $item)
                    @php $rowCount++; @endphp
                    <tr>
                        <td class="center">{{ (($pageNum - 1) * $itemsPerPage) + $i + 1 }}</td>
                        <td>{{ $item->kode_barang }}</td>
                        <td>{{ $item->keterangan }}</td>
                        <td class="center">{{ $item->qty }}</td>
                        <td class="right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="center">{{ $item->diskon_persen ?? 0 }}</td>
                        <td class="right">Rp {{ number_format($item->diskon ?? 0, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- Fill remaining rows with empty ones if needed --}}
                @if ($loop->last)
                    @for ($j = $rowCount; $j < $itemsPerPage; $j++)
                        <tr class="empty-row">
                            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        {{-- FOOTER SECTION (ONLY ON LAST PAGE) --}}
        @if ($loop->last)
        <div class="footer-container">

            <table class="summary-table">
                <tr>
                    <th style="width: 85%">TOTAL</th>
                    <td class="right" style="width: 15%">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>DISCOUNT</th>
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

            <div class="notes-details-wrapper">
                <div class="notes-section">
                    <strong>PERHATIAN !!!</strong><br>
                    Barang masih titipan dari {{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}, bila belum dilunasi. Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan.
                </div>

                <div class="details-row">
                    <div class="payment-info">
                        Titipan Uang: Rp {{ number_format($transaction->dp, 0, ',', '.') }}<br>
                        Sisa Piutang: Rp {{ number_format($transaction->grand_total - $transaction->dp, 0, ',', '.') }}
                    </div>
                    <div class="terbilang-section">
                        Terbilang: {{ ucwords(Terbilang::make($transaction->grand_total, ' rupiah')) }}
                    </div>
                </div>
            </div>

            <div class="signature-row">
                <div class="signature-column">
                    <div class="signature-label">HORMAT KAMI</div>
                    <div class="signature-text"></div>
                </div>
                <div class="signature-column">
                    <div class="signature-label">PENERIMA</div>
                    <div class="signature-text"></div>
                </div>
            </div>

            @if($transaction->is_edited || $transaction->status == 'canceled')
            <div class="edit-info-box">
                @if($transaction->is_edited)
                    <strong>Informasi Edit:</strong> Diedit oleh: {{ $transaction->edited_by }} pada {{ \Carbon\Carbon::parse($transaction->edited_at)->format('d M Y H:i') }}<br>Alasan: {{ $transaction->edit_reason }}
                @endif
                @if($transaction->is_edited && $transaction->status == 'canceled') <br> @endif
                @if($transaction->status == 'canceled')
                    <strong>Informasi Pembatalan:</strong> Dibatalkan oleh: {{ $transaction->canceled_by }} pada {{ \Carbon\Carbon::parse($transaction->canceled_at)->format('d M Y H:i') }}<br>Alasan: {{ $transaction->cancel_reason }}
                @endif
            </div>
            @endif

        </div>
        @endif
    </div>

    {{-- Add page break if it's not the last page --}}
    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
