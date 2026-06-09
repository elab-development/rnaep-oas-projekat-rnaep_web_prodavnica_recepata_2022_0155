<?php
namespace App\Services;
 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
 
class CircuitBreaker
{
    private string $serviceName;
    private int $threshold;
    private int $timeout;
 
    public function __construct(string $serviceName, int $threshold = 5, int $timeout = 30)
    {
        $this->serviceName = $serviceName;
        $this->threshold   = $threshold;
        $this->timeout     = $timeout;
    }
 
    private function keyState(): string        { return "cb:{$this->serviceName}:state"; }
    private function keyFailures(): string     { return "cb:{$this->serviceName}:failures"; }
    private function keyLastFailure(): string  { return "cb:{$this->serviceName}:last_failure"; }
 
    public function getState(): string
    {
        return Cache::get($this->keyState(), 'CLOSED');
    }

    public function isAvailable(): bool
    {
        $state = $this->getState();
 
        if ($state === 'CLOSED') {
            return true;
        }
 
        if ($state === 'OPEN') {
            $lastFailure = Cache::get($this->keyLastFailure(), 0);
            if ((time() - $lastFailure) > $this->timeout) {
                Cache::put($this->keyState(), 'HALF-OPEN', 300);
                Log::info("[CircuitBreaker:{$this->serviceName}] OPEN → HALF-OPEN");
                return true;
            }
            return false;
        }
 
        return true;
    }
 
    public function recordSuccess(): void
    {
        if ($this->getState() === 'HALF-OPEN') {
            Cache::put($this->keyState(), 'CLOSED', 300);
            Cache::put($this->keyFailures(), 0, 300);
            Log::info("[CircuitBreaker:{$this->serviceName}] HALF-OPEN → CLOSED (recovered!)");
        }
    }
 
    public function recordFailure(): void
    {
        $failures = Cache::increment($this->keyFailures());
        Cache::put($this->keyLastFailure(), time(), 300);
 
        Log::warning("[CircuitBreaker:{$this->serviceName}] Failure #{$failures}");
 
        if ($failures >= $this->threshold) {
            Cache::put($this->keyState(), 'OPEN', 300);
            Log::error("[CircuitBreaker:{$this->serviceName}] CLOSED → OPEN (too many failures!)");
        }
    }
}