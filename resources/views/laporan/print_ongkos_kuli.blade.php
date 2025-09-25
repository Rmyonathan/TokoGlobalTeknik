<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Expense - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
            border-collapse: collapse;
        }
        .info td {
            padding: 5px;
            border: none;
        }
        .info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 10px;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 3px;
        }
        .summary-item h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: bold;
        }
        .summary-item p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        .page-break {
            page-break-before: always;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN EXPENSE</h1>
        <h2>Dari {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</h2>
        <p style="margin: 5px 0 0 0; font-size: 12px;">
            <strong>Waktu Cetak:</strong> {{ now()->format('d/m/Y H:i:s') }} | 
            <strong>Sales:</strong> Sernua
        </p>
    </div>

    @if($data->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Faktur</th>
                    <th>Tgl</th>
                    <th>Pelanggan</th>
                    <th>Sales</th>
                    <th>Wilayah</th>
                    <th class="text-right">Kuantiti</th>
                    <th class="text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentSales = null;
                    $rowNumber = 1;
                    $salesSubtotal = 0;
                    $grandTotal = 0;
                @endphp
                
                @foreach($data as $row)
                    @if($currentSales !== $row->sales_nama)
                        @if($currentSales !== null)
                            <!-- Subtotal untuk sales sebelumnya -->
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="6" class="text-right">Salesman Total:</td>
                                <td class="text-right">{{ number_format($salesSubtotal, 2, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            @php $grandTotal += $salesSubtotal; $salesSubtotal = 0; @endphp
                        @endif
                        
                        <!-- Header Sales baru -->
                        <tr style="background-color: #e9ecef; font-weight: bold;">
                            <td colspan="8">Sales: {{ $row->sales_nama ?? 'UNKNOWN' }}</td>
                        </tr>
                        @php $currentSales = $row->sales_nama; @endphp
                    @endif
                    
                    <tr>
                        <td class="text-center">{{ $rowNumber++ }}</td>
                        <td>{{ $row->no_transaksi }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $row->customer_nama ?? '-' }}</td>
                        <td>{{ $row->sales_nama ?? '-' }}</td>
                        <td>{{ $row->wilayah ?? '-' }}</td>
                        <td class="text-right">{{ number_format($row->qty, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row->ongkos_kuli, 2, ',', '.') }}</td>
                    </tr>
                    @php $salesSubtotal += $row->ongkos_kuli; @endphp
                @endforeach
                
                @if($currentSales !== null)
                    <!-- Subtotal untuk sales terakhir -->
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <td colspan="6" class="text-right">Salesman Total:</td>
                        <td class="text-right">{{ number_format($salesSubtotal, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                    @php $grandTotal += $salesSubtotal; @endphp
                @endif
                
                <!-- Grand Total -->
                <tr style="background-color: #dee2e6; font-weight: bold; font-size: 14px;">
                    <td colspan="6" class="text-right">Grand Total:</td>
                    <td class="text-right">{{ number_format($grandTotal, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @else
    <div class="no-data">Tidak ada data untuk periode ini</div>
    @endif

    <!-- Footer -->
    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
        <p>Print Date Time: {{ now()->format('m/d/Y H:i') }}</p>
        <p>Hal: Page 1 of 1</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
