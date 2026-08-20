<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // Bỏ qua cho admin hoặc một số IP nhất định
        if ($request->user() && $request->user()->isAdmin()) {
            return $next($request);
        }

        if (file_exists(storage_path('framework/down'))) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hệ thống đang bảo trì. Vui lòng quay lại sau.',
                ], 503);
            }

            return response()->view('maintenance', [], 503);
        }

        return $next($request);
    }
}
