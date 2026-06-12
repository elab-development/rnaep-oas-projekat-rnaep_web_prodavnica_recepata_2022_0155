<?php

namespace App\Repositories;

use App\Models\Recipe;
use App\Models\RecipeItem;
use Illuminate\Support\Collection;

class RecipeRepository
{
    public function findOrFail(string $id): Recipe
    {
        return Recipe::findOrFail($id);
    }

    public function existsByName(string $name): bool
    {
        return Recipe::where('name', $name)->exists();
    }

    public function create(array $data): Recipe
    {
        return Recipe::create($data);
    }

    public function search(string $search): Collection
    {
        $query = Recipe::query();

        if ($search !== '') {
            $escaped = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                  ->orWhere('description', 'like', "%{$escaped}%");
            });
        }

        return $query->get();
    }

    public function itemsForRecipe(string $recipeId): Collection
    {
        return RecipeItem::where('recipe_id', $recipeId)->get();
    }

    public function ingredientIdsForRecipe(string $recipeId): array
    {
        return RecipeItem::where('recipe_id', $recipeId)
            ->pluck('ingredient_id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function countItems(string $recipeId): int
    {
        return RecipeItem::where('recipe_id', $recipeId)->count();
    }

    public function createItem(array $data): RecipeItem
    {
        return RecipeItem::create($data);
    }

    public function deleteItems(string $recipeId): void
    {
        RecipeItem::where('recipe_id', $recipeId)->delete();
    }
}