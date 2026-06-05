<?php

namespace App\Services;

use App\Repositories\IngredientRepository;
use App\Repositories\RecipeRepository;

class RecipeService
{
    public function __construct(
        private readonly RecipeRepository      $recipeRepository,
        private readonly IngredientRepository  $ingredientRepository,
    ) {}

    public function list(
        string $search,
        string $ingredientsAny,
        string $ingredientsAll,
        string $ingredientsExclude,
        int    $perPage,
        int    $page,
    ): array {
        $parseIds = fn($csv) => array_values(array_unique(
            array_filter(array_map('trim', explode(',', $csv)), fn($i) => $i !== '')
        ));

        $idsAny     = $parseIds($ingredientsAny);
        $idsAll     = $parseIds($ingredientsAll);
        $idsExclude = $parseIds($ingredientsExclude);

        $allRecipes = $this->recipeRepository->search($search);

        if (!empty($idsAny) || !empty($idsAll) || !empty($idsExclude)) {
            $allRecipes = $allRecipes->filter(function ($recipe) use ($idsAny, $idsAll, $idsExclude) {
                $recipeIngIds = $this->recipeRepository->ingredientIdsForRecipe((string) $recipe->_id);

                if (!empty($idsAny) && empty(array_intersect($idsAny, $recipeIngIds))) {
                    return false;
                }
                if (!empty($idsAll) && !empty(array_diff($idsAll, $recipeIngIds))) {
                    return false;
                }
                if (!empty($idsExclude) && !empty(array_intersect($idsExclude, $recipeIngIds))) {
                    return false;
                }

                return true;
            });
        }

        $total  = $allRecipes->count();
        $paged  = $allRecipes->slice(($page - 1) * $perPage, $perPage)->values();

        $recipes = $paged->map(function ($recipe) {
            return [
                'recipe_id'         => (string) $recipe->_id,
                'name'              => $recipe->name,
                'description'       => $recipe->description,
                'ingredients_count' => $this->recipeRepository->countItems((string) $recipe->_id),
                'created_at'        => $recipe->created_at,
                'updated_at'        => $recipe->updated_at,
            ];
        });

        return [
            'meta' => [
                'page'      => $page,
                'per_page'  => $perPage,
                'total'     => $total,
                'last_page' => (int) ceil($total / $perPage),
            ],
            'recipes' => $recipes,
        ];
    }

    public function getById(string $id): array
    {
        $recipe      = $this->recipeRepository->findOrFail($id);
        $recipeItems = $this->recipeRepository->itemsForRecipe((string) $recipe->_id);

        $ingredientIds = $recipeItems->pluck('ingredient_id')->toArray();
        $ingredients   = $this->ingredientRepository->findManyKeyedById($ingredientIds);

        $items = $recipeItems->map(function ($ri) use ($ingredients) {
            $ing = $ingredients[(string) $ri->ingredient_id] ?? null;
            return [
                'recipe_item_id' => (string) $ri->_id,
                'ingredient_id'  => (string) $ri->ingredient_id,
                'id'             => (string) $ri->ingredient_id,
                'name'           => $ing?->name,
                'price'          => $ing ? (float) $ing->price : null,
                'unit'           => $ing?->unit,
                'stock_quantity' => $ing ? (float) ($ing->stock_quantity ?? 0) : null,
                'quantity'       => (float) $ri->quantity,
            ];
        });

        return [
            'recipe_id'   => (string) $recipe->_id,
            'name'        => $recipe->name,
            'description' => $recipe->description,
            'items'       => $items,
            'created_at'  => $recipe->created_at,
            'updated_at'  => $recipe->updated_at,
        ];
    }

    public function getIngredients(string $id): array
    {
        $recipe      = $this->recipeRepository->findOrFail($id);
        $recipeItems = $this->recipeRepository->itemsForRecipe((string) $recipe->_id);

        $ingredientIds = $recipeItems->pluck('ingredient_id')->toArray();
        $ingredients   = $this->ingredientRepository->findManyKeyedById($ingredientIds);

        $items = $recipeItems->map(function ($ri) use ($ingredients) {
            $ing = $ingredients[(string) $ri->ingredient_id] ?? null;
            return [
                'ingredient_id'  => (string) $ri->ingredient_id,
                'id'             => (string) $ri->ingredient_id,
                'name'           => $ing?->name,
                'price'          => $ing ? (float) $ing->price : null,
                'unit'           => $ing?->unit,
                'stock_quantity' => $ing ? (float) $ing->stock_quantity : null,
                'quantity'       => (float) $ri->quantity,
            ];
        });

        return [
            'recipe_id'   => (string) $recipe->_id,
            'ingredients' => $items,
        ];
    }

    public function create(array $data, array $items): array
    {
        $data['name']        = htmlspecialchars(strip_tags($data['name']));
        $data['description'] = isset($data['description'])
            ? htmlspecialchars(strip_tags($data['description']))
            : null;

        if ($this->recipeRepository->existsByName($data['name'])) {
            abort(422, 'Recipe with this name already exists');
        }

        $recipe = $this->recipeRepository->create([
            'name'        => $data['name'],
            'description' => $data['description'],
        ]);

        $this->syncItems((string) $recipe->_id, $items);

        return [
            'recipe_id'   => (string) $recipe->_id,
            'name'        => $recipe->name,
            'description' => $recipe->description,
        ];
    }

    public function update(string $id, array $data, ?array $items): array
    {
        $recipe = $this->recipeRepository->findOrFail($id);

        if (isset($data['name'])) {
            $data['name'] = htmlspecialchars(strip_tags($data['name']));
        }
        if (isset($data['description'])) {
            $data['description'] = htmlspecialchars(strip_tags($data['description']));
        }

        $recipe->update([
            'name'        => $data['name'] ?? $recipe->name,
            'description' => array_key_exists('description', $data)
                ? $data['description']
                : $recipe->description,
        ]);

        if ($items !== null) {
            $this->recipeRepository->deleteItems((string) $recipe->_id);
            $this->syncItems((string) $recipe->_id, $items);
        }

        return [
            'recipe_id'   => (string) $recipe->_id,
            'name'        => $recipe->name,
            'description' => $recipe->description,
        ];
    }

    public function delete(string $id): void
    {
        $recipe = $this->recipeRepository->findOrFail($id);
        $this->recipeRepository->deleteItems((string) $recipe->_id);
        $recipe->delete();
    }

    private function syncItems(string $recipeId, array $items): void
    {
        foreach ($items as $row) {
            $this->recipeRepository->createItem([
                'recipe_id'     => $recipeId,
                'ingredient_id' => (string) $row['ingredient_id'],
                'quantity'      => (float) $row['quantity'],
            ]);
        }
    }
}