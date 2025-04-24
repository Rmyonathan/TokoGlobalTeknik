<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h3 { background: #007bff; color: white; padding: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        .text-right { text-align: right; }
        .text-success { color: green; }
        .footer { margin-top: 20px; text-align: center; font-style: italic; }
    </style>
</head>
<body>
    <h3>Invoice Transaksi</h3>

    <p><strong>No Transaksi:</strong> {{ $transaction->no_transaksi }}</p>
    <p><strong>Tanggal:</strong> {{ $transaction->tanggal }}</p>
    <p><strong>Customer:</strong> {{ $transaction->customer->nama ?? 'N/A' }}</p>
    <p><strong>Total:</strong> <span class="text-success">Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</span></p>

    <h4>Detail Barang</h4>
    <table>
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Diskon</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->items as $item)
            <tr>
                <td>{{ $item->kode_barang }}</td>
                <td>{{ $item->nama_barang }}</td>
                <td>{{ $item->qty }}</td>
                <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->diskon, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h4>Rincian Transaksi</h4>
    <table>
        <tr>
            <th>Subtotal</th>
            <td>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Diskon Transaksi</th>
            <td>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>PPN (11%)</th>
            <td>Rp {{ number_format($transaction->ppn, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>DP</th>
            <td>Rp {{ number_format($transaction->dp, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Grand Total</th>
            <td><strong>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="footer">
        Terima kasih telah bertransaksi dengan kami!
    </div>
</body>
</html>
