<?php
namespace App\Console\Commands;
 
use App\Kafka\OrderCreatedConsumer;
use Illuminate\Console\Command;
 
class ConsumeOrderCreated extends Command
{
    protected $signature   = 'kafka:consume-orders';
    protected $description = 'Consume order-created events from Kafka';
 
    public function handle(): void
    {
        $consumer = new OrderCreatedConsumer();
        $consumer->run();
    }
}