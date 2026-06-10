<?php
namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepo,
        private CatalogService $catalogService 
    ) {}

    public function getOrders(int $userId, string $role): array
    {
        if ($role === 'admin') {
            $orders = $this->orderRepo->findAll();
        } else {
            $orders = $this->orderRepo->findByUserId($userId);
        }

        $allIds = $orders->flatMap(fn($o) => $o->orderItems->pluck('ingredient_id'))
            ->unique()->values()->toArray();

        $ingMap = $this->fetchIngredientMap($allIds);

        return $orders->map(fn($o) => $this->formatOrder($o, $ingMap))->values()->toArray();
    }

    public function getOrder(int $orderId, int $userId, string $role): array
    {
        $order = $this->orderRepo->findById($orderId);

        if (!$order) {
            throw new \Exception('Order not found', 404);
        }

        if ($role !== 'admin' && (int)$order->user_id !== $userId) {
            throw new \Exception('Forbidden', 403);
        }

        $ids    = $order->orderItems->pluck('ingredient_id')->toArray();
        $ingMap = $this->fetchIngredientMap($ids);

        return $this->formatOrder($order, $ingMap);
    }

    public function updateStatus(int $orderId, string $status, string $role): array
    {
        if ($role !== 'admin') {
            throw new \Exception('Only admins can update orders', 403);
        }

        $order = $this->orderRepo->findById($orderId);
        if (!$order) {
            throw new \Exception('Order not found', 404);
        }

        $order  = $this->orderRepo->updateStatus($order, $status);
        $ids    = $order->orderItems->pluck('ingredient_id')->toArray();
        $ingMap = $this->fetchIngredientMap($ids);

        return $this->formatOrder($order, $ingMap);
    }

    public function formatOrder(Order $order, array $ingMap = []): array
    {
        return [
            'order_id'    => $order->order_id,
            'user_id'     => $order->user_id,
            'status'      => $order->status,
            'total_price' => (float)$order->total_price,
            'created_at'  => $order->created_at?->toISOString(),
            'items'       => $order->orderItems->map(fn($it) => [
                'order_item_id' => $it->order_item_id,
                'ingredient_id' => $it->ingredient_id,
                'amount'        => (int)$it->amount,
                'total_price'   => (float)$it->total_price,
                'ingredient'    => $ingMap[$it->ingredient_id] ?? null,  // DODAJ OVO
            ])->values(),
        ];
    }

    private function fetchIngredientMap(array $ids): array
    {
        if (empty($ids)) return [];

        $ingredients = $this->catalogService->getIngredientsByIds($ids);
        return collect($ingredients)->keyBy('id')->toArray();
    }
}