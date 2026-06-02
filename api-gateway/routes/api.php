<?php

use App\Http\Controllers\GatewayController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('/csrf-token', [GatewayController::class, 'csrfToken']);

Route::post('/auth/register', fn (Request $request) => app(GatewayController::class)->proxy($request, 'users', 'register'));
Route::post('/auth/login', fn (Request $request) => app(GatewayController::class)->proxy($request, 'users', 'login'));

Route::get('/catalog/ingredients', fn (Request $request) => app(GatewayController::class)->proxy($request, 'catalog', 'ingredients'));
Route::get('/catalog/ingredients/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "ingredients/{$id}"));
Route::get('/catalog/recipes', fn (Request $request) => app(GatewayController::class)->proxy($request, 'catalog', 'recipes'));
Route::get('/catalog/recipes/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "recipes/{$id}"));
Route::get('/catalog/recipes/{id}/ingredients', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "recipes/{$id}/ingredients"));
Route::get('/catalog/public/recipes', fn (Request $request) => app(GatewayController::class)->proxy($request, 'catalog', 'public/recipes'));

Route::middleware([AuthMiddleware::class])->group(function () {
    Route::post('/auth/logout', fn (Request $request) => app(GatewayController::class)->proxy($request, 'users', 'logout'));

    Route::get('/users', fn (Request $request) => app(GatewayController::class)->proxy($request, 'users', 'users'));
    Route::get('/users/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'users', "users/{$id}"));
    Route::put('/users/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'users', "users/{$id}"));

    Route::post('/catalog/ingredients', fn (Request $request) => app(GatewayController::class)->proxy($request, 'catalog', 'ingredients'));
    Route::put('/catalog/ingredients/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "ingredients/{$id}"));
    Route::delete('/catalog/ingredients/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "ingredients/{$id}"));
    Route::put('/catalog/ingredients/{id}/stock', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "ingredients/{$id}/stock"));
    Route::post('/catalog/ingredients/{id}/restock', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "ingredients/{$id}/restock"));

    Route::post('/catalog/recipes', fn (Request $request) => app(GatewayController::class)->proxy($request, 'catalog', 'recipes'));
    Route::put('/catalog/recipes/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "recipes/{$id}"));
    Route::delete('/catalog/recipes/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'catalog', "recipes/{$id}"));

    Route::get('/orders', fn (Request $request) => app(GatewayController::class)->proxy($request, 'ordering', 'orders'));
    Route::post('/orders', fn (Request $request) => app(GatewayController::class)->proxy($request, 'ordering', 'orders'));
    Route::get('/orders/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'ordering', "orders/{$id}"));
    Route::put('/orders/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'ordering', "orders/{$id}"));

    Route::get('/cart', fn (Request $request) => app(GatewayController::class)->proxy($request, 'ordering', 'cart'));
    Route::post('/cart/items', fn (Request $request) => app(GatewayController::class)->proxy($request, 'ordering', 'cart/items'));
    Route::post('/cart/from-recipes', fn (Request $request) => app(GatewayController::class)->proxy($request, 'ordering', 'cart/from-recipes'));
    Route::put('/cart/items/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'ordering', "cart/items/{$id}"));
    Route::delete('/cart/items/{id}', fn (Request $request, string $id) => app(GatewayController::class)->proxy($request, 'ordering', "cart/items/{$id}"));
    Route::post('/cart/checkout', fn (Request $request) => app(GatewayController::class)->proxy($request, 'ordering', 'cart/checkout'));
});
