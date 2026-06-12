<?php
namespace App\Models;
 
use MongoDB\Laravel\Eloquent\Model;
 
class RecipeItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'recipe_items';
 
    protected $fillable = ['recipe_id', 'ingredient_id', 'quantity'];
 
    protected $casts = [
        'quantity' => 'float',
    ];
}