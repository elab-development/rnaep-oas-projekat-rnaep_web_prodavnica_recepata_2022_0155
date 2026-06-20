<?php

namespace App\Repositories;

use App\Models\Ingredient;
use Illuminate\Support\Collection;

class IngredientRepository
{
    public function all(array $ids = []): Collection
    {
        $query = Ingredient::query();

        if (!empty($ids)) {
            $query->whereIn('_id', $ids);
        }

        return $query->orderBy('name')->get();
    }

    public function find(string $id): ?Ingredient
    {
        return Ingredient::find($id);
    }

    public function findOrFail(string $id): Ingredient
    {
        return Ingredient::findOrFail($id);
    }

    public function existsByName(string $name): bool
    {
        return Ingredient::where('name', $name)->exists();
    }

    public function create(array $data): Ingredient
    {
        return Ingredient::create($data);
    }

    public function findManyKeyedById(array $ids): Collection
    {
        return Ingredient::whereIn('_id', $ids)
            ->get()
            ->keyBy(fn($i) => (string) $i->_id);
    }
}
