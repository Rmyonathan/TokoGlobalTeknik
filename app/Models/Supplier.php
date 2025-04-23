<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['kode_supplier', 'nama', 'alamat', 'pemilik', 'telepon_fax','contact_person', 'hp_contact_person', 'kode_kategori'];
}
