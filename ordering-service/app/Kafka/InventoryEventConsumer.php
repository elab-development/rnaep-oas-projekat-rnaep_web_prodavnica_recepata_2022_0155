<?php
namespace App\Kafka;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class InventoryEventConsumer
{
    private KafkaConsumer $consumer;

    public function __construct()
    {
        $conf = new Conf();
        $conf->set('bootstrap.servers', env('KAFKA_BROKERS', 'kafka:9092'));
        $conf->set('group.id', 'ordering-inventory-group');
        $conf->set('auto.offset.reset', 'earliest');

        $this->consumer = new KafkaConsumer($conf);
        $this->consumer->subscribe(['inventory-updated', 'greska-pri-obradi']);
    }

    public function run(): void
    {
        Log::info('[OrderingService] Listening on: inventory-updated, greska-pri-obradi');

        while (true) {
            $message = $this->consumer->consume(120 * 1000);

            if ($message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $payload   = json_decode($message->payload, true) ?? [];
                $topicName = $message->topic_name;

                Log::info("[OrderingService] Received on {$topicName}", $payload);

                if ($topicName === 'inventory-updated') {
                    $this->handleInventoryUpdated($payload);
                }

                if ($topicName === 'greska-pri-obradi') {
                    $this->handleError($payload);
                }
            }
        }
    }

    private function handleInventoryUpdated(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        if (!$orderId) return;

        Order::where('order_id', $orderId)
            ->update(['status' => 'isporučeno']);

        Log::info("[OrderingService] Order #{$orderId} → isporučeno");
    }

    private function handleError(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        if (!$orderId) return;

        Order::where('order_id', $orderId)
            ->update(['status' => 'otkazano']);

        Log::error("[OrderingService] Order #{$orderId} → otkazano", $payload['errors'] ?? []);
    }
}