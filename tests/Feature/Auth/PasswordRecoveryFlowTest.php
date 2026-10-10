<?php

namespace Tests\Feature\Auth;

use FuteBus\Auth\Mail\PasswordChangedMail;
use FuteBus\Auth\Mail\PasswordResetOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordRecoveryFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function requestCode(string $email = 'customer@example.com'): string
    {
        $this->post(route('password.email'), ['email' => $email])->assertRedirect(route('password.request'));
        $this->get(route('password.request'))->assertOk()->assertSee('data-otp-input', false);

        $code = '';
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        return $code;
    }

    public function test_customer_can_reset_password_after_email_otp_and_log_in(): void
    {
        $user = $this->createTestUser(['email' => 'customer@example.com']);
        $oldHash = $user->password;
        $code = $this->requestCode();

        $this->post(route('password.email.verify'), ['otp' => $code])->assertRedirect(route('password.request'));
        $this->get(route('password.request'))->assertSeeText('Đặt mật khẩu mới');
        $this->post(route('password.update'), [
            'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('login'));

        $user->refresh();
        $this->assertNotSame($oldHash, $user->password);
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
        Mail::assertSent(PasswordChangedMail::class);
        $this->post(route('password.update'), [
            'password' => 'ReplayPassword123', 'password_confirmation' => 'ReplayPassword123',
        ])->assertSessionHasErrors('recovery');
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'NewPassword123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_unknown_email_does_not_send_mail_or_reveal_account(): void
    {
        $this->post(route('password.email'), ['email' => 'missing@example.com'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');
        $this->get(route('password.request'))->assertSee('name="email"', false);
        Mail::assertNothingSent();
    }

    public function test_five_wrong_codes_lock_recovery_and_resend(): void
    {
        $this->createTestUser(['email' => 'customer@example.com']);
        $code = $this->requestCode();
        $wrong = $code === '000000' ? '111111' : '000000';
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('password.email.verify'), ['otp' => $wrong])->assertSessionHasErrors('otp');
        }

        $this->post(route('password.email.verify'), ['otp' => $code])->assertSessionHasErrors('otp');
        $this->travel(61)->seconds();
        $this->post(route('password.email.resend'))->assertSessionHasErrors('otp');
    }

    public function test_expired_code_and_expired_verified_session_cannot_reset_password(): void
    {
        $user = $this->createTestUser(['email' => 'customer@example.com']);
        $code = $this->requestCode();
        $this->travel(301)->seconds();
        $this->post(route('password.email.verify'), ['otp' => $code])->assertSessionHasErrors('otp');

        $this->post(route('password.email.resend'))->assertRedirect(route('password.request'));
        $newCode = '';
        Mail::assertSent(PasswordResetOtpMail::class, function (PasswordResetOtpMail $mail) use (&$newCode): bool {
            $newCode = $mail->code;

            return true;
        });
        $this->post(route('password.email.verify'), ['otp' => $newCode])->assertRedirect(route('password.request'));
        $this->travel(901)->seconds();
        $this->post(route('password.update'), [
            'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasErrors('password');
        $this->assertFalse(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_password_cannot_be_reset_before_code_verification(): void
    {
        $this->createTestUser(['email' => 'customer@example.com']);
        $this->requestCode();
        $this->post(route('password.update'), [
            'password' => 'NewPassword123', 'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasErrors('recovery');
    }

    public function test_recovery_email_and_validation_follow_the_selected_language(): void
    {
        $this->get('/quen-mat-khau?lang=en')->assertOk();
        $this->post(route('password.email'), ['email' => 'invalid'])
            ->assertSessionHasErrors(['email' => 'Invalid email address. Please try again.']);

        $mail = new PasswordResetOtpMail('123456');
        $this->assertSame('FUTA Bus Lines password recovery code', $mail->envelope()->subject);
        $this->assertStringContainsString('Your verification code to reset your password is 123456.', $mail->render());
    }
}
