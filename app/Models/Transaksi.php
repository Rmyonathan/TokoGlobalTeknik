<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StokOwner;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    
    protected $fillable = [
        'no_transaksi',
        'no_po',
        'tanggal',
        'kode_customer',
        'sales_order_id',
        'sales',
        'pembayaran',
        'cara_bayar',
        'tanggal_jadi',
        'hari_tempo',
        'subtotal',
        'discount',
        'disc_rupiah',
        'ppn',
        'dp',
        'grand_total',
        'status',
        'status_piutang',
        'total_dibayar',
        'sisa_piutang',
        'tanggal_jatuh_tempo',
        'tanggal_pelunasan',
        'created_from_po',
        'created_from_multiple_sj',
        'is_edited',
        'edited_by',
        'edited_at',
        'edit_reason',
        'notes',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'tanggal_jadi' => 'datetime',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_pelunasan' => 'date',
        'hari_tempo' => 'integer',
        'total_dibayar' => 'decimal:2',
        'sisa_piutang' => 'decimal:2',
        'edited_at' => 'datetime',
        'is_edited' => 'boolean',
    ];

    public static function generateNoTransaksi(): string
    {
        // Format: KP/WS/0009 (4 digit increment, global)
        $prefix = 'KP/WS/';

        // Ambil transaksi terakhir dengan prefix ini
        $last = self::where('no_transaksi', 'like', $prefix . '%')
            ->orderBy('no_transaksi', 'desc')
            ->first();

        $nextNumber = 1;
        if ($last && strpos($last->no_transaksi, $prefix) === 0) {
            $numeric = (int) substr($last->no_transaksi, strlen($prefix));
            $nextNumber = $numeric + 1;
        }

        // Pastikan unik (jika ada race condition)
        do {
            $generated = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $exists = self::where('no_transaksi', $generated)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $generated;
    }

    public function items()
    {
        return $this->hasMany(TransaksiItem::class, 'transaksi_id');
    }

    // public function customer()
    // {
    //     return $this->belongsTo(Customer::class, 'cutomer_id', 'id');
    // }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'kode_customer');
    }

    /**
     * Relasi ke SalesOrder
     */
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    /**
     * Relasi ke salesman (stok_owners) menggunakan kolom kode
     */
    public function salesman()
    {
        return $this->belongsTo(StokOwner::class, 'sales', 'kode_stok_owner');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function pembayaranDetails()
    {
        return $this->hasMany(PembayaranDetail::class, 'transaksi_id');
    }

    // Business Logic Methods
    public function isLunas(): bool
    {
        return $this->status_piutang === 'lunas';
    }

    public function isSebagian(): bool
    {
        return $this->status_piutang === 'sebagian';
    }

    public function isBelumDibayar(): bool
    {
        return $this->status_piutang === 'belum_dibayar';
    }

    public function getPersentasePelunasanAttribute(): float
    {
        if ($this->grand_total == 0) return 0;
        return ($this->total_dibayar / $this->grand_total) * 100;
    }

    public function getSisaPiutangAttribute(): float
    {
        return $this->grand_total - $this->total_dibayar;
    }

    public function checkJatuhTempo(): bool
    {
        if (!$this->tanggal_jatuh_tempo) return false;
        return now()->isAfter($this->tanggal_jatuh_tempo);
    }

    public function getHariKeterlambatanAttribute(): int
    {
        if (!$this->tanggal_jatuh_tempo || !$this->checkJatuhTempo()) return 0;
        return now()->diffInDays($this->tanggal_jatuh_tempo);
    }

    /**
     * Accessor untuk menampilkan nama salesman langsung dari model
     */
    public function getNamaSalesmanAttribute(): string
    {
        return optional($this->salesman)->keterangan ?? '-';
    }

    // Scopes
    public function scopeLunas($query)
    {
        return $query->where('status_piutang', 'lunas');
    }

    public function scopeSebagian($query)
    {
        return $query->where('status_piutang', 'sebagian');
    }

    public function scopeBelumDibayar($query)
    {
        return $query->where('status_piutang', 'belum_dibayar');
    }

    public function scopeJatuhTempo($query)
    {
        return $query->where('tanggal_jatuh_tempo', '<', now());
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('kode_customer', $customerId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Hitung total COGS untuk transaksi ini
     */
    public function getTotalCogsAttribute(): float
    {
        $totalCogs = 0;
        
        foreach ($this->items as $item) {
            foreach ($item->sumber as $sumber) {
                $totalCogs += $sumber->qty_diambil * $sumber->harga_modal;
            }
        }
        
        return $totalCogs;
    }

    /**
     * Hitung total margin untuk transaksi ini
     */
    public function getTotalMarginAttribute(): float
    {
        return $this->grand_total - $this->total_cogs;
    }

    /**
     * Hitung persentase margin untuk transaksi ini
     */
    public function getMarginPercentageAttribute(): float
    {
        if ($this->grand_total == 0) return 0;
        return ($this->total_margin / $this->grand_total) * 100;
    }
}