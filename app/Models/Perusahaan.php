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
        'email',
        'website',
        'npwp',
        'catatan_nota',
        'catatan_surat_jalan',
        'logo',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Get active company
    public static function getActive()
    {
        return static::where('is_active', true)->first();
    }
}