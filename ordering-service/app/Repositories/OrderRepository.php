<?php
namespace App\Repositories;
 
use App\Models\Order;
use App\Models\OrderItem;
 
class OrderRepository
{
    public function create(int $userId): Order
    {
        return Order::create([
            'user_id'     => $userId,
            'status'      => 'plaćeno',
            'total_price' => 0,
        ]);
    }
 
    public function addOrderItem(
        int $orderId,
        int $userId,
        string $ingredientId,
        int $amount,
        float $totalPrice
    ): OrderItem {
        return OrderItem::create([
            'order_id'      => $orderId,
            'user_id'       => $userId,
            'ingredient_id' => $ingredientId,
            'amount'        => $amount,
            'total_price'   => number_format($totalPrice, 2, '.', ''),
        ]);
    }
 
    public function updateTotal(Order $order, float $total): Order
    {
        $order->update(['total_price' => number_format($total, 2, '.', '')]);
        return $order;
    }
 
    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);
        return $order;
    }
 
    public function findByUserId(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with('orderItems')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }
 
    public function findAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with('orderItems')->latest()->get();
    }
 
    public function findById(int $orderId): ?Order
    {
        return Order::with('orderItems')->find($orderId);
    }
}