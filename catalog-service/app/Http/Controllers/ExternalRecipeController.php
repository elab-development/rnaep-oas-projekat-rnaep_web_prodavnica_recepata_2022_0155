<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExternalRecipeController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q'      => ['required', 'string', 'max:100'],
            'source' => ['sometimes', 'in:mealdb,spoonacular,both'],
            'limit'  => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $query  = trim($validated['q']);
        $source = $validated['source'] ?? 'both';
        $limit  = (int) ($validated['limit'] ?? 10);

        $results = [];
        $meta = [
            'query'  => $query,
            'source' => $source,
        ];

        if ($source === 'mealdb' || $source === 'both') {
            $mealDb = $this->fetchFromMealDb($query, $limit);
            $results = array_merge($results, $mealDb['items']);
            $meta['mealdb_count'] = $mealDb['count'];
        }

        if ($source === 'spoonacular' || $source === 'both') {
            $spoonacular = $this->fetchFromSpoonacular($query, $limit);
            $results = array_merge($results, $spoonacular['items']);
            $meta['spoonacular_count'] = $spoonacular['count'];

            if (!empty($spoonacular['note'])) {
                $meta['spoonacular_note'] = $spoonacular['note'];
            }
        }

        if (empty($results)) {
            return response()->json([
                'message' => 'No recipes found from selected sources.',
                'meta'    => $meta,
                'recipes' => [],
            ], 404);
        }

        return response()->json([
            'meta'    => $meta,
            'recipes' => $results,
        ]);
    }

    private function fetchFromMealDb(string $query, int $limit): array
    {
        return Cache::remember("external:mealdb:{$query}:{$limit}", 600, function () use ($query, $limit) {
            try {
                $response = Http::timeout(10)->get('https://www.themealdb.com/api/json/v1/1/search.php', [
                    's' => $query,
                ]);

                if (!$response->ok()) {
                    return ['items' => [], 'count' => 0];
                }

                $meals = $response->json('meals') ?? [];
                $items = [];

                foreach (array_slice($meals, 0, $limit) as $meal) {
                    $ingredients = [];

                    for ($i = 1; $i <= 20; $i++) {
                        $ingredient = trim((string) ($meal["strIngredient{$i}"] ?? ''));
                        $measure = trim((string) ($meal["strMeasure{$i}"] ?? ''));

                        if ($ingredient !== '') {
                            $ingredients[] = trim("{$measure} {$ingredient}");
                        }
                    }

                    $items[] = [
                        'id'           => (string) ($meal['idMeal'] ?? ''),
                        'title'        => $meal['strMeal'] ?? null,
                        'image'        => $meal['strMealThumb'] ?? null,
                        'source'       => 'mealdb',
                        'source_url'   => $meal['strSource'] ?: ($meal['strYoutube'] ?? null),
                        'category'     => $meal['strCategory'] ?? null,
                        'area'         => $meal['strArea'] ?? null,
                        'instructions' => $meal['strInstructions'] ?? null,
                        'ingredients'  => $ingredients,
                    ];
                }

                return ['items' => $items, 'count' => count($items)];
            } catch (\Throwable) {
                return ['items' => [], 'count' => 0];
            }
        });
    }

    private function fetchFromSpoonacular(string $query, int $limit): array
    {
        $apiKey = config('services.spoonacular.key');

        if (!$apiKey) {
            return [
                'items' => [],
                'count' => 0,
                'note'  => 'Spoonacular API key not configured',
            ];
        }

        return Cache::remember("external:spoonacular:{$query}:{$limit}", 600, function () use ($query, $limit, $apiKey) {
            try {
                $response = Http::timeout(12)->get('https://api.spoonacular.com/recipes/complexSearch', [
                    'apiKey'               => $apiKey,
                    'query'                => $query,
                    'number'               => $limit,
                    'addRecipeInformation' => 'true',
                    'fillIngredients'      => 'true',
                ]);

                if (!$response->ok()) {
                    return ['items' => [], 'count' => 0];
                }

                $recipes = $response->json('results') ?? [];
                $items = [];

                foreach ($recipes as $recipe) {
                    $ingredients = [];

                    foreach ($recipe['extendedIngredients'] ?? [] as $ingredient) {
                        $name = $ingredient['name'] ?? null;
                        $amount = $ingredient['measures']['metric']['amount'] ?? null;
                        $unit = $ingredient['measures']['metric']['unitLong'] ?? null;

                        if ($name) {
                            $ingredients[] = ($amount && $unit)
                                ? trim("{$amount} {$unit} {$name}")
                                : $name;
                        }
                    }

                    $items[] = [
                        'id'             => (string) ($recipe['id'] ?? ''),
                        'title'          => $recipe['title'] ?? null,
                        'image'          => $recipe['image'] ?? null,
                        'source'         => 'spoonacular',
                        'source_url'     => $recipe['sourceUrl'] ?? null,
                        'category'       => null,
                        'area'           => null,
                        'instructions'   => $recipe['instructions'] ?? null,
                        'readyInMinutes' => $recipe['readyInMinutes'] ?? null,
                        'servings'       => $recipe['servings'] ?? null,
                        'summary_html'   => $recipe['summary'] ?? null,
                        'ingredients'    => $ingredients,
                    ];
                }

                return ['items' => $items, 'count' => count($items)];
            } catch (\Throwable) {
                return ['items' => [], 'count' => 0];
            }
        });
    }
}
