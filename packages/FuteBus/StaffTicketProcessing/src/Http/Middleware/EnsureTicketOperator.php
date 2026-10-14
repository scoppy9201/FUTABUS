<?php

declare(strict_types=1);

namespace FuteBus\StaffTicketProcessing\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureTicketOperator
{
    public function handle(Request $request, Closure $next, string $permission = ''): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User && ($user->isStaff() || $user->isBusCompany()) && $user->is_active && $user->email_verified_at !== null, 403);
        if ($user->currentAccessToken() instanceof PersonalAccessToken) {
            abort_unless($user->tokenCan('api'), 403);
        }
        if ($permission !== '') {
            abort_unless($user->bus_company_id && $user->hasPermissionTo($permission), 403);
        }
        if (! $request->isMethodSafe() && ! ($user->currentAccessToken() instanceof PersonalAccessToken)) {
            $token = $request->header('X-CSRF-TOKEN') ?? $request->input('_token');
            abort_unless($request->hasSession() && is_string($token) && hash_equals($request->session()->token(), $token), 419);
        }

        return $next($request);
    }
}
