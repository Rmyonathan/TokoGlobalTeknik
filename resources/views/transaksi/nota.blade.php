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
            // Jika ada query ?print=1, langsung print
            if (window.location.search.includes('print=1')) {
                window.print();
            }
        });
    </script>
    <style>
       @page {
            size: 21.59cm 14cm;
            margin: 8mm; /* Reduced margin from default */
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10px; /* Reduced from 12px */
            line-height: 1.1; /* Reduced from 1.3 */
            margin: 0;
            padding: 0;
        }

        .header, .footer {
            text-align: center;
            margin-bottom: 8px; /* Reduced margin */
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px; /* Reduced from 5px */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px; /* Reduced from 8px */
            margin-bottom: 5px;
        }

        th, td {
            border: 1px solid black;
            padding: 2px; /* Reduced from 3.3px */
            font-size: 9px; /* Smaller font for table */
        }

        .right { text-align: right; }
        .center { text-align: center; }

        .note {
            font-size: 9px; /* Reduced from 11px */
            margin-top: 8px; /* Reduced from 10px */
            margin-bottom: 5px;
            line-height: 1.1;
        }

        .signature {
            margin-top: 15px; /* Reduced from 30px */
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }

        .page-break {
            page-break-after: always;
        }

        /* Compact spacing for specific elements */
        .header strong {
            font-size: 12px;
        }

        .header br {
            line-height: 0.8;
        }

        /* Make the summary table more compact */
        table:last-of-type th,
        table:last-of-type td {
            padding: 1.5px;
            font-size: 9px;
        }

        /* Reduce spacing in customer info */
        .row div {
            line-height: 1.1;
        }

        /* Status badges - make them smaller */
        .status-badge {
            font-size: 8px;
            padding: 2px 4px;
            margin-top: 3px;
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
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
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

        <br>
        
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

        {{-- Tambahkan Terbilang --}}
        <div style="margin-top: 5px; margin-bottom: 10px; margin-right: 5px; font-style: italic; text-align: right;">
            <em>Terbilang: {{ ucwords(Terbilang::make($transaction->grand_total, ' rupiah')) }}</em>
        </div>

        {{-- Pindahkan informasi Titipan & Sisa Piutang --}}
        <div style="margin-bottom: 10px;">
            <div>Titipan Uang: Rp {{ number_format($transaction->dp, 0, ',', '.') }}</div>
            <div>Sisa Piutang: Rp {{ number_format($transaction->grand_total - $transaction->dp, 0, ',', '.') }}</div>
        </div>

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