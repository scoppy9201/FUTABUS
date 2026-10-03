<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Controllers;

use App\Support\Auth\RoleRedirector;
use FuteBus\Auth\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages(['email' => __('Auth::app.registration_flow.errors.invalid_credentials')]);
        }

        if (Auth::user()->email_verified_at === null) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => __('Auth::app.registration_flow.errors.email_unverified')]);
        }

        $request->session()->regenerate();

        return RoleRedirector::redirectAfterLoginFor(Auth::user());
    }
}
