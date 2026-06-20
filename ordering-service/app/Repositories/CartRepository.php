<?php
namespace App\Repositories;
 
use App\Models\Cart;
use App\Models\CartItem;
 
class CartRepository
{
    public function findOrCreateByUserId(int $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId],
            ['total_amount_of_items' => 0, 'total_price' => 0]
        );
    }
 
    public function findWithItemsByUserId(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)
            ->with('cartItems')
            ->first();
    }
 
    public function findCartItem(int $cartId, string $ingredientId): ?CartItem
    {
        return CartItem::where('cart_id', $cartId)
            ->where('ingredient_id', $ingredientId)
            ->first();
    }
 
    public function addCartItem(int $cartId, string $ingredientId, int $amount): CartItem
    {
        return CartItem::create([
            'cart_id'       => $cartId,
            'ingredient_id' => $ingredientId,
            'amount'        => $amount,
        ]);
    }
 
    public function updateCartItemAmount(CartItem $cartItem, int $amount): CartItem
    {
        $cartItem->update(['amount' => $amount]);
        return $cartItem;
    }
 
    public function deleteCartItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }
 
    public function clearCart(int $cartId): void
    {
        CartItem::where('cart_id', $cartId)->delete();
    }
 
    public function updateCartTotals(Cart $cart, int $totalItems, float $totalPrice): void
    {
        $cart->update([
            'total_amount_of_items' => $totalItems,
            'total_price'           => number_format($totalPrice, 2, '.', ''),
        ]);
    }
 
    public function getCartItems(int $cartId)
    {
        return CartItem::where('cart_id', $cartId)->get();
    }
}