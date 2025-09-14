<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    protected $table = 'perusahaan';

    protected $fillable = [
        'nama',
        'alamat',
        'kota',
        'kode_pos',
        'telepon',
        'fax',
        'bri_account',
        'bca_account',
        'catatan_nota',
        'catatan_surat_jalan',
        'is_active',
        'ppn_enabled',
        'ppn_rate'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ppn_enabled' => 'boolean',
        'ppn_rate' => 'decimal:2',
    ];

    // Get active company
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }
}