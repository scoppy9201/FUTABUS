<?php

declare(strict_types=1);

namespace FuteBus\Auth\Services;

use App\Models\User;
use FuteBus\Auth\Mail\AccountActivatedMail;
use FuteBus\Auth\Mail\RegistrationOtpMail;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegistrationService
{
    public function __construct(private readonly RegistrationOtpService $otp) {}

    public function viewData(Request $request): array
    {
        $flow = $request->session()->get('registration', []);

        return [
            'step' => $flow['step'] ?? 'email',
            'registrationEmail' => $flow['email'] ?? '',
            'otpExpiresAt' => $flow['otp_expires_at'] ?? 0,
            'otpResendAt' => $flow['otp_resend_at'] ?? 0,
        ];
    }

    public function startEmail(Request $request, string $email): void
    {
        $flow = $request->session()->get('registration', []);
        $this->otp->assertUnlocked($flow);
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => __('Auth::app.registration_flow.validation.email_taken')]);
        }
        if (($flow['email'] ?? null) === $email) {
            $this->otp->assertResendAllowed($flow);
        }

        $code = (string) random_int(100000, 999999);
        try {
            Mail::to($email)->send(new RegistrationOtpMail($code));
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['email' => __('Auth::app.registration_flow.errors.mail_failed')]);
        }

        $request->session()->put('registration', $this->otp->issue([
            'step' => 'email_otp',
            'email' => $email,
        ], $code));
    }

    public function resendEmail(Request $request): void
    {
        $flow = $this->requireStep($request, 'email_otp');
        $this->startEmail($request, $flow['email']);
    }

    public function verifyEmail(Request $request, string $code): void
    {
        $flow = $this->requireStep($request, 'email_otp');
        $this->otp->verify($request, $flow, $code);
        $flow = $this->otp->clear($flow);
        $flow['step'] = 'password';
        $request->session()->put('registration', $flow);
    }

    public function setPassword(Request $request, string $password): void
    {
        $flow = $this->requireStep($request, 'password');
        $flow['password_hash'] = Hash::make($password);
        $flow['step'] = 'profile';
        $request->session()->put('registration', $flow);
    }

    public function complete(Request $request, string $name, string $submittedPhone): User
    {
        $flow = $this->requireStep($request, 'profile');
        $phone = $this->normalizePhone($submittedPhone);
        if (User::whereIn('phone', [$phone, '0'.substr($phone, 3)])->exists()) {
            throw ValidationException::withMessages(['phone' => __('Auth::app.registration_flow.errors.phone_taken')]);
        }
        if (User::where('email', $flow['email'])->exists()) {
            throw ValidationException::withMessages(['email' => __('Auth::app.registration_flow.validation.email_taken')]);
        }

        try {
            $user = new User([
                'name' => trim($name),
                'email' => $flow['email'],
                'password' => $flow['password_hash'],
                'phone' => $phone,
            ]);
            $user->email_verified_at = now();
            $user->save();
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                throw ValidationException::withMessages(['phone' => __('Auth::app.registration_flow.errors.identity_taken')]);
            }
            throw $exception;
        }

        $request->session()->forget('registration');

        try {
            Mail::to($user->email)->send(new AccountActivatedMail);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $user;
    }

    private function requireStep(Request $request, string $step): array
    {
        $flow = $request->session()->get('registration', []);
        if (($flow['step'] ?? null) !== $step) {
            throw ValidationException::withMessages(['registration' => __('Auth::app.registration_flow.errors.invalid_session')]);
        }

        return $flow;
    }

    private function normalizePhone(string $phone): string
    {
        return str_starts_with($phone, '0') ? '+84'.substr($phone, 1) : $phone;
    }
}
