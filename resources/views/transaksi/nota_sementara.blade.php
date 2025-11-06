@php
use Riskihajar\Terbilang\Facades\Terbilang;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Sementara</title>
    <link href="https://fonts.googleapis.com/css2?family=DejaVu+Sans+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        
        @page { 
            size: A4 portrait; 
            margin: 8mm; 
        }
        
        body { 
            font-family: 'DejaVu Sans Mono', monospace; 
            font-size: 8pt; 
            line-height: 1.1; 
            color: #000; 
            margin: 0; 
            padding: 0; 
        }
        
        .page { 
            width: 100%; 
            padding: 4mm; 
            display: flex; 
            flex-direction: column; 
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 4mm; 
            border-bottom: 2px solid #000;
            padding-bottom: 2mm;
        }
        
        .header strong { 
            font-size: 12pt; 
            display: block;
            margin-bottom: 1mm;
        }
        
        .header .company-info {
            font-size: 8pt;
            line-height: 1.2;
        }
        
        .warning-banner {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 3mm;
            margin-bottom: 4mm;
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: #856404;
        }
        
        .warning-banner .warning-text {
            font-size: 12pt;
            color: #dc3545;
            margin-bottom: 1mm;
        }
        
        .transaction-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3mm;
            font-size: 8pt;
            background: #f8f9fa;
            padding: 2mm;
            border: 1px solid #000;
        }
        
        .customer-info {
            margin-bottom: 3mm;
            font-size: 8pt;
            background: #f8f9fa;
            padding: 2mm;
            border: 1px solid #000;
        }
        
        .customer-info strong {
            font-size: 9pt;
        }
        
        .item-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 3mm; 
            font-size: 7pt;
        }
        
        .item-table th, 
        .item-table td { 
            border: 1px solid #000; 
            padding: 1.5mm; 
            vertical-align: top; 
        }
        
        .item-table th { 
            font-weight: bold; 
            background: #e9ecef;
            text-align: center;
        }
        
        .item-table td.center { text-align: center; }
        .item-table td.right { text-align: right; }
        
        .summary-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 3mm; 
            font-size: 8pt;
        }
        
        .summary-table th, 
        .summary-table td { 
            border: 1px solid #000; 
            padding: 2mm; 
        }
        
        .summary-table th { 
            text-align: left; 
            font-weight: bold; 
            background: #e9ecef;
        }
        
        .summary-table td.right { 
            text-align: right; 
        }
        
        .payment-status {
            background: #f8d7da;
            border: 2px solid #dc3545;
            padding: 3mm;
            margin-bottom: 3mm;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            color: #721c24;
        }
        
        .terbilang {
            font-style: italic;
            margin: 2mm 0;
            padding: 2mm;
            background: #f8f9fa;
            border: 1px solid #000;
            font-size: 7pt;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 6mm;
            font-size: 8pt;
        }
        
        .sign-col {
            text-align: center;
            width: 30%;
            border: 1px solid #000;
            padding: 2mm;
        }
        
        .sign-col strong {
            display: block;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        
        .sign-col .signature-line {
            border-bottom: 1px solid #000;
            height: 15px;
            margin: 8px 0;
        }
        
        .footer-note {
            margin-top: 4mm;
            padding: 3mm;
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            font-size: 7pt;
            color: #0c5460;
        }
        
        .footer-note strong {
            color: #0c5460;
        }
        
        .no-print { 
            position: fixed; 
            top: 10px; 
            right: 10px; 
            z-index: 999; 
        }
        
        .no-print button, 
        .no-print a { 
            margin-left: 5px; 
            padding: 6px 12px; 
            font-size: 12px; 
            border: none; 
            border-radius: 4px; 
            background: #007bff; 
            color: #fff; 
            cursor: pointer; 
            text-decoration: none; 
        }
        
        .no-print a { 
            background: #6c757d; 
        }
        
        @media print { 
            .no-print { 
                display: none !important; 
            } 
        }
        
        .page-break { 
            page-break-after: always; 
        }
        
        .edit-info {
            margin-top: 3mm;
            padding: 2mm;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            font-size: 7pt;
        }
        
        .edit-info strong {
            color: #721c24;
        }
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
        
        {{-- HEADER --}}
        <div class="header">
            <strong>{{ $defaultCompany->nama ?? 'GLOBALTEKNIK' }}</strong>
            <div class="company-info">
                {{ $defaultCompany->alamat ?? 'Jl. Contoh No. 123' }}<br>
                {{ $defaultCompany->kota ?? 'Jakarta' }}{{ $defaultCompany->kode_pos ? ', '.$defaultCompany->kode_pos : '' }}<br>
                @if(!empty($defaultCompany->telepon)) TELP. {{ $defaultCompany->telepon }} @endif
            </div>
        </div>

        {{-- WARNING BANNER --}}
        <div class="warning-banner">
            <div class="warning-text">⚠️ NOTA SEMENTARA ⚠️</div>
            <div>NOTA INI BELUM DILUNASI</div>
            <div style="font-size: 8pt; margin-top: 1mm;">
                Pembayaran belum diterima secara penuh. Silakan lakukan pembayaran sesuai dengan ketentuan yang berlaku.
            </div>
        </div>

        {{-- TRANSACTION INFO --}}
        <div class="transaction-info">
            <div>
                <strong>No. Faktur:</strong> {{ $transaction->no_transaksi }}<br>
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->tanggal)->format('d F Y') }}<br>
                <strong>Salesman:</strong> {{ $transaction->salesman->keterangan ?? 'OFFICE' }}
            </div>
            <div>
                <strong>Pembayaran:</strong> {{ $transaction->cara_bayar ?? 'Tunai' }}<br>
                @if($transaction->tanggal_jatuh_tempo)
                <strong>Jatuh Tempo:</strong> {{ \Carbon\Carbon::parse($transaction->tanggal_jatuh_tempo)->format('d F Y') }}<br>
                @endif
                @if(!empty($transaction->hari_tempo))
                <strong>Hari Tempo:</strong> {{ $transaction->hari_tempo }} hari
                @endif
            </div>
        </div>

        {{-- CUSTOMER INFO --}}
        <div class="customer-info">
            <strong>KEPADA YTH:</strong><br>
            <strong>{{ $transaction->customer->nama ?? '-' }}</strong><br>
            {{ $transaction->customer->alamat ?? '-' }}<br>
            @if(!empty($transaction->customer->telepon))
            Telp: {{ $transaction->customer->telepon }}
            @endif
        </div>

        {{-- ITEM TABLE --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%;">Kode</th>
                    <th style="width: 40%;">Nama Barang</th>
                    <th style="width: 10%;" class="center">Qty</th>
                    <th style="width: 10%;" class="center">Satuan</th>
                    <th style="width: 12%;" class="right">Harga @</th>
                    <th style="width: 13%;" class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $rowCount = 0; @endphp
                @foreach ($chunk as $i => $item)
                    @php $rowCount++; @endphp
                    <tr>
                        <td class="center">{{ (($pageNum - 1) * $itemsPerPage) + $i + 1 }}</td>
                        <td>{{ $item->kode_barang }}</td>
                        <td>{{ $item->nama_barang }}</td>
                        <td class="center">{{ number_format($item->qty, 2) }}</td>
                        <td class="center">{{ $item->satuan ?? 'PCS' }}</td>
                        <td class="right">{{ $item->harga == 0 ? 'Bonus' : 'Rp '.number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                {{-- EMPTY ROWS --}}
                @if ($loop->last)
                    @for ($j = $rowCount; $j < $itemsPerPage; $j++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>

        {{-- SUMMARY --}}
        @if ($loop->last)
        <table class="summary-table">
            <tr>
                <th style="width: 70%;">SUBTOTAL</th>
                <td class="right" style="width: 30%;">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>DISKON</th>
                <td class="right">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>PPN</th>
                <td class="right">Rp {{ number_format($transaction->ppn, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th><strong>TOTAL KESELURUHAN</strong></th>
                <td class="right"><strong>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
        </table>

        {{-- PAYMENT STATUS --}}
        <div class="payment-status">
            STATUS PEMBAYARAN: BELUM LUNAS<br>
            Uang Muka: Rp {{ number_format($transaction->dp, 0, ',', '.') }} | 
            Sisa Piutang: Rp {{ number_format($transaction->grand_total - $transaction->dp, 0, ',', '.') }}
        </div>

        {{-- TERBILANG --}}
        <div class="terbilang">
            <strong>Terbilang:</strong> {{ ucwords(Terbilang::make($transaction->grand_total, ' rupiah')) }}
        </div>

        {{-- SIGNATURES --}}
        <div class="signatures">
            <div class="sign-col">
                <strong>PENERIMA BARANG</strong>
                <div class="signature-line"></div>
                <small>Tanda Tangan & Nama Jelas</small>
            </div>
            <div class="sign-col">
                <strong>HORMAT KAMI</strong>
                <div class="signature-line"></div>
                <small>Tanda Tangan & Nama Jelas</small>
            </div>
            <div class="sign-col">
                <strong>MENGETAHUI</strong>
                <div class="signature-line"></div>
                <small>Manager / Supervisor</small>
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <div class="footer-note">
            <strong>PERHATIAN PENTING:</strong><br>
            • Nota ini adalah dokumen sementara dan belum dilunasi<br>
            • Pembayaran dapat dilakukan melalui transfer bank atau tunai<br>
            • Setelah pembayaran lunas, akan diterbitkan nota asli<br>
            • Simpan nota ini sebagai bukti transaksi sementara<br>
            • Untuk informasi lebih lanjut, hubungi bagian keuangan
        </div>

        {{-- EDIT INFO --}}
        @if($transaction->is_edited || $transaction->status == 'canceled')
        <div class="edit-info">
            @if($transaction->is_edited)
                <strong>INFORMASI EDIT:</strong><br>
                Diedit oleh: {{ $transaction->edited_by }} pada {{ \Carbon\Carbon::parse($transaction->edited_at)->format('d F Y H:i') }}<br>
                Alasan: {{ $transaction->edit_reason }}
            @endif
            @if($transaction->is_edited && $transaction->status == 'canceled') <br><br> @endif
            @if($transaction->status == 'canceled')
                <strong>INFORMASI PEMBATALAN:</strong><br>
                Dibatalkan oleh: {{ $transaction->canceled_by }} pada {{ \Carbon\Carbon::parse($transaction->canceled_at)->format('d F Y H:i') }}<br>
                Alasan: {{ $transaction->cancel_reason }}
            @endif
        </div>
        @endif

        @endif
    </div>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

@if(request('auto_print'))
<script>
    window.addEventListener('load', function () {
        setTimeout(function(){
            window.print();
            setTimeout(function(){ window.close(); }, 500);
        }, 200);
    });
</script>
@endif

</body>
</html>
