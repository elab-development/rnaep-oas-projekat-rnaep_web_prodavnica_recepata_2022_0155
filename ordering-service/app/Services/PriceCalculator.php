<?php
namespace App\Services;
 
class PriceCalculator
{
    public function calculateLinePrice(float $price, int $amount): float
    {
        return round($price * $amount, 2);
    }
 
    public function calculateTotal(array $items): float
    {
        return round(array_sum(array_map(
            fn($item) => $this->calculateLinePrice(
                (float)($item['price'] ?? 0),
                (int)($item['amount'] ?? 0)
            ),
            $items
        )), 2);
    }
 
    public function format(float $price): string
    {
        return number_format($price, 2, '.', '');
    }
}