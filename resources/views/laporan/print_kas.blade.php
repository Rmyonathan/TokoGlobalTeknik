<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .summary { margin-bottom: 20px; }
        .summary-item { display: inline-block; margin-right: 20px; }
        .footer { margin-top: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KAS</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i:s') }}</p>
        @if($type)
        <p>Filter Tipe: {{ $type }}</p>
        @endif
    </div>

    <div class="summary">
        <div class="summary-item"><strong>Total Debit:</strong> Rp {{ number_format($summary['total_debit'], 0, ',', '.') }}</div>
        <div class="summary-item"><strong>Total Kredit:</strong> Rp {{ number_format($summary['total_kredit'], 0, ',', '.') }}</div>
        <div class="summary-item"><strong>Total Bonus:</strong> Rp {{ number_format($summary['total_bonus'], 0, ',', '.') }}</div>
        <div class="summary-item"><strong>Total Hutang:</strong> Rp {{ number_format($summary['total_hutang'], 0, ',', '.') }}</div>
        <div class="summary-item"><strong>Saldo Akhir:</strong> Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Saldo</th>
                <th>Manual/Sistem</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->type }}</td>
                <td class="text-right">Rp {{ number_format($item->qty, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->saldo, 0, ',', '.') }}</td>
                <td>{{ $item->is_manual ? 'Manual' : 'Sistem' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data kas untuk periode yang dipilih</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($groupByType) && $groupByType->count() > 0)
    <h3>Ringkasan per Tipe</h3>
    <table>
        <thead>
            <tr>
                <th>Tipe</th>
                <th>Jumlah Transaksi</th>
                <th>Total</th>
                <th>Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupByType as $typeData)
            <tr>
                <td>{{ $typeData['type'] }}</td>
                <td>{{ $typeData['count'] }}</td>
                <td class="text-right">Rp {{ number_format($typeData['total'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($typeData['avg'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem</p>
    </div>
</body>
</html>
