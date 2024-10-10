<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Nomenclatures;

class NomenclaturesController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'supplier_id' => ['required', 'integer'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        $nomenclature = new Nomenclatures;

        $nomenclature->name = $request->name;
        $nomenclature->supplier_id = $request->supplier_id;
        $nomenclature->price_per_unit = $request->price_per_unit;
        $nomenclature->total_quantity = 0;
        $nomenclature->total_price = 0;

        $nomenclature->save();

        return Response::json(['status' => 'Данные добавлены'], 200);
    }

    public function getNomenclatures()
    {
        $nomenclatures = Nomenclatures::with('supplier')->get();
        return Response::json(['nomenclatures' => $nomenclatures], 200);
    }

    public function deleteById(Request $request)
    {
        $nomenclature = Nomenclatures::find($request->nomenclature_id);
        if (!$nomenclature) {
            return Response::json(['status' => 'Ошибка, элемент не найден'], 404);
        }
        $nomenclature->delete();
        return Response::json(['status' => 'Номенклатура успешно удалена'], 200);
    }
}
