<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RecordRequestMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('metrics_started', microtime(true));
        $supplied = (string) $request->header('X-Request-ID', '');
        $id = preg_match('/\A[a-zA-Z0-9._-]{8,80}\z/', $supplied) ? $supplied : (string) Str::uuid();
        $request->attributes->set('metrics_request_id', $id);
        $response = $next($request);
        if ($request->is('api/*')) {
            $response->headers->set('X-Request-ID', $id);
            $response->headers->set('Server-Timing', 'app;dur='.round((microtime(true) - $request->attributes->get('metrics_started')) * 1000, 2));
        }
        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->is('api/*')) {
            return;
        }
        Log::info('operations.request', [
            'request_id' => $request->attributes->get('metrics_request_id'),
            'method' => $request->method(),
            'route' => $request->route()?->uri() ?? 'unmatched',
            'status' => $response->getStatusCode(),
            'duration_ms' => round((microtime(true) - (float) $request->attributes->get('metrics_started', microtime(true))) * 1000, 2),
            'php_peak_memory_bytes' => memory_get_peak_usage(true),
        ]);
    }
}
