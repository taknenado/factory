<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products_Nomenclature extends Model
{
    use HasFactory;

    protected $table = 'products__nomenclatures';

    protected $fillable = ['product_id', 'nomenclature_id', 'quantity', 'price'];

    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

    public function nomenclature()
    {
        return $this->belongsTo(Nomenclatures::class, 'nomenclature_id');
    }
}