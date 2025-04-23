<?php

// filepath: c:\Users\Thomas\Desktop\Pekerjaan\Atapjeri1\app\Models\Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'stocks'];
}
