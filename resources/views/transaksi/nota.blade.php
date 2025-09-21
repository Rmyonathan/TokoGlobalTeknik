@php
use Riskihajar\Terbilang\Facades\Terbilang;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi</title>
    <link href="https://fonts.googleapis.com/css2?family=DejaVu+Sans+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Match print_nota layout */
        * { box-sizing: border-box; }
        @page { size: 21.59cm 13.97cm landscape; margin: 0mm; }
        body { font-family: 'DejaVu Sans Mono', monospace; font-size: 8pt; line-height: 0.8; color: #000; margin: 0; padding: 0; display: flex; flex-direction: column; width: 100%; height: 100%; }
        .page { width: 90%; padding: 3mm 6mm 1mm 6mm; margin: 1mm auto 0 auto; display: flex; flex-direction: column; }
        .header { text-align: center; line-height: 1.0; margin-bottom: 2mm; }
        .header strong { font-size: 8.5pt; }
        .info-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1mm; font-size: 6.8pt; width: 100%; }
        .info-left-section { flex-basis: 65%; text-align: left; }
        .info-right-section { flex-basis: 35%; text-align: right; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 1.5mm; table-layout: fixed; }
        .item-table th, .item-table td { border: 1px solid #000; padding: 0.5mm 1mm; font-size: 7.2pt; vertical-align: top; word-wrap: break-word; white-space: normal; }
        .item-table th { font-weight: bold; }
        .item-table th:nth-child(1), .item-table td:nth-child(1) { width: 5%; text-align: center; }
        .item-table th:nth-child(2), .item-table td:nth-child(2) { width: 35%; }
        .item-table th:nth-child(3), .item-table td:nth-child(3) { width: 10%; text-align: center; }
        .item-table th:nth-child(4), .item-table td:nth-child(4) { width: 15%; text-align: center; }
        .item-table th:nth-child(5), .item-table td:nth-child(5) { width: 15%; text-align: right; }
        .item-table th:nth-child(6), .item-table td:nth-child(6) { width: 20%; text-align: right; }
        tr.empty-row td { border: 1px solid #000; border-color: #eee; color: #fff; }
        tr.empty-row td:first-child { border-left-color: #000; }
        tr.empty-row td:last-child { border-right-color: #000; }
        .footer-container { width: 100%; margin-top: auto; padding-top: 1.5mm; }
        .summary-table { width: 100%; border-collapse: collapse; font-size: 7.2pt; margin-bottom: 1mm; }
        .summary-table th, .summary-table td { border: 1px solid #000; padding: 1mm 1.5mm; }
        .summary-table th { text-align: left; font-weight: bold; }
        .notes-details-wrapper { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: nowrap; width: 100%; margin-top: 1.5mm; }
        .notes-section { flex: 1 1 0%; min-width: 55%; padding-right: 2mm; font-size: 7pt; line-height: 1.05; padding: 0.8mm; }
        .details-row { flex: 0 1 auto; min-width: 40%; display: flex; flex-direction: column; font-size: 7pt; text-align: right; padding-left: 2mm; }
        .payment-info { margin-bottom: 0.8mm; }
        .terbilang-section { font-style: italic; }
        .signature-row { width: 100%; display: flex; justify-content: space-between; margin-top: 100px; font-size: 9pt; padding-top: 50px; }
        .signature-left { float: left; text-align: center; width: 20%; }
        .signature-right { float: right; text-align: center; width: 20%; }
        .edit-info-box { font-size: 6pt; margin-top: 2.5mm; padding: 0.5mm; border: 1px solid #ccc; line-height: 1.0; clear: both; }
        .right { text-align: right; } .center { text-align: center; }
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
    $itemsPerPage = 10;
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
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="text-align: left;">
                    <strong>{{ $defaultCompany->nama ?? '' }}</strong><br>
                    {{ $defaultCompany->alamat ?? '' }}<br>
                    {{ $defaultCompany->kota ?? '' }}{{ $defaultCompany->kode_pos ? ', '.$defaultCompany->kode_pos : '' }}<br>
                    @if(!empty($defaultCompany->telepon)) TELP. {{ $defaultCompany->telepon }} @endif
                    @if(!empty($defaultCompany->fax)) &nbsp;&nbsp; FAX {{ $defaultCompany->fax }} @endif
                </div>
                <div style="text-align: right;">
                    {{ \Carbon\Carbon::parse($transaction->tanggal)->format('d/M/Y') }}<br>
                    HALAMAN: {{ $pageNum }} / {{ $totalPages }}
                </div>
            </div>
        </div>

        {{-- INFO PELANGGAN & TRANSAKSI --}}
        <table style="width: 100%; table-layout: fixed; margin-bottom: 10px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <strong>Kepada Yth: {{ $transaction->customer->nama ?? '-' }}</strong><br>
                    {{ $transaction->customer->alamat ?? '-' }}
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%; font-size: 8pt;">
                        <tr>
                            <td style="width: 30%;"><strong>Faktur:</strong></td>
                            <td style="width: 70%;">{{ $transaction->no_transaksi }}</td>
                        </tr>
                        <tr>
                            <td><strong>Salesman:</strong></td>
                            <td>{{ $transaction->salesman->keterangan ?? 'OFFICE' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pengirim:</strong></td>
                            <td>{{ $defaultCompany->nama ?? '' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pembayaran:</strong></td>
                            <td>{{ $transaction->cara_bayar ?? 'Tunai' }}</td>
                        </tr>
                        @if(isset($suratJalans) && $suratJalans->count())
                        <tr>
                            <td><strong>Referensi SJ:</strong></td>
                            <td>
                                {{ $suratJalans->pluck('no_suratjalan')->join(', ') }}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>No PO (Faktur):</strong></td>
                            <td>{{ $transaction->no_po ?? '-' }}</td>
                        </tr>
                        @php
                            $poPerSj = $suratJalans->map(function($sj){ return ($sj->no_po ? ($sj->no_suratjalan.' → '.$sj->no_po) : ($sj->no_suratjalan.' → -')); })->join(', ');
                            $distinctPo = $suratJalans->pluck('no_po')->filter()->unique();
                        @endphp
                        @if($distinctPo->count() > 1)
                        <tr>
                            <td><strong>No PO per SJ:</strong></td>
                            <td>{{ $poPerSj }}</td>
                        </tr>
                        @endif
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        {{-- TABEL DAFTAR BARANG --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Barang</th>
                    <th>Ball</th>
                    <th>Kuantiti</th>
                    <th>Harga @</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php $rowCount = 0; @endphp
                @foreach ($chunk as $i => $item)
                    @php $rowCount++; @endphp
                    <tr>
                        <td class="center">{{ (($pageNum - 1) * $itemsPerPage) + $i + 1 }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td class="center">{{ ceil($item->qty / 25) }}</td>
                        <td class="center">{{ number_format($item->qty, 2) }} {{ $item->satuan ?? 'PAK' }}</td>
                        <td class="right">@if($item->harga == 0) Bonus @else Rp {{ number_format($item->harga, 0, ',', '.') }} @endif</td>
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
