<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Controllers;

use FuteBus\Auth\Http\Requests\RequestPasswordRecoveryOtpRequest;
use FuteBus\Auth\Http\Requests\ResetPasswordRequest;
use FuteBus\Auth\Http\Requests\VerifyRecoveryOtpRequest;
use FuteBus\Auth\Services\PasswordRecoveryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PasswordRecoveryController extends Controller
{
    public function __construct(private readonly PasswordRecoveryService $recovery) {}

    public function show(Request $request): View
    {
        return view('Auth::forgot-password', $this->recovery->viewData($request));
    }

    public function requestCode(RequestPasswordRecoveryOtpRequest $request): RedirectResponse
    {
        $this->recovery->requestCode($request, $request->validated('email'));

        return redirect()->route('password.request')->with('status', __('Auth::app.password_recovery.request_status'));
    }

    public function resendCode(Request $request): RedirectResponse
    {
        $this->recovery->resendCode($request);

        return redirect()->route('password.request')->with('status', __('Auth::app.password_recovery.resend_status'));
    }

    public function verifyCode(VerifyRecoveryOtpRequest $request): RedirectResponse
    {
        $this->recovery->verifyCode($request, $request->validated('otp'));

        return redirect()->route('password.request');
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $this->recovery->resetPassword($request, $request->validated('password'));

        return redirect()->route('login')->with('status', __('Auth::app.password_recovery.completed'));
    }
}
