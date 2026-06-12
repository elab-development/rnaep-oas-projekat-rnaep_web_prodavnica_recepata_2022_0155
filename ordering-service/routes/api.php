<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/orders',              [OrderController::class, 'index']);
Route::post('/orders',             [OrderController::class, 'store']);
Route::get('/orders/{id}',         [OrderController::class, 'show']);
Route::put('/orders/{id}',         [OrderController::class, 'update']);

Route::get('/cart',                [CartController::class, 'show']);
Route::post('/cart/items',         [CartController::class, 'addItem']);
Route::delete('/cart/items/{cartItem}', [CartController::class, 'removeItem']);
Route::put('/cart/items/{cartItem}', [CartController::class, 'updateItem']);
Route::post('/cart/from-recipes', [CartController::class, 'addFromRecipes']);
Route::post('/cart/checkout',      [OrderController::class, 'checkout']);

