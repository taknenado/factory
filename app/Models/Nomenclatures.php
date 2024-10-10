<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nomenclatures extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'supplier_id', 'price_per_unit', 'total_quantity', 'total_price'];

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class);
    }

    public function products()
    {
        return $this->belongsToMany(Products::class, 'products__nomenclatures', 'nomenclature_id', 'product_id')
                ->withPivot('quantity', 'price');
    }
}
