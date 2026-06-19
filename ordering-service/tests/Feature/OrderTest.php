<?php

namespace Tests\Feature;

use App\Kafka\KafkaProducer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_direct_order(): void
    {
        $this->mock(CatalogService::class, function ($mock) {
            $mock->shouldReceive('getIngredientsByIds')
                ->once()
                ->andReturn([
                    [
                        'id' => 'ingredient-1',
                        'name' => 'Krompir',
                        'price' => 120,
                        'unit' => 'kg',
                        'stock_quantity' => 50,
                    ],
                ]);
        });

        $this->mock(KafkaProducer::class, function ($mock) {
            $mock->shouldReceive('publish')
                ->once()
                ->withArgs(function ($topic, $payload) {
                    return $topic === 'order-created'
                        && isset($payload['order_id'])
                        && isset($payload['items'])
                        && $payload['total'] === 240.0;
                });
        });

        $response = $this->withHeaders([
            'X-User-Id' => '1',
            'X-User-Role' => 'user',
        ])->postJson('/api/orders', [
            'items' => [
                [
                    'ingredient_id' => 'ingredient-1',
                    'amount' => 2,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'Order created',
                'total_price' => 240,
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => 1,
            'status' => 'plaćeno',
        ]);

        $this->assertDatabaseHas('order_items', [
            'ingredient_id' => 'ingredient-1',
            'amount' => 2,
        ]);
    }

    public function test_order_cannot_be_created_without_items(): void
    {
        $response = $this->withHeaders([
            'X-User-Id' => '1',
            'X-User-Role' => 'user',
        ])->postJson('/api/orders', [
            'items' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_cannot_create_order_as_user(): void
    {
        $response = $this->withHeaders([
            'X-User-Id' => '1',
            'X-User-Role' => 'admin',
        ])->postJson('/api/orders', [
            'items' => [
                [
                    'ingredient_id' => 'ingredient-1',
                    'amount' => 1,
                ],
            ],
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'error' => 'Only users can create orders',
            ]);
    }

    public function test_user_can_see_only_own_orders(): void
    {
        Order::create([
            'user_id' => 1,
            'status' => 'plaćeno',
            'total_price' => 500,
        ]);

        Order::create([
            'user_id' => 2,
            'status' => 'plaćeno',
            'total_price' => 1000,
        ]);

        $response = $this->withHeaders([
            'X-User-Id' => '1',
            'X-User-Role' => 'user',
        ])->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonFragment([
                'total_price' => 500,
            ])
            ->assertJsonMissing([
                'total_price' => 1000,
            ]);
    }

    public function test_user_cannot_access_someone_elses_order(): void
    {
        $order = Order::create([
            'user_id' => 2,
            'status' => 'plaćeno',
            'total_price' => 1000,
        ]);

        $response = $this->withHeaders([
            'X-User-Id' => '1',
            'X-User-Role' => 'user',
        ])->getJson('/api/orders/' . $order->order_id);

        $response->assertStatus(403);
    }

    public function test_admin_can_access_all_orders(): void
    {
        Order::create([
            'user_id' => 1,
            'status' => 'plaćeno',
            'total_price' => 500,
        ]);

        Order::create([
            'user_id' => 2,
            'status' => 'plaćeno',
            'total_price' => 1000,
        ]);

        $response = $this->withHeaders([
            'X-User-Id' => '99',
            'X-User-Role' => 'admin',
        ])->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonFragment([
                'total_price' => 500,
            ])
            ->assertJsonFragment([
                'total_price' => 1000,
            ]);
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::create([
            'user_id' => 1,
            'status' => 'plaćeno',
            'total_price' => 500,
        ]);

        $response = $this->withHeaders([
            'X-User-Id' => '99',
            'X-User-Role' => 'admin',
        ])->putJson('/api/orders/' . $order->order_id, [
            'status' => 'isporučeno',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => 'Order updated',
                'status' => 'isporučeno',
            ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => $order->order_id,
            'status' => 'isporučeno',
        ]);
    }

    public function test_user_cannot_update_order_status(): void
    {
        $order = Order::create([
            'user_id' => 1,
            'status' => 'plaćeno',
            'total_price' => 500,
        ]);

        $response = $this->withHeaders([
            'X-User-Id' => '1',
            'X-User-Role' => 'user',
        ])->putJson('/api/orders/' . $order->order_id, [
            'status' => 'isporučeno',
        ]);

        $response->assertStatus(403);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}