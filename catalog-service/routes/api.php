<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ExternalRecipeController;

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

Route::get('/ingredients',          [IngredientController::class, 'index']);
Route::get('/ingredients/{id}',     [IngredientController::class, 'show']);

Route::post('/ingredients',         [IngredientController::class, 'store']);
Route::put('/ingredients/{id}',     [IngredientController::class, 'update']);
Route::delete('/ingredients/{id}',  [IngredientController::class, 'destroy']);
Route::put('/ingredients/{id}/stock',    [IngredientController::class, 'updateStock']);
Route::post('/ingredients/{id}/restock', [IngredientController::class, 'restock']);

Route::get('/recipes',                      [RecipeController::class, 'index']);
Route::get('/recipes/{id}',                 [RecipeController::class, 'show']);
Route::get('/recipes/{id}/ingredients',     [RecipeController::class, 'ingredients']);

Route::post('/recipes',             [RecipeController::class, 'store']);
Route::put('/recipes/{id}',         [RecipeController::class, 'update']);
Route::delete('/recipes/{id}',      [RecipeController::class, 'destroy']);
Route::get('/public/recipes',       [ExternalRecipeController::class, 'search']);

Route::post('/internal/decrement-stock', [IngredientController::class, 'decrementStock']);

Route::get('/metrics', [MetricsController::class, 'index']);
