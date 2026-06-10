<?php
namespace App\Http\Controllers;
 
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Http\Request;
 
class OrderController extends Controller
{
    public function __construct(
        private OrderService    $orderService,
        private CheckoutService $checkoutService
    ) {}
 
    public function index(Request $request)
    {
        $userId = (int)$request->header('X-User-Id');
        $role   = $request->header('X-User-Role');
 
        $orders = $this->orderService->getOrders($userId, $role);
        return response()->json(['orders' => $orders]);
    }
 
    public function show(Request $request, int $id)
    {
        $userId = (int)$request->header('X-User-Id');
        $role   = $request->header('X-User-Role');
 
        try {
            $order = $this->orderService->getOrder($id, $userId, $role);
            return response()->json(['order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
 
    public function update(Request $request, int $id)
    {
        $role = $request->header('X-User-Role');
 
        $validated = $request->validate([
            'status' => 'required|in:plaćeno,isporučeno,otkazano',
        ]);
 
        try {
            $order = $this->orderService->updateStatus($id, $validated['status'], $role);
 
            return response()->json(['message' => 'Order updated', 'order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
 
    public function store(Request $request)
    {
        $userId = (int)$request->header('X-User-Id');
        $role   = $request->header('X-User-Role');
 
        if ($role !== 'user') {
            return response()->json(['error' => 'Only users can create orders'], 403);
        }
 
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|string',
            'items.*.amount'        => 'required|integer|min:1',
        ]);
 
        try {
            $order = $this->checkoutService->checkoutDirect($userId, $request->input('items'));
            return response()->json(['message' => 'Order created', 'order' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
 
    public function checkout(Request $request)
    {
        $userId = (int)$request->header('X-User-Id');
        $role   = $request->header('X-User-Role');
 
        if ($role !== 'user') {
            return response()->json(['error' => 'Only users can checkout'], 403);
        }
 
        try {
            $order = $this->checkoutService->checkoutFromCart($userId);
            return response()->json(['message' => 'Checkout successful', 'order' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}