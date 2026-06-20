<?php

namespace App\Http\Controllers;

use App\Services\IngredientService;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function __construct(
        private readonly IngredientService $ingredientService
    ) {}

    public function index(Request $request)
    {
        $ids = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $request->query('ids', ''))
        )));

        return response()->json([
            'ingredients' => $this->ingredientService->getAll($ids),
        ]);
    }

    public function show(string $id)
    {
        return response()->json([
            'ingredient' => $this->ingredientService->getById($id),
        ]);
    }

    public function store(Request $request)
    {
        $this->requireAdmin($request);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'unit'           => 'required|string|max:50',
            'category'       => 'nullable|string|max:255',
            'type'           => 'nullable|string|max:255',
            'description'    => 'nullable|string',
            'stock_quantity' => 'nullable|numeric|min:0',
            'photo_path'     => 'nullable|string|max:500',
        ]);

        $ingredient = $this->ingredientService->create($validated);

        return response()->json([
            'message'    => 'Ingredient created successfully',
            'ingredient' => $ingredient,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $this->requireAdmin($request);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'price'          => 'sometimes|numeric|min:0',
            'unit'           => 'sometimes|string|max:50',
            'category'       => 'sometimes|nullable|string|max:255',
            'type'           => 'sometimes|nullable|string|max:255',
            'description'    => 'sometimes|nullable|string',
            'stock_quantity' => 'sometimes|numeric|min:0',
            'photo_path'     => 'sometimes|nullable|string|max:500',
        ]);

        $ingredient = $this->ingredientService->update($id, $validated);

        return response()->json([
            'message'    => 'Ingredient updated',
            'ingredient' => $ingredient,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $this->requireAdmin($request);
        $this->ingredientService->delete($id);

        return response()->json(['message' => 'Ingredient deleted']);
    }

    public function updateStock(Request $request, string $id)
    {
        $this->requireAdmin($request);

        $validated = $request->validate([
            'stock_quantity' => 'required|numeric|min:0',
        ]);

        $ingredient = $this->ingredientService->update($id, [
            'stock_quantity' => (float) $validated['stock_quantity'],
        ]);

        return response()->json([
            'message'    => 'Stock updated',
            'ingredient' => $ingredient,
        ]);
    }

    public function restock(Request $request, string $id)
    {
        $this->requireAdmin($request);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $ingredient = $this->ingredientService->getById($id);
        $current = (float) ($ingredient['stock_quantity'] ?? 0);
        $ingredient = $this->ingredientService->update($id, [
            'stock_quantity' => $current + (float) $validated['amount'],
        ]);

        return response()->json([
            'message'    => 'Stock restocked',
            'ingredient' => $ingredient,
        ]);
    }

    private function requireAdmin(Request $request): void
    {
        if ($request->header('X-User-Role') !== 'admin') {
            abort(403, 'Only admins can perform this action');
        }
    }
    public function decrementStock(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.ingredient_id' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0.01',
        ]);

        $this->ingredientService->decrementStock($request->input('items', []));

        return response()->json(['message' => 'Stock updated']);
    }
}
