<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\OrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/order', [OrderController::class, 'order']);
Route::post('/products', [OrderController::class, 'show_products']);
Route::post('/categories', [OrderController::class, 'show_categories']);
Route::post('/specials', [OrderController::class, 'show_special_offers']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/sensor', [AdminController::class, 'sensor']);
    Route::post('/addcategory', [AdminController::class, 'add_category']);
    Route::post('/addproduct', [AdminController::class, 'add_product']);
    Route::post('/editorder', [AdminController::class, 'update_order']);

});