<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranPiutangNotaKredit extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_piutang_nota_kredits';

    protected $fillable = [
        'pembayaran_id',
        'nota_kredit_id',
        'no_nota_kredit',
        'total_nota_kredit',
        'jumlah_digunakan',
        'sisa_nota_kredit',
        'keterangan',
    ];

    protected $casts = [
        'total_nota_kredit' => 'decimal:2',
        'jumlah_digunakan' => 'decimal:2',
        'sisa_nota_kredit' => 'decimal:2',
    ];

    // Relationships
    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function notaKredit(): BelongsTo
    {
        return $this->belongsTo(NotaKredit::class, 'nota_kredit_id');
    }
}
