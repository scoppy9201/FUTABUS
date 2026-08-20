<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Middleware
{
    abstract public function handle(Request $request, Closure $next): Response;

    protected function isAuthenticated(Request $request): bool
    {
        return $request->user() !== null;
    }

    protected function hasPermission(Request $request, string $permission): bool
    {
        $user = $request->user();

        return $user && method_exists($user, 'hasPermissionTo')
            ? $user->hasPermissionTo($permission)
            : false;
    }

    protected function hasRole(Request $request, string|array $roles): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        return method_exists($user, 'hasRole')
            ? $user->hasRole($roles)
            : in_array($user->role ?? '', (array) $roles, true);
    }

    protected function forbiddenResponse(string $message = 'Access denied.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    protected function unauthorizedResponse(string $message = 'Authentication is required.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }
}
