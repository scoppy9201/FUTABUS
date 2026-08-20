<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! method_exists($user, 'hasPermissionTo') || ! $user->hasPermissionTo($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
