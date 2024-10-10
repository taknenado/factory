<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suppliers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class SuppliersController extends Controller
{
    public function store(Request $request)
    {
    
        
        $validated = $request->validate([
            'supplierName' => ['required', 'max:50'],
            'address' => ['required', 'max:100'],
            'supplierComments' => ['required', 'max:500'],
            'phoneNumber' => ['required', 'integer'],
        ]);


        $pattern = '/^\d{11}$/';

        if (preg_match($pattern, $request->phoneNumber)) {
           
            $suppliers = new Suppliers;
            $suppliers->name = $request->supplierName;
            $suppliers->address = $request->address;
            $suppliers->comments = $request->supplierComments;
            $suppliers->phone = $request->phoneNumber;
            $suppliers->save();
            return Response::json(['status' => 'Данные добавлены', 'isOk' => True], 200);
        } else {
            return Response::json(['status' => 'Номер телефона недействителен.','isOk' => False], 200);
        }

    }

    public function getSuppliers(){

        $suppliers = Suppliers::all();
        return Response::json(['suppliers' => $suppliers], 200);

    }

    public function deleteById(Request $request){

        $flight = Suppliers::findOr($request->suppliers_id, function () {
              return Response::json(['status' => 'Ошибка, элемент не найден'], 200);
        });
            Suppliers::destroy($request->suppliers_id);
            return Response::json(['status' => 'Поставщик успешно удален'], 200);
    }



    public function updateById(Request $request){

        $flight = Suppliers::findOr($request->supplierId, function () {
            return Response::json(['status' => 'Ошибка, элемент не найден', 'isOk' => False], 200);
        });
        $pattern = '/^\d{11}$/';

        if (preg_match($pattern, $request->phoneNumber)) {
           
            $flight->name = $request->supplierName;
            $flight->address = $request->address;
            $flight->comments = $request->supplierComments;
            $flight->phone = $request->phoneNumber;
            $flight->save();
            return Response::json(['status' => 'Поставщик успешно изменен', 'isOk' => True], 200);

        } else {
            return Response::json(['status' => 'Номер телефона недействителен.','isOk' => False], 200);
        }
           
    }

}
