<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranUtangSupplierDetail extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_utang_supplier_details';

    protected $fillable = [
        'pembayaran_utang_supplier_id',
        'pembelian_id',
        'no_pembelian',
        'total_faktur',
        'sudah_dibayar',
        'jumlah_dilunasi',
        'sisa_tagihan',
        'status_pelunasan',
        'keterangan',
    ];

    protected $casts = [
        'total_faktur' => 'decimal:2',
        'sudah_dibayar' => 'decimal:2',
        'jumlah_dilunasi' => 'decimal:2',
        'sisa_tagihan' => 'decimal:2',
    ];

    // Relationships
    public function pembayaranUtangSupplier(): BelongsTo
    {
        return $this->belongsTo(PembayaranUtangSupplier::class, 'pembayaran_utang_supplier_id');
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    // Static methods
    public static function updatePembelianUtangStatus($pembelianId): void
    {
        $pembelian = Pembelian::find($pembelianId);
        if (!$pembelian) return;

        // Hitung total yang sudah dibayar (uang tunai)
        $totalDibayar = self::where('pembelian_id', $pembelianId)
            ->sum('jumlah_dilunasi');

        // Hitung total nota debit yang digunakan untuk pembelian ini
        $totalNotaDebitDigunakan = \App\Models\PembayaranUtangSupplierNotaDebit::whereHas('pembayaranUtangSupplier', function($query) use ($pembelianId) {
            $query->whereHas('details', function($q) use ($pembelianId) {
                $q->where('pembelian_id', $pembelianId);
            });
        })->sum('jumlah_digunakan');

        // Sisa utang = (grand_total - total_nota_debit_digunakan) - total_dibayar
        // Nota debit mengurangi tagihan, bukan menambah pembayaran
        $tagihanSetelahNotaDebit = $pembelian->grand_total - $totalNotaDebitDigunakan;
        $sisaUtang = $tagihanSetelahNotaDebit - $totalDibayar;
        
        // Jika sisa utang negatif, berarti ada kelebihan pembayaran
        // Ini bisa terjadi jika ada retur setelah faktur lunas
        if ($sisaUtang < 0) {
            $sisaUtang = 0; // Set ke 0 untuk display
        }

        // Update pembelian status
        $pembelian->update([
            'total_dibayar' => $totalDibayar,
            'sisa_utang' => $sisaUtang,
            'status_utang' => $sisaUtang <= 0 ? 'lunas' : ($totalDibayar > 0 || $totalNotaDebitDigunakan > 0 ? 'sebagian' : 'belum_dibayar')
        ]);
    }
}
