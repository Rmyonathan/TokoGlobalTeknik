<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranUtangSupplierNotaDebit extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_utang_supplier_nota_debits';

    protected $fillable = [
        'pembayaran_utang_supplier_id',
        'nota_debit_id',
        'no_nota_debit',
        'total_nota_debit',
        'jumlah_digunakan',
        'sisa_nota_debit',
        'keterangan',
    ];

    protected $casts = [
        'total_nota_debit' => 'decimal:2',
        'jumlah_digunakan' => 'decimal:2',
        'sisa_nota_debit' => 'decimal:2',
    ];

    // Relationships
    public function pembayaranUtangSupplier(): BelongsTo
    {
        return $this->belongsTo(PembayaranUtangSupplier::class, 'pembayaran_utang_supplier_id');
    }

    public function notaDebit(): BelongsTo
    {
        return $this->belongsTo(NotaDebit::class, 'nota_debit_id');
    }
}
