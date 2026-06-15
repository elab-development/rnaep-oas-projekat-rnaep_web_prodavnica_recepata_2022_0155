<?php

namespace Tests\Unit;

use App\Services\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    public function test_calculates_line_price(): void
    {
        $calculator = new PriceCalculator();

        $result = $calculator->calculateLinePrice(120, 3);

        $this->assertSame(360.0, $result);
    }

    public function test_calculates_line_price_with_decimal_price(): void
    {
        $calculator = new PriceCalculator();

        $result = $calculator->calculateLinePrice(99.99, 2);

        $this->assertSame(199.98, $result);
    }

    public function test_zero_amount_returns_zero(): void
    {
        $calculator = new PriceCalculator();

        $result = $calculator->calculateLinePrice(120, 0);

        $this->assertSame(0.0, $result);
    }
}