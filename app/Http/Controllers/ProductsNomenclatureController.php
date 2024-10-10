<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\Products;
use App\Models\Nomenclatures;
use App\Models\Products_Nomenclature;
use App\Models\Supplies;
use Inertia\Inertia;

class ProductsNomenclatureController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'nomenclatures' => 'required|array',
            'nomenclatures.*.id' => 'required|exists:nomenclatures,id',
            'nomenclatures.*.quantity' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $nomenclatureId = $request->input("nomenclatures.{$index}.id");
                    $unit = Supplies::where('nomenclature_id', $nomenclatureId)->value('unit');
                    
                    if ($unit === 'шт.' && !is_int($value)) {
                        $fail('Количество должно быть целым числом для единицы измерения "шт."');
                    }
                },
            ],
            'nomenclatures.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Products::findOrFail($request->product_id);

        foreach ($request->nomenclatures as $nomenclature) {
            if ($nomenclature['id'] && $nomenclature['quantity'] > 0) {
                // Здесь мы сохраняем цену за единицу, а не общую цену
                $pricePerUnit = $nomenclature['price'] / $nomenclature['quantity'];
                
                $product->nomenclatures()->attach($nomenclature['id'], [
                    'quantity' => $nomenclature['quantity'],
                    'price' => $pricePerUnit, // Сохраняем цену за единицу
                ]);
            }
        }

        // Пересчитываем себестоимость и общую цену продукта
        $product->setCostPrice()->setTotalPrice()->save();

        return response()->json(['status' => 'Данные добавлены'], 200);
    }

    public function getProductsNomenclatures()
    {
        $products = Products::with('nomenclatures')->get();
        $nomenclatures = Nomenclatures::all();

        return Response::json([
            'products' => $products,
            'nomenclatures' => $nomenclatures,
        ], 200);
    }

    public function deleteById(Request $request)
    {
        $product = Products::findOrFail($request->product_id);
        $product->nomenclatures()->detach($request->nomenclature_id);

        return Response::json(['status' => 'Связь успешно удалена'], 200);
    }

    public function getAvailableNomenclatures()
    {
        $availableNomenclatures = Nomenclatures::select('nomenclatures.id', 'nomenclatures.name', 'supplies.unit', 'nomenclatures.price_per_unit')
            ->join('supplies', 'nomenclatures.id', '=', 'supplies.nomenclature_id')
            ->distinct()
            ->get();

        return Response::json(['nomenclatures' => $availableNomenclatures], 200);
    }

    public function index()
    {
        $productsNomenclatures = Products_Nomenclature::with(['product', 'nomenclature'])->get();
        
        $formattedData = $productsNomenclatures->groupBy('product_id')->map(function ($group) {
            $product = $group->first()->product;
            $nomenclatures = $group->map(function ($item) {
                return [
                    'id' => $item->nomenclature->id,
                    'name' => $item->nomenclature->name,
                    'pivot' => [
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ],
                ];
            });
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'nomenclatures' => $nomenclatures,
            ];
        })->values();

        return Inertia::render('ProductsNomenclatures', [
            'productsNomenclatures' => $formattedData
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'nomenclature_id' => 'required|exists:nomenclatures,id',
        ]);

        $product = Products::findOrFail($request->product_id);
        $deleted = $product->nomenclatures()->detach($request->nomenclature_id);

        if ($deleted) {
            // Пересчитываем себестоимость и общую цену продукта
            $product->setCostPrice()->setTotalPrice()->save();
            return Response::json(['status' => 'Связь успешно удалена'], 200);
        } else {
            return Response::json(['status' => 'Не удалось удалить связь'], 400);
        }
    }
}
