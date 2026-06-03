<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CsrfProtection
{
    private array $stateMutatingMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    private array $except = [
        'api/auth/login',
        'api/auth/register',
        'api/csrf-token',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        if (!in_array($request->method(), $this->stateMutatingMethods, true)) {
            return $next($request);
        }

        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        $sessionToken = (string) session('csrf_token', '');
        $requestToken = (string) ($request->header('X-CSRF-TOKEN') ?? $request->input('_token', ''));

        if ($sessionToken === '' || $requestToken === '' || !hash_equals($sessionToken, $requestToken)) {
            return response()->json(['error' => 'CSRF token mismatch'], 419);
        }

        return $next($request);
    }
}
