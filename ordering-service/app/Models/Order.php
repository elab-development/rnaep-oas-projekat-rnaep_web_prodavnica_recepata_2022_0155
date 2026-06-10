<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $primaryKey = 'order_id';
    protected $fillable = ['user_id', 'status', 'total_price'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'order_items', 'order_id', 'ingredient_id')
                    ->withPivot('amount', 'total_price', 'user_id');
    }
    
    public function getRouteKeyName()
    {
        return $this->primaryKey;
    }
}
