<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\OrderController;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

Route::post('/order', [OrderController::class, 'order']);
Route::post('/products', [OrderController::class, 'show_products']);
Route::post('/categories', [OrderController::class, 'show_categories']);
Route::post('/specials', [OrderController::class, 'show_special_offers']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/sensor', [AdminController::class, 'getSensorData']);
    Route::post('/orders', [OrderController::class, 'show_orders']);
    Route::post('/addcategory', [AdminController::class, 'add_category']);
    Route::post('/addproduct', [AdminController::class, 'add_product']);
    Route::post('/updateproduct', [AdminController::class, 'update_product']);
    Route::post('/editorder', [AdminController::class, 'update_order']);
    Route::post('/warnings', [AdminController::class, 'showWarnings']);
    Route::post('/addsensor', [AdminController::class, 'addSensor']);
    Route::post('/sensors/dates', [AdminController::class, 'getDates']);

});