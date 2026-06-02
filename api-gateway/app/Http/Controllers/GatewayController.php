<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GatewayController extends Controller
{
    public function proxy(Request $request, string $service, string $path = ''): mixed
    {
        $serviceUrl = $this->resolveServiceUrl($service);

        if (!$serviceUrl) {
            return response()->json(['error' => "Nepoznat servis: {$service}"], 404);
        }

        $url = rtrim($serviceUrl, '/') . '/' . ltrim($path, '/');

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->buildForwardHeaders($request))
                ->send($request->method(), $url, [
                    'json'  => $request->isJson() ? $request->json()->all() : $request->all(),
                    'query' => $request->query(),
                ]);

            return response($response->body(), $response->status())
                ->withHeaders(['Content-Type' => $response->header('Content-Type', 'application/json')]);
        } catch (\Throwable $e) {
            Log::error("[Gateway] Proxy greska {$service}: {$e->getMessage()}");
            return response()->json(['error' => 'Servis je nedostupan'], 503);
        }
    }

    private function resolveServiceUrl(string $service): ?string
    {
        return match ($service) {
            'users'    => env('USER_SERVICE_URL'),
            'catalog'  => env('CATALOG_SERVICE_URL'),
            'ordering' => env('ORDERING_SERVICE_URL'),
            default    => null,
        };
    }

    private function buildForwardHeaders(Request $request): array
    {
        $headers = [
            'Accept'         => 'application/json',
            'Content-Type'   => 'application/json',
            'X-User-Id'      => $request->header('X-User-Id', ''),
            'X-User-Email'   => $request->header('X-User-Email', ''),
            'X-User-Role'    => $request->header('X-User-Role', ''),
            'X-Forwarded-By' => 'api-gateway',
        ];

        if ($request->hasHeader('Authorization')) {
            $headers['Authorization'] = $request->header('Authorization');
        }

        return $headers;
    }

    public function csrfToken(Request $request)
    {
        $token = bin2hex(random_bytes(32));
        session(['csrf_token' => $token]);

        return response()->json(['csrf_token' => $token])
            ->cookie('XSRF-TOKEN', $token, 120, '/', null, false, false, false, 'Lax');
    }
}
