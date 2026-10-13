<?php

declare(strict_types=1);

namespace Staff\Dashboard\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isStaff() && $user->is_active && $user->email_verified_at !== null, 403);

        return $next($request);
    }
}
