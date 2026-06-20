<?php
namespace App\Services;
 
use App\Kafka\KafkaProducer;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
 
class CheckoutService
{
    public function __construct(
        private CartRepository  $cartRepo,
        private OrderRepository $orderRepo,
        private CatalogService  $catalogService,
        private PriceCalculator $calculator,
        private KafkaProducer   $kafkaProducer
    ) {}
 
    public function checkoutFromCart(int $userId): array
    {
        $cart = $this->cartRepo->findWithItemsByUserId($userId);
 
        if (!$cart || $cart->cartItems->isEmpty()) {
            throw new \Exception('Cart is empty', 422);
        }
 
        $ids         = $cart->cartItems->pluck('ingredient_id')->toArray();
        $ingredients = $this->catalogService->getIngredientsByIds($ids);
 
        if (empty($ingredients)) {
            throw new \Exception('Catalog service unavailable', 503);
        }
 
        $ingMap = collect($ingredients)->keyBy('id');
 
        return DB::transaction(function () use ($cart, $ingMap, $userId) {
            $order = $this->orderRepo->create($userId);
 
            $total     = 0.0;
            $kafkaItems = [];
 
            foreach ($cart->cartItems as $ci) {
                $ing  = $ingMap[$ci->ingredient_id] ?? null;
                if (!$ing) continue;
 
                $linePrice = $this->calculator->calculateLinePrice(
                    (float)$ing['price'],
                    (int)$ci->amount
                );
                $total += $linePrice;
 
                $this->orderRepo->addOrderItem(
                    $order->order_id,
                    $userId,
                    $ci->ingredient_id,
                    (int)$ci->amount,
                    $linePrice
                );
 
                $kafkaItems[] = [
                    'ingredient_id' => $ci->ingredient_id,
                    'amount'        => (int)$ci->amount,
                ];
            }
 
            $this->orderRepo->updateTotal($order, $total);
 
            $this->cartRepo->clearCart($cart->cart_id);
            $this->cartRepo->updateCartTotals($cart, 0, 0.0);
 
            $this->kafkaProducer->publish('order-created', [
                'order_id'  => $order->order_id,
                'user_id'   => $userId,
                'items'     => $kafkaItems,
                'total'     => $total,
                'timestamp' => now()->toISOString(),
            ]);
 
            return $this->formatCheckoutResponse($order->fresh(['orderItems']));
        });
    }
 
    public function checkoutDirect(int $userId, array $items): array
    {
        $ingredientIds = array_column($items, 'ingredient_id');
        $ingredients   = $this->catalogService->getIngredientsByIds($ingredientIds);
 
        if (empty($ingredients)) {
            throw new \Exception('Catalog service unavailable', 503);
        }
 
        $ingMap = collect($ingredients)->keyBy('id');
 
        return DB::transaction(function () use ($items, $ingMap, $userId) {
            $order = $this->orderRepo->create($userId);
 
            $total      = 0.0;
            $kafkaItems = [];
 
            foreach ($items as $row) {
                $ing = $ingMap[$row['ingredient_id']] ?? null;
                if (!$ing) continue;
 
                $linePrice = $this->calculator->calculateLinePrice(
                    (float)$ing['price'],
                    (int)$row['amount']
                );
                $total += $linePrice;
 
                $this->orderRepo->addOrderItem(
                    $order->order_id,
                    $userId,
                    $row['ingredient_id'],
                    (int)$row['amount'],
                    $linePrice
                );
 
                $kafkaItems[] = [
                    'ingredient_id' => $row['ingredient_id'],
                    'amount'        => (int)$row['amount'],
                ];
            }
 
            $this->orderRepo->updateTotal($order, $total);
 
            $this->kafkaProducer->publish('order-created', [
                'order_id'  => $order->order_id,
                'user_id'   => $userId,
                'items'     => $kafkaItems,
                'total'     => $total,
                'timestamp' => now()->toISOString(),
            ]);
 
            return $this->formatCheckoutResponse($order->fresh(['orderItems']));
        });
    }
 
    private function formatCheckoutResponse(\App\Models\Order $order): array
    {
        return [
            'order_id'    => $order->order_id,
            'status'      => $order->status,
            'total_price' => (float)$order->total_price,
            'items'       => $order->orderItems->map(fn($it) => [
                'ingredient_id' => $it->ingredient_id,
                'amount'        => (int)$it->amount,
                'total_price'   => (float)$it->total_price,
            ])->values(),
        ];
    }
}