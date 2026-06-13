<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayTest extends TestCase
{
    public function test_csrf_token_endpoint_returns_token(): void
    {
        $response = $this->getJson('/api/csrf-token');

        $response->assertOk()
            ->assertJsonStructure([
                'csrf_token',
            ]);
    }

    public function test_gateway_proxies_register_request_to_user_service(): void
    {
        config([
            'app.url' => 'http://localhost',
        ]);

        putenv('USER_SERVICE_URL=http://user-service/api');

        Http::fake([
            'http://user-service/api/register' => Http::response([
                'message' => 'User registered successfully',
                'user' => [
                    'user_id' => 1,
                    'email' => 'vanja@test.com',
                    'role' => 'user',
                ],
                'token' => 'test-token',
            ], 201),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Vanja',
            'email' => 'vanja@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'User registered successfully',
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://user-service/api/register'
                && $request->method() === 'POST';
        });
    }

    public function test_gateway_proxies_catalog_ingredients_request(): void
    {
        putenv('CATALOG_SERVICE_URL=http://catalog-service/api');

        Http::fake([
            'http://catalog-service/api/ingredients' => Http::response([
                'ingredients' => [
                    [
                        'id' => 'ingredient-1',
                        'name' => 'Krompir',
                        'price' => 120,
                        'unit' => 'kg',
                        'stock_quantity' => 50,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/catalog/ingredients');

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Krompir',
                'stock_quantity' => 50,
            ]);

        Http::assertSent(function ($request) {
            return $request->url() === 'http://catalog-service/api/ingredients'
                && $request->method() === 'GET';
        });
    }

    public function test_gateway_proxies_external_recipes_request(): void
    {
        putenv('CATALOG_SERVICE_URL=http://catalog-service/api');

        Http::fake([
            'http://catalog-service/api/public/recipes*' => Http::response([
                'source' => 'both',
                'recipes' => [
                    [
                        'title' => 'Chicken Soup',
                        'source' => 'mealdb',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/catalog/public/recipes?q=chicken&source=both&limit=5');

        $response->assertOk()
            ->assertJsonFragment([
                'title' => 'Chicken Soup',
            ]);

        Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'http://catalog-service/api/public/recipes')
                && $request->method() === 'GET';
        });
    }

    public function test_gateway_returns_503_when_service_is_unavailable(): void
    {
        putenv('CATALOG_SERVICE_URL=http://catalog-service/api');

        Http::fake([
            'http://catalog-service/api/ingredients' => function () {
                throw new \Exception('Connection refused');
            },
        ]);

        $response = $this->getJson('/api/catalog/ingredients');

        $response->assertStatus(503)
            ->assertJsonFragment([
                'error' => 'Servis je nedostupan',
            ]);
    }
}