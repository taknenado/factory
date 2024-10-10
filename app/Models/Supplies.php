<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplies extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomenclature_id',
        'supply_date',
        'quantity',
        'unit',
        'price'
    ];

    public function nomenclature()
    {
        return $this->belongsTo(Nomenclatures::class);
    }
}
