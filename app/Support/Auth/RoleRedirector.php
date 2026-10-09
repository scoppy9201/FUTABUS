<?php

namespace App\Support\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

class RoleRedirector
{
    public static function pathFor(?Authenticatable $user): string
    {
        if ($user && ((method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'hasRole') && $user->hasRole('bus-company')))) {
            return Route::has('dashboard') ? route('dashboard', [], false) : '/dashboard';
        }

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('staff')) {
            if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('dashboard.view')) {
                return Route::has('dashboard') ? route('dashboard', [], false) : '/dashboard';
            }

            foreach ([
                'trips' => 'trip.view',
                'routes' => 'route.view',
                'buses' => 'bus.view',
                'reports' => 'report.view',
            ] as $section => $permission) {
                if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission)) {
                    return Route::has('dashboard.section')
                        ? route('dashboard.section', $section, false)
                        : '/quan-tri/'.$section;
                }
            }
        }

        return Route::has('home') ? route('home', [], false) : '/';
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
