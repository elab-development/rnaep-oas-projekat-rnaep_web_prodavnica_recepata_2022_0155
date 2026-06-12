<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\RecipeService;

class RecipeController extends Controller
{
    public function __construct(
        private readonly RecipeService $recipeService
    ) {}
 
    public function index(Request $request)
    {
        $request->validate([
            'search'              => ['sometimes', 'string', 'max:200'],
            'ingredients_any'     => ['sometimes', 'string'],
            'ingredients_all'     => ['sometimes', 'string'],
            'ingredients_exclude' => ['sometimes', 'string'],
            'per_page'            => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page'                => ['sometimes', 'integer', 'min:1'],
        ]);
 
        $result = $this->recipeService->list(
            search:         trim((string) $request->input('search', '')),
            ingredientsAny: $request->input('ingredients_any', ''),
            ingredientsAll: $request->input('ingredients_all', ''),
            ingredientsExclude: $request->input('ingredients_exclude', ''),
            perPage:        (int) $request->input('per_page', 15),
            page:           (int) $request->input('page', 1),
        );
 
        return response()->json($result);
    }
 
    public function show($id)
    {
        return response()->json([
            'recipe' => $this->recipeService->getById($id),
        ]);
    }
 
    public function ingredients($id)
    {
        return response()->json(
            $this->recipeService->getIngredients($id)
        );
    }
 
    public function store(Request $request)
    {
        $this->requireAdmin($request);
 
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'description'              => 'nullable|string',
            'items'                    => 'sometimes|array|min:1',
            'items.*.ingredient_id'    => 'required_with:items|string',
            'items.*.quantity'         => 'required_with:items|numeric|min:0.01',
        ]);
 
        $recipe = $this->recipeService->create($validated, $request->input('items', []));
 
        return response()->json([
            'message' => 'Recipe created successfully',
            'recipe'  => $recipe,
        ], 201);
    }
 
    public function update(Request $request, $id)
    {
        $this->requireAdmin($request);
 
        $validated = $request->validate([
            'name'                     => 'sometimes|string|max:255',
            'description'              => 'sometimes|nullable|string',
            'items'                    => 'sometimes|array|min:1',
            'items.*.ingredient_id'    => 'required_with:items|string',
            'items.*.quantity'         => 'required_with:items|numeric|min:0.01',
        ]);
 
        $recipe = $this->recipeService->update(
            $id,
            $validated,
            $request->has('items') ? $request->input('items') : null
        );
 
        return response()->json([
            'message' => 'Recipe updated successfully',
            'recipe'  => $recipe,
        ]);
    }
 
    public function destroy(Request $request, $id)
    {
        $this->requireAdmin($request);
        $this->recipeService->delete($id);
 
        return response()->json(['message' => 'Recipe deleted successfully']);
    }
 
    private function requireAdmin(Request $request): void
    {
        if ($request->header('X-User-Role') !== 'admin') {
            abort(403, 'Only admins can perform this action');
        }
    }
}