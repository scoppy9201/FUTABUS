<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use FuteBus\Auth\Mail\PasswordResetOtpMail;
use FuteBus\Auth\Mail\RegistrationOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_registration_requires_verified_otp_and_challenge_is_single_use(): void
    {
        $token = $this->postJson(route('api.v1.registration-challenges.store'), [
            'email' => 'NEW@example.com', 'terms' => true,
        ])->assertCreated()->json('challenge');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
        $code = '';
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        $payload = [
            'challenge'             => $token,
            'name'                  => 'Nguyen Van A',
            'phone'                 => '0912345678',
            'password'              => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ];
        $this->postJson(route('api.v1.registrations.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('challenge');
        $this->postJson(route('api.v1.registration-challenges.verify'), [
            'challenge' => $token, 'otp' => $code,
        ])->assertNoContent();
        $this->postJson(route('api.v1.registrations.store'), $payload)->assertCreated()
            ->assertJsonPath('email', 'new@example.com');
        $this->postJson(route('api.v1.registrations.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('challenge');
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('+84912345678', $user->phone);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('StrongPass123', $user->password));
    }

    public function test_wrong_otp_locks_challenge_after_five_attempts(): void
    {
        $token = $this->postJson(route('api.v1.registration-challenges.store'), [
            'email' => 'another@example.com', 'terms' => true,
        ])->assertCreated()->json('challenge');
        $code = '';
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson(route('api.v1.registration-challenges.verify'), [
                'challenge' => $token, 'otp' => $wrong,
            ])->assertUnprocessable()->assertJsonValidationErrors('otp');
        }
        $this->postJson(route('api.v1.registration-challenges.verify'), [
            'challenge' => $token, 'otp' => $code,
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');
    }

    public function test_resend_rotates_code_and_expired_challenge_cannot_be_verified(): void
    {
        $token = $this->postJson(route('api.v1.registration-challenges.store'), [
            'email' => 'rotate@example.com', 'terms' => true,
        ])->assertCreated()->json('challenge');
        $firstCode = '';
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$firstCode): bool {
            $firstCode = $mail->code;

            return true;
        });
        $this->postJson(route('api.v1.registration-challenges.resend'), [
            'challenge' => $token,
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');

        $this->travel(61)->seconds();
        $this->postJson(route('api.v1.registration-challenges.resend'), [
            'challenge' => $token,
        ])->assertNoContent();
        $this->postJson(route('api.v1.registration-challenges.verify'), [
            'challenge' => $token, 'otp' => $firstCode,
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');

        $this->travel(301)->seconds();
        $this->postJson(route('api.v1.registration-challenges.verify'), [
            'challenge' => $token, 'otp' => $firstCode,
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');
        $this->postJson(route('api.v1.registration-challenges.resend'), [
            'challenge' => $token,
        ])->assertUnprocessable()->assertJsonValidationErrors('otp');
    }

    public function test_recovery_does_not_reveal_unknown_accounts_and_revokes_tokens(): void
    {
        $user = User::factory()->create(['email' => 'known@example.com']);
        $user->createToken('before-reset');
        $known = $this->postJson(route('api.v1.password-recovery-challenges.store'), [
            'email' => $user->email,
        ])->assertAccepted()->json('challenge');
        $unknown = $this->postJson(route('api.v1.password-recovery-challenges.store'), [
            'email' => 'missing@example.com',
        ])->assertAccepted()->json('challenge');
        $this->assertSame(strlen($known), strlen($unknown));
        Mail::assertSentCount(1);

        $code = '';
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });
        $this->postJson(route('api.v1.password-recovery-challenges.verify'), [
            'challenge' => $known, 'otp' => $code,
        ])->assertNoContent();
        $payload = [
            'challenge'             => $known,
            'password'              => 'NewStrongPass123',
            'password_confirmation' => 'NewStrongPass123',
        ];
        $this->postJson(route('api.v1.password-resets.store'), $payload)->assertNoContent();
        $this->postJson(route('api.v1.password-resets.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('challenge');
        $this->assertTrue(Hash::check('NewStrongPass123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->postJson(route('api.v1.password-recovery-challenges.verify'), [
            'challenge' => $unknown, 'otp' => $code,
        ])->assertUnprocessable();
    }
}
