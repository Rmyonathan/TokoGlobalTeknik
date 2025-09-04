<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba per Faktur - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</title>
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
            grid-template-columns: repeat(4, 1fr);
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
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
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
        <h1>LAPORAN LABA PER FAKTUR</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</h2>
    </div>

    <div class="info">
        <table>
            <tr>
                <td>Tanggal Cetak:</td>
                <td>{{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <td>Periode:</td>
                <td>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Summary -->
    <div class="summary">
        <h3>RINGKASAN KESELURUHAN</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <h4>{{ number_format($summary['total_faktur']) }}</h4>
                <p>Total Faktur</p>
            </div>
            <div class="summary-item">
                <h4>Rp {{ number_format($summary['total_omset'], 0, ',', '.') }}</h4>
                <p>Total Omset</p>
            </div>
            <div class="summary-item">
                <h4>Rp {{ number_format($summary['total_modal'], 0, ',', '.') }}</h4>
                <p>Total Modal</p>
            </div>
            <div class="summary-item">
                <h4 class="{{ $summary['total_laba_kotor'] >= 0 ? 'positive' : 'negative' }}">
                    Rp {{ number_format($summary['total_laba_kotor'], 0, ',', '.') }}
                </h4>
                <p>Total Laba Kotor</p>
            </div>
            <div class="summary-item">
                <h4>Rp {{ number_format($summary['total_ongkos_kuli'], 0, ',', '.') }}</h4>
                <p>Total Ongkos Kuli</p>
            </div>
            <div class="summary-item">
                <h4 class="{{ $summary['total_laba_bersih'] >= 0 ? 'positive' : 'negative' }}">
                    Rp {{ number_format($summary['total_laba_bersih'], 0, ',', '.') }}
                </h4>
                <p>Total Laba Bersih</p>
            </div>
            <div class="summary-item">
                <h4>{{ number_format($summary['margin_kotor_rata'], 2) }}%</h4>
                <p>Margin Kotor Rata-rata</p>
            </div>
            <div class="summary-item">
                <h4>{{ number_format($summary['margin_bersih_rata'], 2) }}%</h4>
                <p>Margin Bersih Rata-rata</p>
            </div>
        </div>
    </div>

    <!-- Detail Faktur -->
    <div class="section">
        <h3>DETAIL PER FAKTUR</h3>
        @if(count($laporanData) > 0)
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No Transaksi</th>
                    <th>Customer</th>
                    <th class="text-right">Omset</th>
                    <th class="text-right">Modal</th>
                    <th class="text-right">Laba Kotor</th>
                    <th class="text-right">Ongkos Kuli</th>
                    <th class="text-right">Laba Bersih</th>
                    <th class="text-right">Margin Kotor</th>
                    <th class="text-right">Margin Bersih</th>
                    <th>Status Piutang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporanData as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['tanggal'] }}</td>
                    <td>{{ $row['no_transaksi'] }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td class="text-right">{{ number_format($row['omset'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['modal'], 0, ',', '.') }}</td>
                    <td class="text-right {{ $row['laba_kotor'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($row['laba_kotor'], 0, ',', '.') }}
                    </td>
                    <td class="text-right">{{ number_format($row['ongkos_kuli'], 0, ',', '.') }}</td>
                    <td class="text-right {{ $row['laba_bersih'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($row['laba_bersih'], 0, ',', '.') }}
                    </td>
                    <td class="text-right">{{ number_format($row['margin_kotor'], 2) }}%</td>
                    <td class="text-right">{{ number_format($row['margin_bersih'], 2) }}%</td>
                    <td class="text-center">
                        @if($row['status_piutang'] == 'lunas')
                            <span style="color: #28a745; font-weight: bold;">LUNAS</span>
                        @elseif($row['status_piutang'] == 'sebagian')
                            <span style="color: #ffc107; font-weight: bold;">SEBAGIAN</span>
                        @else
                            <span style="color: #dc3545; font-weight: bold;">BELUM</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td colspan="4" class="text-center">TOTAL</td>
                    <td class="text-right">{{ number_format($summary['total_omset'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($summary['total_modal'], 0, ',', '.') }}</td>
                    <td class="text-right {{ $summary['total_laba_kotor'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($summary['total_laba_kotor'], 0, ',', '.') }}
                    </td>
                    <td class="text-right">{{ number_format($summary['total_ongkos_kuli'], 0, ',', '.') }}</td>
                    <td class="text-right {{ $summary['total_laba_bersih'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($summary['total_laba_bersih'], 0, ',', '.') }}
                    </td>
                    <td class="text-right">{{ number_format($summary['margin_kotor_rata'], 2) }}%</td>
                    <td class="text-right">{{ number_format($summary['margin_bersih_rata'], 2) }}%</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        @else
        <div class="no-data">Tidak ada data untuk periode ini</div>
        @endif
    </div>

    <!-- Analisis ROI -->
    <div class="section page-break">
        <h3>ANALISIS ROI (RETURN ON INVESTMENT)</h3>
        <div class="summary">
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>{{ number_format($summary['roi'], 2) }}%</h4>
                    <p>ROI Keseluruhan</p>
                </div>
                <div class="summary-item">
                    <h4>Rp {{ number_format($summary['total_modal'], 0, ',', '.') }}</h4>
                    <p>Total Investasi (Modal)</p>
                </div>
                <div class="summary-item">
                    <h4 class="{{ $summary['total_laba_bersih'] >= 0 ? 'positive' : 'negative' }}">
                        Rp {{ number_format($summary['total_laba_bersih'], 0, ',', '.') }}
                    </h4>
                    <p>Keuntungan Bersih</p>
                </div>
                <div class="summary-item">
                    <h4>{{ number_format($summary['total_faktur']) }}</h4>
                    <p>Jumlah Faktur</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background-color: #e9ecef; border-radius: 5px;">
            <h4 style="margin: 0 0 10px 0; font-size: 14px;">Interpretasi ROI:</h4>
            <p style="margin: 0; font-size: 12px;">
                @if($summary['roi'] > 20)
                    <strong style="color: #28a745;">Sangat Baik</strong> - ROI di atas 20% menunjukkan kinerja yang sangat baik.
                @elseif($summary['roi'] > 10)
                    <strong style="color: #17a2b8;">Baik</strong> - ROI antara 10-20% menunjukkan kinerja yang baik.
                @elseif($summary['roi'] > 0)
                    <strong style="color: #ffc107;">Cukup</strong> - ROI positif menunjukkan masih menguntungkan.
                @else
                    <strong style="color: #dc3545;">Perhatian</strong> - ROI negatif menunjukkan kerugian, perlu evaluasi.
                @endif
            </p>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
