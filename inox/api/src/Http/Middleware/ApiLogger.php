<?php

namespace Inox\Api\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inox\Api\Models\ApiLogEntry;

class ApiLogger
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!config('inox.api.log_enabled', true)) {
            return $next($request);
        }

        $start = microtime(true);

        $response = $next($request);

        $duration = (int) round((microtime(true) - $start) * 1000);

        try {
            ApiLogEntry::create([
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_id' => $request->user()?->id,
                'duration_ms' => $duration,
                'request_headers' => $request->headers->all(),
                'request_body' => $request->getContent(),
                'response_body' => mb_substr($response->getContent() ?? '', 0, 10000),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ApiLogger failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $response;
    }
}
