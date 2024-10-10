<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'markup', 'price', 'total_price'];

    public function nomenclatures()
    {
        return $this->belongsToMany(Nomenclatures::class, 'products__nomenclatures', 'product_id', 'nomenclature_id')
                    ->withPivot('quantity', 'price');
    }

    public function calculateCostPrice()
    {
        // Расчет себестоимости
        return $this->nomenclatures->sum(function ($nomenclature) {
            return $nomenclature->pivot->price * $nomenclature->pivot->quantity;
        });
    }

    public function calculateTotalPrice()
    {
        // Расчет итоговой цены с учетом наценки
        $costPrice = $this->calculateCostPrice();
        return $costPrice * (1 + $this->markup / 100);
    }

    public function calculateTotalNomenclaturePrice()
    {
        return $this->nomenclatures->sum(function ($nomenclature) {
            return $nomenclature->pivot->price * $nomenclature->pivot->quantity;
        });
    }

    public function setCostPrice()
    {
        $totalCost = $this->nomenclatures->sum(function ($nomenclature) {
            return $nomenclature->pivot->price * $nomenclature->pivot->quantity;
        });
        $this->price = $totalCost;
        return $this;
    }

    public function setTotalPrice()
    {
        $this->total_price = $this->calculateTotalPrice();
        return $this;
    }
}
