<?php
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
 
class AuthMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();
 
        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }
 
        try {
            $response = Http::timeout(5)
                ->withToken($token)
                ->get(env('USER_SERVICE_URL') . '/verify');
 
            if (!$response->successful()) {
                return response()->json(['error' => 'Invalid or expired token'], 401);
            }
 
            $user = $response->json();
 
            $request->headers->set('X-User-Id',    $user['user_id']);
            $request->headers->set('X-User-Email', $user['email']);
            $request->headers->set('X-User-Role',  $user['role']);
 
        } catch (\Throwable $e) {
            Log::error('[Gateway] Auth verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication service unavailable'], 503);
        }
 
        return $next($request);
    }
}