<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeItem;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();


    $database = config('database.connections.mongodb.database');

    if ($database !== 'catalog_test') {

        throw new \RuntimeException(

            'Testovi se ne smeju pokretati nad ovom bazom: ' . $database

        );

    }
        Ingredient::query()->delete();
        Recipe::query()->delete();
        RecipeItem::query()->delete();
    }

    public function test_can_list_ingredients(): void
    {
        Ingredient::create([
            'name' => 'Krompir',
            'price' => 120,
            'unit' => 'kg',
            'category' => 'Povrće',
            'type' => 'sastojak',
            'description' => 'Svež krompir',
            'stock_quantity' => 50,
        ]);

        Ingredient::create([
            'name' => 'Piletina',
            'price' => 650,
            'unit' => 'kg',
            'category' => 'Meso',
            'type' => 'sastojak',
            'description' => 'Pileće belo meso',
            'stock_quantity' => 20,
        ]);

        $response = $this->getJson('/api/ingredients');

        $response->assertOk()
            ->assertJsonStructure([
                'ingredients',
            ])
            ->assertJsonFragment([
                'name' => 'Krompir',
            ])
            ->assertJsonFragment([
                'name' => 'Piletina',
            ]);
    }

    public function test_can_show_single_ingredient_with_stock_quantity(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Brašno',
            'price' => 90,
            'unit' => 'kg',
            'category' => 'Osnovne namirnice',
            'type' => 'sastojak',
            'description' => 'Pšenično brašno',
            'stock_quantity' => 100,
        ]);

        $response = $this->getJson('/api/ingredients/' . (string) $ingredient->_id);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Brašno',
                'stock_quantity' => 100,
            ]);
    }

    public function test_admin_can_create_ingredient(): void
    {
        $response = $this->withHeaders([
            'X-User-Role' => 'admin',
        ])->postJson('/api/ingredients', [
            'name' => 'Jaja',
            'price' => 25,
            'unit' => 'kom',
            'category' => 'Mlečni proizvodi',
            'type' => 'sastojak',
            'description' => 'Sveža jaja',
            'stock_quantity' => 80,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Ingredient created successfully',
                'name' => 'Jaja',
            ]);

        $this->assertSame(1, Ingredient::where('name', 'Jaja')->count());
    }

    public function test_non_admin_cannot_create_ingredient(): void
    {
        $response = $this->withHeaders([
            'X-User-Role' => 'user',
        ])->postJson('/api/ingredients', [
            'name' => 'Mleko',
            'price' => 150,
            'unit' => 'l',
            'stock_quantity' => 30,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_stock_quantity(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Pirinač',
            'price' => 180,
            'unit' => 'kg',
            'stock_quantity' => 40,
        ]);

        $response = $this->withHeaders([
            'X-User-Role' => 'admin',
        ])->putJson('/api/ingredients/' . (string) $ingredient->_id . '/stock', [
            'stock_quantity' => 75,
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Stock updated',
                'stock_quantity' => 75,
            ]);
    }

    public function test_admin_can_restock_ingredient(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Šećer',
            'price' => 110,
            'unit' => 'kg',
            'stock_quantity' => 10,
        ]);

        $response = $this->withHeaders([
            'X-User-Role' => 'admin',
        ])->postJson('/api/ingredients/' . (string) $ingredient->_id . '/restock', [
            'amount' => 15,
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Stock restocked',
                'stock_quantity' => 25,
            ]);
    }

    public function test_can_list_recipes(): void
    {
        Recipe::create([
            'name' => 'Palačinke',
            'description' => 'Osnovni recept za palačinke.',
        ]);

        $response = $this->getJson('/api/recipes');

        $response->assertOk()
            ->assertJsonStructure([
                'meta',
                'recipes',
            ])
            ->assertJsonFragment([
                'name' => 'Palačinke',
            ]);
    }

    public function test_can_show_recipe_with_items_and_ingredient_quantities(): void
    {
        $flour = Ingredient::create([
            'name' => 'Brašno',
            'price' => 90,
            'unit' => 'kg',
            'stock_quantity' => 100,
        ]);

        $milk = Ingredient::create([
            'name' => 'Mleko',
            'price' => 150,
            'unit' => 'l',
            'stock_quantity' => 30,
        ]);

        $recipe = Recipe::create([
            'name' => 'Palačinke',
            'description' => 'Osnovni recept za palačinke.',
        ]);

        RecipeItem::create([
            'recipe_id' => (string) $recipe->_id,
            'ingredient_id' => (string) $flour->_id,
            'quantity' => 1,
        ]);

        RecipeItem::create([
            'recipe_id' => (string) $recipe->_id,
            'ingredient_id' => (string) $milk->_id,
            'quantity' => 2,
        ]);

        $response = $this->getJson('/api/recipes/' . (string) $recipe->_id);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Palačinke',
            ])
            ->assertJsonFragment([
                'name' => 'Brašno',
                'quantity' => 1,
                'stock_quantity' => 100,
            ])
            ->assertJsonFragment([
                'name' => 'Mleko',
                'quantity' => 2,
                'stock_quantity' => 30,
            ]);
    }

    public function test_can_get_recipe_ingredients_endpoint(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Krompir',
            'price' => 120,
            'unit' => 'kg',
            'stock_quantity' => 50,
        ]);

        $recipe = Recipe::create([
            'name' => 'Musaka',
            'description' => 'Musaka sa krompirom.',
        ]);

        RecipeItem::create([
            'recipe_id' => (string) $recipe->_id,
            'ingredient_id' => (string) $ingredient->_id,
            'quantity' => 3,
        ]);

        $response = $this->getJson('/api/recipes/' . (string) $recipe->_id . '/ingredients');

        $response->assertOk()
            ->assertJsonStructure([
                'recipe_id',
                'ingredients',
            ])
            ->assertJsonFragment([
                'name' => 'Krompir',
                'quantity' => 3,
                'stock_quantity' => 50,
            ]);
    }
}