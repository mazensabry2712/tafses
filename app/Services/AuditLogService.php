<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(Request $request, ?int $statusCode = null, array $context = []): AuditLog
    {
        $user = $request->user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role,
            'action' => $this->actionName($request),
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => $request->path(),
            'status_code' => $statusCode,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'context' => $context ?: null,
        ]);
    }

    private function actionName(Request $request): string
    {
        return match ($request->method()) {
            'POST' => 'create_or_update',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };
    }
}
