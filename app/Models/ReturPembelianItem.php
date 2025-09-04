<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturPembelianItem extends Model
{
    protected $table = 'retur_pembelian_items';

    protected $fillable = [
        'retur_pembelian_id',
        'pembelian_item_id',
        'kode_barang',
        'nama_barang',
        'qty_retur',
        'satuan',
        'harga',
        'total',
        'alasan',
    ];

    protected $casts = [
        'qty_retur' => 'decimal:2',
        'harga' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function returPembelian(): BelongsTo
    {
        return $this->belongsTo(ReturPembelian::class);
    }

    public function pembelianItem(): BelongsTo
    {
        return $this->belongsTo(PembelianItem::class);
    }

    public function kodeBarang(): BelongsTo
    {
        return $this->belongsTo(KodeBarang::class, 'kode_barang', 'kode_barang');
    }

    // Method untuk menghitung qty yang tersisa setelah retur
    public function getQtyTersisaAttribute()
    {
        if (!$this->pembelianItem) {
            return 0;
        }

        // Hitung total retur untuk item ini (termasuk retur yang sudah ada)
        $totalRetur = ReturPembelianItem::where('pembelian_item_id', $this->pembelian_item_id)
            ->where('retur_pembelian_id', '!=', $this->retur_pembelian_id) // Exclude current retur
            ->sum('qty_retur');

        // Qty tersisa = qty asli - total retur sebelumnya
        $qtyTersisa = $this->pembelianItem->qty - $totalRetur;
        
        return max(0, $qtyTersisa); // Pastikan tidak negatif
    }

    // Method untuk menghitung qty yang tersisa untuk item pembelian tertentu
    public static function getQtyTersisaForPembelianItem($pembelianItemId)
    {
        $pembelianItem = PembelianItem::find($pembelianItemId);
        if (!$pembelianItem) {
            return 0;
        }

        // Hitung total retur untuk item ini
        $totalRetur = ReturPembelianItem::where('pembelian_item_id', $pembelianItemId)
            ->whereHas('returPembelian', function($query) {
                $query->whereIn('status', ['approved', 'processed']);
            })
            ->sum('qty_retur');

        // Qty tersisa = qty asli - total retur
        $qtyTersisa = $pembelianItem->qty - $totalRetur;
        
        return max(0, $qtyTersisa); // Pastikan tidak negatif
    }
}
