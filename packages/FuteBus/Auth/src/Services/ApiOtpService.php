<?php

declare(strict_types=1);

namespace FuteBus\Auth\Services;

use App\Models\User;
use FuteBus\Auth\Mail\AccountActivatedMail;
use FuteBus\Auth\Mail\PasswordChangedMail;
use FuteBus\Auth\Mail\PasswordResetOtpMail;
use FuteBus\Auth\Mail\RegistrationOtpMail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiOtpService
{
    private const OTP_SECONDS = 300;

    private const VERIFY_SECONDS = 900;

    private const RESEND_SECONDS = 60;

    public function startRegistration(string $email): string
    {
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => __('Auth::app.registration_flow.validation.email_taken'),
            ]);
        }

        return $this->start('registration', $email, null);
    }

    public function startRecovery(string $email): string
    {
        $userId = User::where('email', $email)->value('id');

        return $this->start('recovery', $email, $userId);
    }

    public function resend(string $token, string $purpose): void
    {
        $errorPrefix = $this->errors($purpose);
        $result = DB::transaction(function () use ($token, $purpose): ?string {
            $challenge = $this->find($token, $purpose);
            if ($challenge->verified_until !== null || $challenge->expires_at <= now()
                || $challenge->locked_until !== null && $challenge->locked_until > now()
                || $challenge->resends >= 5) {
                return 'otp_expired';
            }
            if ($challenge->resend_at > now()) {
                return 'resend_wait';
            }

            $code = $this->code();
            $this->send($purpose, $challenge->email, $code, $challenge->user_id !== null);
            DB::table('api_otp_challenges')->where('id', $challenge->id)->update([
                'code_hash'  => $challenge->user_id !== null || $purpose === 'registration' ? Hash::make($code) : null,
                'attempts'   => 0,
                'resends'    => $challenge->resends + 1,
                'expires_at' => now()->addSeconds(self::OTP_SECONDS),
                'resend_at'  => now()->addSeconds(self::RESEND_SECONDS),
                'updated_at' => now(),
            ]);

            return null;
        });

        if ($result !== null) {
            throw ValidationException::withMessages(['otp' => __("{$errorPrefix}.{$result}")]);
        }
    }

    public function verify(string $token, string $purpose, string $code): void
    {
        $result = DB::transaction(function () use ($token, $purpose, $code): ?string {
            $challenge = $this->find($token, $purpose);
            if ($challenge->locked_until !== null && $challenge->locked_until > now()) {
                return 'otp_locked';
            }
            if ($challenge->expires_at <= now() || $challenge->verified_until !== null) {
                return 'otp_expired';
            }
            if ($challenge->code_hash === null || ! Hash::check($code, $challenge->code_hash)) {
                $attempts = $challenge->attempts + 1;
                DB::table('api_otp_challenges')->where('id', $challenge->id)->update([
                    'attempts'     => $attempts,
                    'locked_until' => $attempts >= 5 ? now()->addSeconds(self::VERIFY_SECONDS) : null,
                    'updated_at'   => now(),
                ]);

                return $attempts >= 5 ? 'otp_locked' : 'otp_invalid';
            }

            DB::table('api_otp_challenges')->where('id', $challenge->id)->update([
                'code_hash'      => null,
                'verified_until' => now()->addSeconds(self::VERIFY_SECONDS),
                'updated_at'     => now(),
            ]);

            return null;
        });

        if ($result !== null) {
            throw ValidationException::withMessages(['otp' => __($this->errors($purpose).'.'.$result)]);
        }
    }

    public function completeRegistration(string $token, string $name, string $phone, string $password): User
    {
        $user = DB::transaction(function () use ($token, $name, $phone, $password): User {
            $challenge = $this->verified($token, 'registration');
            $normalizedPhone = str_starts_with($phone, '0') ? '+84'.substr($phone, 1) : $phone;
            if (User::where('email', $challenge->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => __('Auth::app.registration_flow.validation.email_taken'),
                ]);
            }
            if (User::whereIn('phone', [$normalizedPhone, '0'.substr($normalizedPhone, 3)])->exists()) {
                throw ValidationException::withMessages([
                    'phone' => __('Auth::app.registration_flow.errors.phone_taken'),
                ]);
            }

            try {
                $user = new User([
                    'name'     => trim($name),
                    'email'    => $challenge->email,
                    'password' => $password,
                    'phone'    => $normalizedPhone,
                ]);
                $user->email_verified_at = now();
                $user->save();
            } catch (QueryException $exception) {
                if (in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
                    throw ValidationException::withMessages([
                        'email' => __('Auth::app.registration_flow.errors.identity_taken'),
                    ]);
                }
                throw $exception;
            }

            $this->consume($challenge->id);

            return $user;
        });

        try {
            Mail::to($user->email)->send(new AccountActivatedMail);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $user;
    }

    public function resetPassword(string $token, string $password): void
    {
        $user = DB::transaction(function () use ($token, $password): User {
            $challenge = $this->verified($token, 'recovery');
            $user = User::whereKey($challenge->user_id)->where('email', $challenge->email)->first();
            if ($user === null) {
                throw ValidationException::withMessages([
                    'recovery' => __('Auth::app.password_recovery.errors.invalid_session'),
                ]);
            }

            $user->password = $password;
            $user->remember_token = Str::random(60);
            $user->save();
            $user->tokens()->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            if (config('session.driver') === 'database') {
                DB::table('sessions')->where('user_id', $user->getKey())->delete();
            }
            $this->consume($challenge->id);

            return $user;
        });

        try {
            Mail::to($user->email)->send(new PasswordChangedMail);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function start(string $purpose, string $email, ?int $userId): string
    {
        $rateKey = 'api-otp:'.hash('sha256', $purpose.':'.$email);
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            throw ValidationException::withMessages([
                'email' => __('Auth::app.registration_flow.errors.otp_locked'),
            ]);
        }
        RateLimiter::hit($rateKey, self::VERIFY_SECONDS);

        $token = Str::random(64);
        $code = $this->code();
        $this->send($purpose, $email, $code, $userId !== null);
        DB::table('api_otp_challenges')->insert([
            'token_hash' => hash('sha256', $token),
            'purpose'    => $purpose,
            'email'      => $email,
            'user_id'    => $userId,
            'code_hash'  => $userId !== null || $purpose === 'registration' ? Hash::make($code) : null,
            'expires_at' => now()->addSeconds(self::OTP_SECONDS),
            'resend_at'  => now()->addSeconds(self::RESEND_SECONDS),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $token;
    }

    private function find(string $token, string $purpose): object
    {
        $challenge = DB::table('api_otp_challenges')
            ->where('token_hash', hash('sha256', $token))
            ->where('purpose', $purpose)
            ->lockForUpdate()->first();
        if ($challenge === null || $challenge->consumed_at !== null) {
            throw ValidationException::withMessages([
                'challenge' => __('Auth::app.registration_flow.errors.invalid_session'),
            ]);
        }

        return $challenge;
    }

    private function verified(string $token, string $purpose): object
    {
        $challenge = $this->find($token, $purpose);
        if ($challenge->verified_until === null || $challenge->verified_until <= now()) {
            throw ValidationException::withMessages([
                'challenge' => __('Auth::app.registration_flow.errors.invalid_session'),
            ]);
        }

        return $challenge;
    }

    private function consume(int $id): void
    {
        DB::table('api_otp_challenges')->where('id', $id)
            ->update(['consumed_at' => now(), 'updated_at' => now()]);
    }

    private function send(string $purpose, string $email, string $code, bool $knownAccount): void
    {
        if ($purpose === 'recovery' && ! $knownAccount) {
            return;
        }
        try {
            $mail = $purpose === 'registration'
                ? new RegistrationOtpMail($code)
                : new PasswordResetOtpMail($code);
            Mail::to($email)->send($mail);
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'email' => __('Auth::app.registration_flow.errors.mail_failed'),
            ]);
        }
    }

    private function code(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function errors(string $purpose): string
    {
        return $purpose === 'registration'
            ? 'Auth::app.registration_flow.errors'
            : 'Auth::app.password_recovery.errors';
    }
}
