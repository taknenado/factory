<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\NomenclaturesController;
use App\Http\Controllers\SuppliesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProductsNomenclatureController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

// dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


// suppliers
    Route::get('/suppliers', function () {
        return Inertia::render('Suppliers');
    })->name('suppliers');
    Route::post('/add-suppliers', [SuppliersController::class, 'store'])->name('add-suppliers');
    Route::get('/get-suppliers', [SuppliersController::class, 'getSuppliers'])->name('get-suppliers');
    Route::post('/delete-suppliers', [SuppliersController::class, 'deleteById'])->name('delete-suppliers');
    Route::post('/update-suppliers', [SuppliersController::class, 'updateById'])->name('update-suppliers');


// supplies
    Route::get('/supplies', function () {
        return Inertia::render('Supplies');
    })->name('supplies');

    Route::get('/get-supplies', [SuppliesController::class, 'getSupplies'])->name('get-supplies');
    Route::get('/get-nomenclatures', [SuppliesController::class, 'getNomenclatures'])->name('get-nomenclatures');
    Route::post('/add-supply', [SuppliesController::class, 'store'])->name('add-supply');
    Route::post('/update-supply', [SuppliesController::class, 'update'])->name('update-supply');
    Route::post('/delete-supply', [SuppliesController::class, 'delete'])->name('delete-supply');


// nomenclatures
    Route::get('/nomenclature', function () {
        return Inertia::render('Nomenclature');
    })->name('nomenclature');


    Route::post('/add-nomenclature', [NomenclaturesController::class, 'store'])->name('add-nomenclature');
    Route::get('/get-nomenclature', [NomenclaturesController::class, 'getNomenclatures'])->name('get-nomenclature');
    Route::post('/delete-nomenclature', [NomenclaturesController::class, 'deleteById'])->name('delete-nomenclature');
    Route::post('/update-nomenclature', [NomenclaturesController::class, 'updateById'])->name('update-nomenclature');

    // products 
    Route::get('/products', function () {
        return Inertia::render('Products');
    })->name('products');

    Route::get('/get-products', [ProductsController::class, 'getProducts'])->name('products.index');
    Route::post('/add-product', [ProductsController::class, 'store'])->name('products.store');
    Route::post('/update-product/{id}', [ProductsController::class, 'update'])->name('products.update');
    Route::delete('/delete-product/{id}', [ProductsController::class, 'delete'])->name('products.delete');
    Route::get('/get-product-details/{id}', [ProductsController::class, 'getProductDetails'])->name('products.details');
    Route::get('/get-available-nomenclatures', [ProductsNomenclatureController::class, 'getAvailableNomenclatures'])->name('get-available-nomenclatures');
    Route::get('/get-product-details/{id}', [ProductsController::class, 'getProductDetails'])->name('get-product-details');
    // products nomenclatures
    Route::get('/products-nomenclatures', [ProductsNomenclatureController::class, 'index'])->name('products_nomenclatures');
    Route::get('/get-products-nomenclatures', [ProductsNomenclatureController::class, 'getProductsNomenclatures'])->name('get-products-nomenclatures');
    Route::post('/add-products-nomenclatures', [ProductsNomenclatureController::class, 'store'])->name('add-products-nomenclatures');
    Route::post('/update-products-nomenclatures', [ProductsNomenclatureController::class, 'update'])->name('update-products-nomenclatures');
    Route::post('/delete-products-nomenclatures', [ProductsNomenclatureController::class, 'delete'])->name('delete-products-nomenclatures');
    
});