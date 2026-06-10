<?php
namespace App\Console\Commands;

use App\Kafka\InventoryEventConsumer;
use Illuminate\Console\Command;

class ConsumeInventoryEvents extends Command
{
    protected $signature   = 'kafka:consume-inventory-events';
    protected $description = 'Consume inventory-updated and greska-pri-obradi events';

    public function handle(): void
    {
        (new InventoryEventConsumer())->run();
    }
}