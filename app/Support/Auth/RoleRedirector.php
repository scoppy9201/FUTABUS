<?php

namespace App\Support\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RoleRedirector
{
    public static function pathFor(?Authenticatable $user): string
    {
        return match ($user?->role ?? null) {
            'admin' => Route::has('dashboard') ? route('dashboard', [], false) : '/dashboard',
            'staff' => Route::has('dashboard') ? route('dashboard', [], false) : '/dashboard',
            'customer' => Route::has('home') ? route('home', [], false) : '/',
            default => Route::has('home') ? route('home', [], false) : '/',
        };
    }

    public static function redirectFor(?Authenticatable $user): RedirectResponse
    {
        $response = new RedirectResponse(self::pathFor($user));

        if (app()->bound('session.store')) {
            $response->setSession(session()->driver());
        }

        return $response;
    }

    public static function redirectAfterLoginFor(?Authenticatable $user): RedirectResponse
    {
        self::clearUnsafeIntendedFor($user);

        return redirect()->intended(self::pathFor($user));
    }

    public static function clearUnsafeIntendedFor(?Authenticatable $user): void
    {
        $intended = session('url.intended');

        if ($intended && self::isPublicUrl($intended)) {
            session()->forget('url.intended');
        }
    }

    private static function isPublicUrl(string $url): bool
    {
        return true;
    }
}