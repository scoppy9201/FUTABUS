<?php

declare(strict_types=1);

namespace FuteBus\Auth\Services;

use App\Models\User;
use FuteBus\Auth\Mail\PasswordResetOtpMail;
use FuteBus\Auth\Mail\PasswordChangedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PasswordRecoveryService
{
    private const SESSION_KEY = 'password_recovery';
    private const OTP_ERRORS = 'Auth::app.password_recovery.errors';
    private const VERIFIED_LIFETIME_SECONDS = 900;

    public function __construct(private readonly OtpChallengeService $otp) {}

    public function viewData(Request $request): array
    {
        $flow = $request->session()->get(self::SESSION_KEY, []);

        return [
            'step' => $flow['step'] ?? 'email',
            'recoveryEmail' => $flow['email'] ?? '',
            'otpExpiresAt' => $flow['otp_expires_at'] ?? 0,
            'otpResendAt' => $flow['otp_resend_at'] ?? 0,
        ];
    }

    public function requestCode(Request $request, string $email): void
    {
        $flow = $request->session()->get(self::SESSION_KEY, []);
        $this->otp->assertUnlocked($flow, self::OTP_ERRORS);
        if (($flow['email'] ?? null) === $email) {
            $this->otp->assertResendAllowed($flow, self::OTP_ERRORS);
        }

        $user = User::where('email', $email)->first();
        if ($user === null) {
            $request->session()->forget(self::SESSION_KEY);
            return;
        }

        $code = (string) random_int(100000, 999999);
        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($code));
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['email' => __('Auth::app.password_recovery.errors.mail_failed')]);
        }

        $request->session()->put(self::SESSION_KEY, $this->otp->issue([
            'step' => 'otp',
            'user_id' => $user->getKey(),
            'email' => $user->email,
        ], $code));
    }

    public function resendCode(Request $request): void
    {
        $flow = $this->requireStep($request, 'otp');
        $this->requestCode($request, $flow['email']);
    }

    public function verifyCode(Request $request, string $code): void
    {
        $flow = $this->requireStep($request, 'otp');
        $this->otp->verify($request, $flow, $code, self::SESSION_KEY, self::OTP_ERRORS);

        $flow = $this->otp->clear($flow);
        $flow['step'] = 'password';
        $flow['verified_until'] = now()->timestamp + self::VERIFIED_LIFETIME_SECONDS;
        $request->session()->put(self::SESSION_KEY, $flow);
    }

    public function resetPassword(Request $request, string $password): void
    {
        $flow = $this->requireStep($request, 'password');
        if (($flow['verified_until'] ?? 0) < now()->timestamp) {
            $request->session()->forget(self::SESSION_KEY);
            throw ValidationException::withMessages(['password' => __('Auth::app.password_recovery.errors.verification_expired')]);
        }

        $user = User::find($flow['user_id']);
        if ($user === null || $user->email !== $flow['email']) {
            $request->session()->forget(self::SESSION_KEY);
            throw ValidationException::withMessages(['email' => __('Auth::app.password_recovery.errors.invalid_session')]);
        }

        $user->password = $password;
        $user->remember_token = Str::random(60);
        $user->save();
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $user->getKey())->delete();
        }
        $request->session()->forget(self::SESSION_KEY);

        try {
            Mail::to($user->email)->send(new PasswordChangedMail);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function requireStep(Request $request, string $step): array
    {
        $flow = $request->session()->get(self::SESSION_KEY, []);
        if (($flow['step'] ?? null) !== $step) {
            throw ValidationException::withMessages(['recovery' => __('Auth::app.password_recovery.errors.invalid_session')]);
        }

        return $flow;
    }
}
