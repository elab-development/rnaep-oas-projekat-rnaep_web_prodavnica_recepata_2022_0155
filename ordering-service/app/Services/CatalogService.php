<?php
namespace App\Services;
 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\CircuitBreaker;

class CatalogService
{
    private CircuitBreaker $cb;
    private string $baseUrl;
 
    public function __construct()
    {
        $this->baseUrl = env('CATALOG_SERVICE_URL', 'http://catalog-service/api');
        $this->cb      = new CircuitBreaker('catalog-service');
    }
 
    public function getIngredientsByIds(array $ids): array
    {
        if (!$this->cb->isAvailable()) {
            Log::warning('[CatalogService] Circuit OPEN - skipping request');
            return [];
        }
 
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/ingredients", [
                    'ids' => implode(',', $ids)
                ]);
 
            if ($response->successful()) {
                $this->cb->recordSuccess();
                return $response->json('ingredients', []);
            }
 
            $this->cb->recordFailure();
            return [];
 
        } catch (\Throwable $e) {
            $this->cb->recordFailure();
            Log::error("[CatalogService] Request failed: {$e->getMessage()}");
            return [];
        }
    }
 
    public function getIngredient(string $id): ?array
    {
        if (!$this->cb->isAvailable()) {
            return null;
        }
 
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/ingredients/{$id}");
 
            if ($response->successful()) {
                $this->cb->recordSuccess();
                return $response->json('ingredient');
            }
 
            $this->cb->recordFailure();
            return null;
 
        } catch (\Throwable $e) {
            $this->cb->recordFailure();
            return null;
        }
    }
    public function getRecipeIngredients(string $recipeId): array
    {
        if (!$this->cb->isAvailable()) {
            Log::warning('[CatalogService] Circuit OPEN - skipping recipe ingredients request');
            return [];
        }

        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/recipes/{$recipeId}/ingredients");

            if ($response->successful()) {
                $this->cb->recordSuccess();
                return $response->json('ingredients', []);
            }

            $this->cb->recordFailure();
            return [];

        } catch (\Throwable $e) {
            $this->cb->recordFailure();
            Log::error("[CatalogService] Recipe ingredients request failed: {$e->getMessage()}");
            return [];
        }
    }
    public function getCircuitState(): string
    {
        return $this->cb->getState();
    }
}