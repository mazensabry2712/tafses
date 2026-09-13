<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next, AuditLogService $audit): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $audit->record($request, $response->getStatusCode());
        }

        return $response;
    }
}
