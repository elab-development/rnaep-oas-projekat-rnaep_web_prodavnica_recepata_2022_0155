<?php
namespace App\Kafka;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

class OrderCreatedConsumer
{
    private KafkaConsumer $consumer;
    private Producer $producer;

    public function __construct()
    {
        $consumerConf = new Conf();
        $consumerConf->set('bootstrap.servers', env('KAFKA_BROKERS', 'kafka:9092'));
        $consumerConf->set('group.id', 'catalog-service-group');
        $consumerConf->set('auto.offset.reset', 'earliest');

        $this->consumer = new KafkaConsumer($consumerConf);
        $this->consumer->subscribe(['order-created']);

        $producerConf = new Conf();
        $producerConf->set('bootstrap.servers', env('KAFKA_BROKERS', 'kafka:9092'));
        $this->producer = new Producer($producerConf);
    }

    public function run(): void
    {
        while (true) {
            $message = $this->consumer->consume(120 * 1000);

            if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $payload = json_decode($message->payload, true);
                Log::info('[CatalogService] order-created received', $payload);
                $this->handleOrderCreated($payload ?? []);
            }
        }
    }
    private function handleOrderCreated(array $payload): void
    {
        $items = $payload['items'] ?? [];
        $errors = [];

        $ingredientIds = array_column($items, 'ingredient_id');
        $ingredients = Ingredient::whereIn('id', $ingredientIds)
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {

            $ingredient = $ingredients[$item['ingredient_id']] ?? null;

            if (!$ingredient) {
                $errors[] = "Ingredient {$item['ingredient_id']} not found";
                continue;
            }

            if ((float)$ingredient->stock_quantity < (float)$item['amount']) {
                $errors[] = "Insufficient stock for {$ingredient->name}";
            }
        }

        if (!empty($errors)) {
            $this->publish('greska-pri-obradi', [
                'order_id'  => $payload['order_id'],
                'errors'    => $errors,
                'timestamp' => now()->toISOString(),
            ]);

            Log::error("[CatalogService] Order failed validation", [
                'order_id' => $payload['order_id'],
                'errors' => $errors
            ]);

            return;
        }

        foreach ($items as $item) {

            $ingredient = $ingredients[$item['ingredient_id']];

            $newStock = (float)$ingredient->stock_quantity - (float)$item['amount'];

            $ingredient->update([
                'stock_quantity' => max(0, $newStock)
            ]);

            Log::info("[CatalogService] Stock updated: {$ingredient->name} → {$newStock}");
        }

        $this->publish('inventory-updated', [
            'order_id'  => $payload['order_id'],
            'timestamp' => now()->toISOString(),
        ]);

        Log::info("[CatalogService] Inventory confirmed for order {$payload['order_id']}");
    }

    private function publish(string $topicName, array $event): void
    {
        $topic = $this->producer->newTopic($topicName);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($event));
        $this->producer->flush(10000);
        Log::info("[CatalogService] Published to {$topicName}", $event);
    }
}