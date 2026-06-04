<?php
namespace App\Models;
 
use MongoDB\Laravel\Eloquent\Model;
 
class Ingredient extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'ingredients';
 
    protected $fillable = [
        'name', 'price', 'unit', 'category',
        'type', 'photo_path', 'description', 'stock_quantity',
    ];
 
    protected $casts = [
        'price'          => 'float',
        'stock_quantity' => 'float',
    ];
 
    public function isInStock(float $required = 1): bool
    {
        return ($this->stock_quantity ?? 0) >= $required;
    }
}