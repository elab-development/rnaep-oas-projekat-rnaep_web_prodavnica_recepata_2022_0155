<?php
namespace App\Kafka;
 
use Illuminate\Support\Facades\Log;
use RdKafka\Conf;
use RdKafka\Producer;
 
class KafkaProducer
{
    private Producer $producer;
 
    public function __construct()
    {
        $conf = new Conf();
        $conf->set('bootstrap.servers', env('KAFKA_BROKERS', 'kafka:9092'));
        $this->producer = new Producer($conf);
    }
 
    public function publish(string $topic, array $payload): void
    {
        try {
            $kafkaTopic = $this->producer->newTopic($topic);
            $kafkaTopic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($payload));
            $this->producer->flush(10000);
            Log::info("[KafkaProducer] Published to {$topic}", $payload);
        } catch (\Throwable $e) {
            Log::error("[KafkaProducer] Failed to publish to {$topic}: {$e->getMessage()}");
        }
    }
}