<?php
namespace App\Http\Controllers;
 
use App\Models\Order;
use App\Services\CircuitBreaker;
 
class MetricsController extends Controller
{
    public function index()
    {
        $totalOrders    = Order::count();
        $paidOrders     = Order::where('status', 'plaćeno')->count();
        $deliveredOrders = Order::where('status', 'isporučeno')->count();
        $cancelledOrders = Order::where('status', 'otkazano')->count();
        $memUsage       = memory_get_usage(true);
 
        $cb = new CircuitBreaker('catalog-service');
        $cbState = $cb->getState();
 
        $m = "# HELP ordering_orders_total Total orders\n";
        $m .= "# TYPE ordering_orders_total gauge\n";
        $m .= "ordering_orders_total {$totalOrders}\n\n";
 
        $m .= "# HELP ordering_orders_by_status Orders by status\n";
        $m .= "# TYPE ordering_orders_by_status gauge\n";
        $m .= "ordering_orders_by_status{status=\"placeno\"} {$paidOrders}\n";
        $m .= "ordering_orders_by_status{status=\"isporuceno\"} {$deliveredOrders}\n";
        $m .= "ordering_orders_by_status{status=\"otkazano\"} {$cancelledOrders}\n\n";
 
        $cbStateNum = $cbState === 'CLOSED' ? 0 : ($cbState === 'HALF-OPEN' ? 1 : 2);
        $m .= "# HELP circuit_breaker_state Circuit breaker state (0=CLOSED, 1=HALF-OPEN, 2=OPEN)\n";
        $m .= "# TYPE circuit_breaker_state gauge\n";
        $m .= "circuit_breaker_state{service=\"catalog-service\"} {$cbStateNum}\n\n";
 
        $m .= "# HELP ordering_memory_bytes Memory usage\n";
        $m .= "# TYPE ordering_memory_bytes gauge\n";
        $m .= "ordering_memory_bytes {$memUsage}\n";
 
        return response($m, 200)->header('Content-Type', 'text/plain; version=0.0.4');
    }
}