<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $primaryKey = 'cart_id';
    protected $fillable = ['user_id', 'total_amount_of_items', 'total_price'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'cart_items', 'cart_id', 'ingredient_id')
                    ->withPivot('amount');
    }
    
    public function getRouteKeyName()
    {
        return $this->primaryKey;
    }
}
