<?php
namespace App\Http\Controllers;
 
use App\Models\Ingredient;
use App\Models\Recipe;
 
class MetricsController extends Controller
{
    public function index()
    {
        $totalIngredients = Ingredient::count();
        $totalRecipes     = Recipe::count();
        $memUsage         = memory_get_usage(true);
 
        $m = "# HELP catalog_ingredients_total Total ingredients\n";
        $m .= "# TYPE catalog_ingredients_total gauge\n";
        $m .= "catalog_ingredients_total {$totalIngredients}\n\n";
 
        $m .= "# HELP catalog_recipes_total Total recipes\n";
        $m .= "# TYPE catalog_recipes_total gauge\n";
        $m .= "catalog_recipes_total {$totalRecipes}\n\n";
 
        $m .= "# HELP catalog_memory_bytes Memory usage\n";
        $m .= "# TYPE catalog_memory_bytes gauge\n";
        $m .= "catalog_memory_bytes {$memUsage}\n";
 
        return response($m, 200)->header('Content-Type', 'text/plain; version=0.0.4');
    }
}