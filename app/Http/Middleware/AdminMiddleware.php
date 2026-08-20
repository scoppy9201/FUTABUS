<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $hasAdminRoleMethod = method_exists($user, 'hasRole') && $user->hasRole('admin');

        if (! $user->isAdmin() && ! $hasAdminRoleMethod) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
