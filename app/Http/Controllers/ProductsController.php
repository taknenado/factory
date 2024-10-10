<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProductsController extends Controller
{
    public function getProducts()
    {
        $products = Products::with('nomenclatures')->get();
        $products->each(function ($product) {
            try {
                $product->setCostPrice()->setTotalPrice()->save();
            } catch (\Exception $e) {
                $product->fill([
                    'price' => 0,
                    'total_price' => 0
                ])->save();
            }
        });
        return Response::json(['products' => $products], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'markup' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return Inertia::render('Products', [
                'errors' => $validator->errors(),
            ])->withViewData(['error' => 'Validation failed']);
        }

        $product = new Products;
        $product->name = $request->name;
        $product->markup = $request->markup;
        $product->save();

        return Inertia::render('Products', [
            'flash' => [
                'message' => 'Продукт успешно добавлен',
                'product' => $product,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:50'],
            'markup' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $product = Products::findOrFail($id);
        $product->name = $request->name;
        $product->markup = $request->markup;
        $product->save();

        return Inertia::render('Products', [
            'flash' => [
                'message' => 'Продукт успешно обновлен',
            ],
        ]);
    }

    public function delete($id)
    {
        $product = Products::findOrFail($id);
        $product->delete();

        return Response::json(['status' => 'Продукт успешно удален'], 200);
    }

    public function getProductDetails($id)
    {
        $product = Products::with('nomenclatures')->findOrFail($id);
        try {
            $product->setCostPrice()->setTotalPrice();
            $product->total_nomenclature_price = $product->calculateTotalNomenclaturePrice();
        } catch (\Exception $e) {
            $product->price = 0;
            $product->total_price = 0;
            $product->total_nomenclature_price = 0;
        }

        return Response::json(['product' => $product], 200);
    }
}
