@php
use Riskihajar\Terbilang\Facades\Terbilang;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Besar</title>
    <style>
        * { 
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        @page { 
            size: A4 portrait; 
            margin: 15mm; 
        }
        
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11pt; 
            line-height: 1.3; 
            color: #000; 
        }
        
        .container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header .company-name {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header .company-address {
            font-size: 10pt;
            margin-bottom: 2px;
        }
        
        .header .company-contact {
            font-size: 10pt;
            font-weight: bold;
        }
        
        /* Invoice Info */
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .invoice-info-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .invoice-info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .invoice-info-right table {
            margin-left: auto;
            border-collapse: collapse;
        }
        
        .invoice-info-right td {
            padding: 2px 5px;
            font-size: 10pt;
        }
        
        .invoice-info-right td:first-child {
            font-weight: bold;
            text-align: left;
        }
        
        .invoice-info-right td:last-child {
            text-align: left;
        }
        
        /* Customer Info */
        .customer-info {
            margin-bottom: 15px;
            font-size: 10pt;
        }
        
        .customer-info table {
            border-collapse: collapse;
        }
        
        .customer-info td {
            padding: 2px 5px;
        }
        
        .customer-info td:first-child {
            font-weight: bold;
            width: 80px;
        }
        
        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        
        .items-table th {
            border: 1px solid #000;
            padding: 8px 4px;
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .items-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: top;
        }
        
        .items-table .center {
            text-align: center;
        }
        
        .items-table .right {
            text-align: right;
        }
        
        /* Footer Section */
        .footer-section {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .footer-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .footer-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        
        .terbilang {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .notes {
            font-size: 9pt;
            line-height: 1.4;
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
        }
        
        .notes strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 15px;
        }
        
        .summary-table td {
            padding: 5px 8px;
            border: 1px solid #000;
        }
        
        .summary-table td:first-child {
            font-weight: bold;
            width: 60%;
        }
        
        .summary-table td:last-child {
            text-align: right;
            width: 40%;
        }
        
        .summary-table .grand-total {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        /* Signatures */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        
        .signature-col {
            display: table-cell;
            width: 33%;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-col strong {
            display: block;
            margin-bottom: 60px;
            font-size: 10pt;
        }
        
        .signature-col .signature-line {
            border-bottom: 1px solid #000;
            width: 150px;
            margin: 0 auto;
        }
        
        /* No Print */
        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 999;
        }
        
        .no-print button,
        .no-print a {
            margin-left: 5px;
            padding: 8px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            background: #007bff;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .no-print a {
            background: #6c757d;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

@php
    $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
@endphp

<div class="no-print">
    <a href="{{ route('transaksi.listnota') }}">Kembali</a>
    <button onclick="window.print()">Print</button>
    @if($transaction->status != 'canceled')
        <a href="{{ route('transaksi.edit', $transaction->id) }}">Edit</a>
    @endif
</div>

<div class="container">
    {{-- HEADER --}}
    <div class="header">
        <div class="company-name">{{ $defaultCompany->nama_perusahaan ?? 'GLOBAL TEKNIK' }}</div>
        <div class="company-address">
            {{ $defaultCompany->alamat ?? 'Jl. Hasanudin No. 102 (Ps. Kangkung) Teluk Betung - Bandar Lampung' }}
        </div>
        <div class="company-contact">
            Telp/Fax: {{ $defaultCompany->no_telp ?? '(0721) 484251' }}
            @if($defaultCompany->email)
                | Email: {{ $defaultCompany->email }}
            @endif
        </div>
    </div>

    {{-- INVOICE INFO --}}
    <div class="invoice-info">
        <div class="invoice-info-left">
            &nbsp;
        </div>
        <div class="invoice-info-right">
            <table>
                <tr>
                    <td>No. Nota</td>
                    <td>: {{ $transaction->no_transaksi }}</td>
                </tr>
                <tr>
                    <td>Jth Tempo</td>
                    <td>: {{ $transaction->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($transaction->tanggal_jatuh_tempo)->format('d-M-Y') : '-' }}</td>
                </tr>
                <tr>
                    <td colspan="2">{{ $defaultCompany->kota ?? 'Bandar Lampung' }}, {{ \Carbon\Carbon::parse($transaction->tanggal)->format('d-M-Y') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- CUSTOMER INFO --}}
    <div class="customer-info">
        <table>
            <tr>
                <td>Tuan</td>
                <td>: {{ $transaction->customer->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Toko</td>
                <td>: {{ $transaction->alamat_customer ?? $transaction->customer->alamat ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- ITEMS TABLE --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">Jml</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 32%;">Nama Barang</th>
                <th style="width: 15%;">Harga Satuan<br>(Rp)</th>
                <th style="width: 8%;">Disc<br>(%)</th>
                <th style="width: 12%;">Disc<br>(Rp)</th>
                <th style="width: 15%;">Sub Total<br>(Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->items as $item)
                <tr>
                    <td class="center">{{ number_format($item->qty, 0) }}</td>
                    <td class="center">{{ $item->kodeBarang->unit_dasar ?? $item->satuan ?? 'PCS' }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td class="right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="center">{{ $item->diskon_persen ?? 0 }}</td>
                    <td class="right">{{ number_format($item->diskon ?? 0, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            {{-- Baris kosong untuk ruang tambahan --}}
            @php
                $emptyRows = 8; // Jumlah baris kosong
            @endphp
            @for ($i = 0; $i < $emptyRows; $i++)
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
        </tbody>
    </table>

    {{-- FOOTER SECTION --}}
    <div class="footer-section">
        <div class="footer-left">
            {{-- TERBILANG --}}
            <div class="terbilang">
                #{{ ucwords(Terbilang::make($transaction->grand_total, ' rupiah')) }}#
            </div>

            {{-- NOTES --}}
            <div class="notes">
                <strong>Produk, Jumlah & Harga</strong>
                Telah diperiksa & sesuai<br>
                Barang yang sudah dibeli<br>
                tidak dapat dikembalikan/diretur
                @if($transaction->notes)
                    <br><br>
                    <strong>Catatan:</strong><br>
                    {{ $transaction->notes }}
                @endif
            </div>

            {{-- SIGNATURES --}}
            <div class="signatures">
                <div class="signature-col">
                    <strong>Penerima</strong>
                    <div class="signature-line"></div>
                    <div>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
                </div>
                <div class="signature-col">
                    &nbsp;
                </div>
                <div class="signature-col">
                    <strong>Hormat Kami</strong>
                    <div class="signature-line"></div>
                    <div>(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)</div>
                </div>
            </div>
        </div>

        <div class="footer-right">
            {{-- SUMMARY --}}
            <table class="summary-table">
                <tr>
                    <td>Total Rp.</td>
                    <td>{{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon Rp.</td>
                    <td>{{ number_format($transaction->discount, 0, ',', '.') }}</td>
                </tr>
                @if($transaction->ppn > 0)
                <tr>
                    <td>PPN ({{ $transaction->ppn_rate ?? 11 }}%)</td>
                    <td>{{ number_format($transaction->ppn, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($transaction->dp > 0)
                <tr>
                    <td>DP</td>
                    <td>{{ number_format($transaction->dp, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>Jumlah Rp.</td>
                    <td>{{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- EDIT/CANCEL INFO --}}
    @if($transaction->is_edited || $transaction->status == 'canceled')
    <div style="margin-top: 20px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffc107; font-size: 9pt;">
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
</div>

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
