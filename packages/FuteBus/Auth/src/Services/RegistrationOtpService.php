<?php

declare(strict_types=1);

namespace FuteBus\Auth\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrationOtpService
{
    private const LIFETIME_SECONDS = 300;
    private const RESEND_SECONDS = 60;
    private const LOCK_SECONDS = 900;

    public function issue(array $flow, string $code): array
    {
        return array_merge($flow, [
            'otp_hash' => Hash::make($code),
            'otp_expires_at' => now()->timestamp + self::LIFETIME_SECONDS,
            'otp_resend_at' => now()->timestamp + self::RESEND_SECONDS,
            'attempts' => 0,
            'locked_until' => 0,
        ]);
    }

    public function verify(Request $request, array $flow, string $code): void
    {
        $this->assertUnlocked($flow);

        if (now()->timestamp > ($flow['otp_expires_at'] ?? 0)) {
            throw ValidationException::withMessages(['otp' => __('Auth::app.registration_flow.errors.otp_expired')]);
        }

        if (! Hash::check($code, $flow['otp_hash'] ?? '')) {
            $flow['attempts'] = ($flow['attempts'] ?? 0) + 1;
            if ($flow['attempts'] >= 5) {
                $flow['locked_until'] = now()->timestamp + self::LOCK_SECONDS;
            }
            $request->session()->put('registration', $flow);

            throw ValidationException::withMessages(['otp' => $flow['attempts'] >= 5
                ? __('Auth::app.registration_flow.errors.otp_locked')
                : __('Auth::app.registration_flow.errors.otp_invalid')]);
        }
    }

    public function assertUnlocked(array $flow): void
    {
        if (($flow['locked_until'] ?? 0) > now()->timestamp) {
            throw ValidationException::withMessages(['otp' => __('Auth::app.registration_flow.errors.otp_locked')]);
        }
    }

    public function assertResendAllowed(array $flow): void
    {
        if (($flow['otp_resend_at'] ?? 0) > now()->timestamp) {
            throw ValidationException::withMessages(['otp' => __('Auth::app.registration_flow.errors.resend_wait')]);
        }
    }

    public function clear(array $flow): array
    {
        unset($flow['otp_hash'], $flow['otp_expires_at'], $flow['otp_resend_at'], $flow['attempts'], $flow['locked_until']);

        return $flow;
    }
}
