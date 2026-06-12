<?php
namespace App\Http\Controllers;
 
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}
 
    public function show(Request $request)
    {
        $userId = (int)$request->header('X-User-Id');
 
        $cart = $this->cartService->getCart($userId);
        return response()->json($cart);
    }
 
    public function addItem(Request $request)
    {
        $userId = (int)$request->header('X-User-Id');
 
        $validated = $request->validate([
            'ingredient_id' => 'required|string',
            'amount'        => 'required|integer|min:1',
        ]);
 
        try {
            $cart = $this->cartService->addItem(
                $userId,
                $validated['ingredient_id'],
                (int)$validated['amount']
            );
            return response()->json(['message' => 'Item added', 'cart' => $cart], 201);
 
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

        public function addFromRecipes(Request $request)
    {
        $userId = (int) $request->header('X-User-Id');

        if (!$userId) {
            return response()->json([
                'error' => 'Korisnik nije autentifikovan.'
            ], 401);
        }

        $validated = $request->validate([
            'recipe_ids' => 'required|array|min:1',
            'recipe_ids.*' => 'required|string',
        ]);

        try {
            $cart = $this->cartService->addRecipes(
                $userId,
                $validated['recipe_ids']
            );

            return response()->json([
                'message' => 'Sastojci iz recepta su dodati u korpu.',
                'cart' => $cart,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
 
    public function removeItem(Request $request, CartItem $cartItem)
    {
        $userId = (int)$request->header('X-User-Id');
 
        try {
            $cart = $this->cartService->removeItem($userId, $cartItem);
            return response()->json(['message' => 'Item removed', 'cart' => $cart]);
 
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    public function updateItem(Request $request, CartItem $cartItem)
    {
        $userId = (int)$request->header('X-User-Id');

        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        try {
            $cart = $this->cartService->updateItem($userId, $cartItem, (int)$validated['amount']);
            return response()->json(['message' => 'Item updated', 'cart' => $cart]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}