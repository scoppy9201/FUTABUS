<?php

namespace App\Http\Middleware;

use App\Support\Auth\RoleRedirector;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                RoleRedirector::clearUnsafeIntendedFor($user);

                return RoleRedirector::redirectFor($user);
            }
        }

        return $next($request);
    }
}
