<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        abort_unless(
            $token instanceof PersonalAccessToken && $token->can('api')
                && $user->is_active && $user->email_verified_at !== null,
            403,
        );

        return $next($request);
    }
}
