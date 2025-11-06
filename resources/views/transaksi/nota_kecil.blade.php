@php
use Riskihajar\Terbilang\Facades\Terbilang;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Kecil</title>
    <link href="https://fonts.googleapis.com/css2?family=DejaVu+Sans+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        
        @page { 
            size: 8.5cm 11cm; 
            margin: 2mm; 
        }
        
        body { 
            font-family: 'DejaVu Sans Mono', monospace; 
            font-size: 7pt; 
            line-height: 0.9; 
            color: #000; 
            margin: 0; 
            padding: 0; 
        }
        
        .page { 
            width: 100%; 
            padding: 2mm; 
            display: flex; 
            flex-direction: column; 
        }
        
        .header { 
            text-align: center; 
            margin-bottom: 2mm; 
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
        }
        
        .header strong { 
            font-size: 8pt; 
            display: block;
            margin-bottom: 1mm;
        }
        
        .header .company-info {
            font-size: 6pt;
            line-height: 1.0;
        }
        
        .transaction-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2mm;
            font-size: 6pt;
        }
        
        .customer-info {
            margin-bottom: 2mm;
            font-size: 6pt;
        }
        
        .item-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 2mm; 
            font-size: 6pt;
        }
        
        .item-table th, 
        .item-table td { 
            border: 1px solid #000; 
            padding: 1mm; 
            vertical-align: top; 
        }
        
        .item-table th { 
            font-weight: bold; 
            background: #f0f0f0;
        }
        
        .item-table td.center { text-align: center; }
        .item-table td.right { text-align: right; }
        
        .summary-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 2mm; 
            font-size: 6pt;
        }
        
        .summary-table th, 
        .summary-table td { 
            border: 1px solid #000; 
            padding: 1mm; 
        }
        
        .summary-table th { 
            text-align: left; 
            font-weight: bold; 
        }
        
        .summary-table td.right { 
            text-align: right; 
        }
        
        .footer-info {
            font-size: 5pt;
            line-height: 1.0;
            margin-top: 2mm;
        }
        
        .terbilang {
            font-style: italic;
            margin: 1mm 0;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 3mm;
            font-size: 6pt;
        }
        
        .sign-col {
            text-align: center;
            width: 45%;
        }
        
        .sign-col strong {
            display: block;
            margin-bottom: 15px;
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
            padding: 4px 8px; 
            font-size: 10px; 
            border: none; 
            border-radius: 3px; 
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
    </style>
</head>
<body>

@php
    $defaultCompany = \App\Models\Perusahaan::where('is_default', true)->first() ?? new \App\Models\Perusahaan();
    $itemsPerPage = 8; // Lebih sedikit untuk nota kecil
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

        {{-- TRANSACTION INFO --}}
        <div class="transaction-info">
            <div>
                <strong>Faktur:</strong> {{ $transaction->no_transaksi }}<br>
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->tanggal)->format('d/M/Y') }}
            </div>
            <div>
                <strong>Salesman:</strong> {{ $transaction->salesman->keterangan ?? 'OFFICE' }}<br>
                <strong>Pembayaran:</strong> {{ $transaction->cara_bayar ?? 'Tunai' }}
            </div>
        </div>

        {{-- CUSTOMER INFO --}}
        <div class="customer-info">
            <strong>Kepada:</strong> {{ $transaction->customer->nama ?? '-' }}<br>
            {{ $transaction->customer->alamat ?? '-' }}
        </div>

        {{-- ITEM TABLE --}}
        <table class="item-table">
            <thead>
                <tr>
                    <th style="width: 55%;">Nama Barang</th>
                    <th style="width: 20%;" class="center">Qty</th>
                    <th style="width: 25%;" class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $rowCount = 0; @endphp
                @foreach ($chunk as $i => $item)
                    @php $rowCount++; @endphp
                    <tr>
                        <td>{{ $item->nama_barang }}</td>
                        <td class="center">{{ number_format($item->qty, 0) }} {{ $item->kodeBarang->unit_dasar ?? $item->satuan ?? 'PCS' }}</td>
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
                <th><strong>TOTAL</strong></th>
                <td class="right"><strong>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></td>
            </tr>
        </table>

        {{-- EDIT INFO --}}
        @if($transaction->is_edited || $transaction->status == 'canceled')
        <div style="font-size: 5pt; margin-top: 2mm; padding: 1mm; border: 1px solid #ccc;">
            @if($transaction->is_edited)
                <strong>Edit:</strong> {{ $transaction->edited_by }} - {{ \Carbon\Carbon::parse($transaction->edited_at)->format('d M Y H:i') }}<br>
                Alasan: {{ $transaction->edit_reason }}
            @endif
            @if($transaction->is_edited && $transaction->status == 'canceled') <br> @endif
            @if($transaction->status == 'canceled')
                <strong>Batal:</strong> {{ $transaction->canceled_by }} - {{ \Carbon\Carbon::parse($transaction->canceled_at)->format('d M Y H:i') }}<br>
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
