<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Repositories\IngredientRepository;
use Illuminate\Support\Collection;

class IngredientService
{
    public function __construct(
        private readonly IngredientRepository $ingredientRepository
    ) {}

    public function getAll(array $ids = []): Collection
    {
        return $this->ingredientRepository
            ->all($ids)
            ->map(fn (Ingredient $ingredient) => $this->format($ingredient));
    }

    public function getById(string $id): array
    {
        return $this->format($this->ingredientRepository->findOrFail($id));
    }

    public function create(array $data): array
    {
        $data = $this->sanitize($data);
        $data['stock_quantity'] = isset($data['stock_quantity'])
            ? (float) $data['stock_quantity']
            : 100.0;

        if ($this->ingredientRepository->existsByName($data['name'])) {
            abort(422, 'Ingredient with this name already exists');
        }

        return $this->format($this->ingredientRepository->create($data));
    }

    public function update(string $id, array $data): array
    {
        $ingredient = $this->ingredientRepository->findOrFail($id);
        $ingredient->update($this->sanitize($data));

        return $this->format($ingredient->fresh());
    }

    public function delete(string $id): void
    {
        $ingredient = $this->ingredientRepository->findOrFail($id);
        $ingredient->delete();
    }

    private function sanitize(array $data): array
    {
        foreach (['name', 'category', 'type', 'description', 'photo_path'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = htmlspecialchars(strip_tags((string) $data[$field]));
            }
        }

        if (isset($data['price'])) {
            $data['price'] = (float) $data['price'];
        }

        if (isset($data['stock_quantity'])) {
            $data['stock_quantity'] = (float) $data['stock_quantity'];
        }

        return $data;
    }

    private function format(Ingredient $ingredient): array
    {
        $id = (string) $ingredient->_id;
        $photoPath = $ingredient->photo_path ?? null;

        return [
            'id'             => $id,
            'ingredient_id'  => $id,
            '_id'            => $id,
            'name'           => $ingredient->name,
            'price'          => (float) ($ingredient->price ?? 0),
            'unit'           => $ingredient->unit,
            'category'       => $ingredient->category,
            'type'           => $ingredient->type,
            'description'    => $ingredient->description,
            'photo_path'     => $photoPath,
            'photo_url'      => $photoPath,
            'stock_quantity' => (float) ($ingredient->stock_quantity ?? 0),
            'created_at'     => $ingredient->created_at,
            'updated_at'     => $ingredient->updated_at,
        ];
    }
    public function decrementStock(array $items): void
    {
        foreach ($items as $item) {
            $ingredient = $this->ingredientRepository->find($item['ingredient_id']);
            if (!$ingredient) {
                continue;
            }

            $newStock = max(0, (float) ($ingredient->stock_quantity ?? 0) - (float) $item['amount']);
            $ingredient->update(['stock_quantity' => $newStock]);
        }
    }
}
