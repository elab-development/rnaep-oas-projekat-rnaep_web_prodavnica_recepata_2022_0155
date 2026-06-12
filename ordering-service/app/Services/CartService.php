<?php
namespace App\Services;

use App\Repositories\CartRepository;
use Illuminate\Support\Facades\DB;
 
class CartService
{
    public function __construct(
        private CartRepository $cartRepo,
        private CatalogService $catalogService,
        private PriceCalculator $calculator
    ) {}
 
    public function getCart(int $userId): array
    {
        $cart = $this->cartRepo->findOrCreateByUserId($userId);
        $cart->load('cartItems');
        return $this->formatCart($cart);
    }
 
    public function addItem(int $userId, string $ingredientId, int $amount): array
    {
        $ingredient = $this->catalogService->getIngredient($ingredientId);
        if (!$ingredient) {
            throw new \Exception('Ingredient not found or catalog service unavailable', 503);
        }
 
        return DB::transaction(function () use ($userId, $ingredientId, $amount) {
            $cart     = $this->cartRepo->findOrCreateByUserId($userId);
            $existing = $this->cartRepo->findCartItem($cart->cart_id, $ingredientId);
 
            if ($existing) {
                $this->cartRepo->updateCartItemAmount(
                    $existing,
                    $existing->amount + $amount
                );
            } else {
                $this->cartRepo->addCartItem($cart->cart_id, $ingredientId, $amount);
            }
 
            $this->recalculateTotals($cart);
            $cart->load('cartItems');
 
            return $this->formatCart($cart);
        });
    }
    public function addRecipes(int $userId, array $recipeIds): array
    {
        return DB::transaction(function () use ($userId, $recipeIds) {
            $cart = $this->cartRepo->findOrCreateByUserId($userId);

            foreach ($recipeIds as $recipeId) {
                $ingredients = $this->catalogService->getRecipeIngredients((string) $recipeId);

                foreach ($ingredients as $ingredient) {
                    $ingredientId = $ingredient['ingredient_id'] ?? $ingredient['id'] ?? null;

                    if (!$ingredientId) {
                        continue;
                    }

                    $amount = (int) ceil((float) ($ingredient['quantity'] ?? 1));

                    if ($amount < 1) {
                        $amount = 1;
                    }

                    $existing = $this->cartRepo->findCartItem($cart->cart_id, (string) $ingredientId);

                    if ($existing) {
                        $this->cartRepo->updateCartItemAmount(
                            $existing,
                            (int) $existing->amount + $amount
                        );
                    } else {
                        $this->cartRepo->addCartItem(
                            $cart->cart_id,
                            (string) $ingredientId,
                            $amount
            );
            }}}

            $this->recalculateTotals($cart);
            $cart->load('cartItems');

            return $this->formatCart($cart);
        });
    }
    public function removeItem(int $userId, \App\Models\CartItem $cartItem): array
    {
        $cart = $this->cartRepo->findOrCreateByUserId($userId);
        if ($cart->cart_id !== $cartItem->cart_id) {
            throw new \Exception('Forbidden', 403);
        }
 
        return DB::transaction(function () use ($cart, $cartItem) {
            $this->cartRepo->deleteCartItem($cartItem);
            $this->recalculateTotals($cart);
            $cart->load('cartItems');
            return $this->formatCart($cart);
        });
    }
 
    public function recalculateTotals(\App\Models\Cart $cart): void
    {
        $items = $this->cartRepo->getCartItems($cart->cart_id);
 
        if ($items->isEmpty()) {
            $this->cartRepo->updateCartTotals($cart, 0, 0.0);
            return;
        }
 
        $ids         = $items->pluck('ingredient_id')->toArray();
        $ingredients = $this->catalogService->getIngredientsByIds($ids);
        $ingMap      = collect($ingredients)->keyBy('id');
 
        $itemsForCalc = $items->map(fn($it) => [
            'price'  => (float)($ingMap[$it->ingredient_id]['price'] ?? 0),
            'amount' => (int)$it->amount,
        ])->toArray();
 
        $totalPrice  = $this->calculator->calculateTotal($itemsForCalc);
        $totalAmount = $items->sum('amount');
 
        $this->cartRepo->updateCartTotals($cart, $totalAmount, $totalPrice);
    }
 
    private function formatCart(\App\Models\Cart $cart): array
    {
        $ids = $cart->cartItems
            ->pluck('ingredient_id')
            ->unique()
            ->toArray();

        $ingredients = $this->catalogService->getIngredientsByIds($ids);

        $ingredientMap = collect($ingredients)
            ->keyBy('ingredient_id');

        return [
            'cart_id'               => $cart->cart_id,
            'user_id'               => $cart->user_id,
            'total_amount_of_items' => (int)$cart->total_amount_of_items,
            'total_price'           => (float)$cart->total_price,
            'items' => $cart->cartItems->map(function ($it) use ($ingredientMap) {
                return [
                    'cart_item_id'  => $it->cart_item_id,
                    'ingredient_id' => $it->ingredient_id,
                    'amount'        => (int)$it->amount,

                    'ingredient'    => $ingredientMap[$it->ingredient_id] ?? null,
                ];
            })->values(),
        ];
    }
    public function updateItem(int $userId, \App\Models\CartItem $cartItem, int $amount): array
    {
        $cart = $this->cartRepo->findOrCreateByUserId($userId);
        if ($cart->cart_id !== $cartItem->cart_id) {
            throw new \Exception('Forbidden', 403);
        }

        return DB::transaction(function () use ($cart, $cartItem, $amount) {
            $this->cartRepo->updateCartItemAmount($cartItem, $amount);
            $this->recalculateTotals($cart);
            $cart->load('cartItems');
            return $this->formatCart($cart);
        });
    }
}