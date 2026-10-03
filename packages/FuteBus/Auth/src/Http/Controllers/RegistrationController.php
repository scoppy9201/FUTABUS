<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Controllers;

use FuteBus\Auth\Http\Requests\CompleteRegistrationRequest;
use FuteBus\Auth\Http\Requests\SendRegistrationEmailRequest;
use FuteBus\Auth\Http\Requests\SetRegistrationPasswordRequest;
use FuteBus\Auth\Http\Requests\VerifyRegistrationOtpRequest;
use FuteBus\Auth\Services\RegistrationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegistrationService $registration) {}

    public function show(Request $request): View
    {
        return view('Auth::register', $this->registration->viewData($request));
    }

    public function sendEmail(SendRegistrationEmailRequest $request): RedirectResponse
    {
        $this->registration->startEmail($request, $request->validated('email'));

        return redirect()->route('register')->with('status', __('Auth::app.registration_flow.status.otp_sent'));
    }

    public function resendEmail(Request $request): RedirectResponse
    {
        $this->registration->resendEmail($request);

        return redirect()->route('register')->with('status', __('Auth::app.registration_flow.status.otp_resent'));
    }

    public function verifyEmail(VerifyRegistrationOtpRequest $request): RedirectResponse
    {
        $this->registration->verifyEmail($request, $request->validated('otp'));

        return redirect()->route('register');
    }

    public function submitPassword(SetRegistrationPasswordRequest $request): RedirectResponse
    {
        $this->registration->setPassword($request, $request->validated('password'));

        return redirect()->route('register');
    }

    public function submitProfile(CompleteRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->registration->complete($request, $data['name'], $data['phone']);

        return redirect()->route('login')->with('status', __('Auth::app.registration_flow.status.completed'));
    }
}
