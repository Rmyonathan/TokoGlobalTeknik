@php
use Riskihajar\Terbilang\Facades\Terbilang;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        @page {
            size: 21.59cm 13.97cm;
            margin: 0mm;
        }

        body {
            font-family: 'Press Start 2P', monospace;
            font-size: 7pt;
            line-height: 1;
            color: #000;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .page {
            width: 95%;
            /* PERUBAHAN FINAL: Padding samping dikurangi lagi menjadi 3mm agar tidak terpotong */
            padding: 8mm 3mm 5mm 3mm;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .header { text-align: center; line-height: 1.1; }
        .header strong { font-size: 9pt; }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 7pt; }

        .item-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .item-table th,
        .item-table td { border: 1px solid #000; padding: 2px 3px; font-size: 7pt; vertical-align: top; }
        .item-table th { font-weight: bold; }

        tr.empty-row td {
            border: 1px solid #000;
            border-color: #eee;
            color: #fff;
        }
        tr.empty-row td:first-child { border-left-color: #000; }
        tr.empty-row td:last-child { border-right-color: #000; }

        .footer-container {
            width: 100%;
            margin-top: auto;
            padding-top: 5px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-bottom: 3px;
        }
        .summary-table th, .summary-table td { border: 1px solid #000; padding: 2px 3px; }
        .summary-table th { text-align: left; }

        .notes-details-wrapper {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            width: 100%;
            margin-top: 3px;
        }

        .notes-section {
            flex-basis: 70%;
            padding-right: 10px;
            font-size: 7pt;
            line-height: 1.1;
            padding: 2px;
        }

        .details-row {
            flex-basis: 30%;
            font-size: 7pt;
        }
        .payment-info { }
        .terbilang-section { font-style: italic; margin-top: 5px; }

        .signature-row {
            width: 100%;
            overflow: hidden;
            margin-top: 10px;
            font-size: 9pt;
        }
        .signature-left { float: left; text-align: center; }
        .signature-right { float: right; text-align: center; }

        .edit-info-box { font-size: 7px; margin-top: 8px; padding: 2px; border: 1px solid #ccc; line-height: 1.0; clear: both; }

        .right { text-align: right; }
        .center { text-align: center; }

        .no-print { position: fixed; top: 10px; right: 10px; z-index: 999; }
        .no-print button, .no-print a { margin-left: 8px; padding: 6px 16px; font-size: 14px; border: none; border-radius: 4px; background: #007bff; color: #fff; cursor: pointer; text-decoration: none; }
        .no-print a { background: #6c757d; }

        .page-break { page-break-after: always; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

@php
    $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
    $itemsPerPage = 5;
    $groupedItems = $transaction->items->chunk($itemsPerPage);
    $totalPages = $groupedItems->count();
    $pageNum = 0;
@endphp

<div class="no-print">
    <a href="{{ route('transaksi.listnota') }}">Kembali</a>
    <button onclick="window.print()">Print</button>
    @if($transaction->status != 'canceled')
        <a href="{{ route('transaksi.edit', $transaction->id) }}">Edit</a>
    @endif
</div>

@foreach ($groupedItems as $chunk)
    @php $pageNum++; @endphp
    <div class="page">
        {{-- BAGIAN HEADER --}}
        <div class="header">
            <strong>{{ $defaultCompany->nama ?? 'CV. ALUMKA CIPTA PRIMA' }}</strong><br>
            {{ $defaultCompany->alamat ?? 'JL. SINAR RAGA ABI HASAN NO.1553 RT.022 RW.008' }}<br>
            {{ $defaultCompany->kota ?? '8 ILIR' }}, {{ $defaultCompany->kode_pos ?? 'ILIR TIMUR II' }}<br>
            TELP. {{ $defaultCompany->telepon ?? '(0711) 311158' }} &nbsp;&nbsp; FAX {{ $defaultCompany->fax ?? '(0711) 311158' }}<br>
            NO FAKTUR: {{ $transaction->no_transaksi }}
        </div>

        {{-- INFO PELANGGAN & TANGGAL --}}
        <div class="info-row">
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

        {{-- TABEL DAFTAR BARANG --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 14%;">Kode Barang</th>
                    <th style="width: 28%;">Nama Barang</th>
                    <th style="width: 6%;">Qty</th>
                    <th style="width: 6%;">Satuan</th>
                    <th style="width: 14%;">Harga Satuan</th>
                    <th style="width: 7%;">Disc %</th>
                    <th style="width: 10%;">Disc Rp</th>
                    <th style="width: 10%;">Sub Total</th>
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
                        <td class="center">{{ $item->satuan ?? '-' }}</td>
                        <td class="right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="center">{{ $item->diskon_persen ?? 0 }}</td>
                        <td class="right">Rp {{ number_format($item->diskon ?? 0, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- TEKNIK BARIS KOSONG OTOMATIS --}}
                @if ($loop->last)
                    @for ($j = $rowCount; $j < $itemsPerPage; $j++)
                        <tr class="empty-row">
                            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                            <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        {{-- HANYA TAMPILKAN FOOTER DI HALAMAN TERAKHIR --}}
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
                    {{-- <strong>PERHATIAN !!!</strong><br>
                    Barang masih titipan dari {{ $defaultCompany->nama ?? 'CV. Alumka Cipta Prima' }}, bila belum dilunasi. Pembayaran dengan Cek, Giro, Slip dan lainnya akan dianggap lunas bila dapat diuangkan. --}}
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
                <div class="signature-left">
                    HORMAT KAMI<br><br><br><br>
                    (_____________)
                </div>
                <div class="signature-right">
                    PENERIMA<br><br><br><br>
                    (_____________)
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

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
