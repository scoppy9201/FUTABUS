<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isStaff() && $user->is_active && $user->email_verified_at !== null, 403);
        if ($request->is('api/*')) {
            if ($user->currentAccessToken() instanceof PersonalAccessToken) {
                abort_unless($user->tokenCan('api'), 403);
            } elseif (! $request->isMethodSafe()) {
                $token = $request->header('X-CSRF-TOKEN') ?? $request->input('_token');
                abort_unless($request->hasSession() && is_string($token) && hash_equals($request->session()->token(), $token), 419);
            }
        }

        return $next($request);
    }
}
