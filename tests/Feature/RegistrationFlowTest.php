<?php

namespace Tests\Feature;

use App\Models\User;
use FuteBus\Auth\Mail\AccountActivatedMail;
use FuteBus\Auth\Mail\RegistrationOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function requestCode(string $email = 'new@example.com'): string
    {
        $this->post(route('register.email'), ['email' => $email, 'terms' => '1'])
            ->assertRedirect(route('register'));
        $this->get(route('register'))->assertOk()->assertSee('data-otp-input', false);

        $code = '';
        Mail::assertSent(RegistrationOtpMail::class, function (RegistrationOtpMail $mail) use (&$code): bool {
            $code = $mail->code;
            return true;
        });

        return $code;
    }

    public function test_email_otp_registration_creates_a_verified_account_and_can_log_in(): void
    {
        $code = $this->requestCode();
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);

        $this->post(route('register.email.verify'), ['otp' => $code])->assertRedirect(route('register'));
        $this->get(route('register'))->assertSee('Đặt mật khẩu');
        $this->post(route('register.password'), [
            'password' => 'StrongPass123', 'password_confirmation' => 'StrongPass123',
        ])->assertRedirect(route('register'));
        $this->get(route('register'))->assertSee('Thông tin cá nhân');
        $this->post(route('register.profile'), [
            'name' => 'Nguyen Van A',
            'phone' => '0912345678',
        ])->assertRedirect(route('login'));

        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('+84912345678', $user->phone);
        $this->post(route('login.store'), ['email' => 'new@example.com', 'password' => 'StrongPass123'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        Mail::assertSent(RegistrationOtpMail::class);
        Mail::assertSent(AccountActivatedMail::class);
    }

    public function test_duplicate_email_and_phone_are_rejected(): void
    {
        User::factory()->create(['email' => 'used@example.com', 'phone' => '0912345678']);
        $this->post(route('register.email'), ['email' => 'used@example.com', 'terms' => '1'])
            ->assertSessionHasErrors('email');
        Mail::assertNothingSent();

        $code = $this->requestCode();
        $this->post(route('register.email.verify'), ['otp' => $code])->assertRedirect(route('register'));
        $this->post(route('register.password'), [
            'password' => 'StrongPass123', 'password_confirmation' => 'StrongPass123',
        ])->assertRedirect(route('register'));
        $this->post(route('register.profile'), [
            'name' => 'Nguyen Van A', 'phone' => '0912345678',
        ])->assertSessionHasErrors('phone');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_five_wrong_codes_lock_verification_and_resend(): void
    {
        $code = $this->requestCode();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('register.email.verify'), ['otp' => $code === '000000' ? '111111' : '000000'])
                ->assertSessionHasErrors('otp');
        }

        $this->assertSame(5, session('registration.attempts'));
        $this->assertGreaterThan(time(), session('registration.locked_until'));

        $this->post(route('register.email.verify'), ['otp' => $code])->assertSessionHasErrors('otp');
        $this->travel(61)->seconds();
        $this->post(route('register.email.resend'))->assertSessionHasErrors('otp');
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_unverified_email_cannot_log_in(): void
    {
        User::factory()->unverified()->create(['email' => 'pending@example.com', 'password' => 'StrongPass123']);
        $this->post(route('login.store'), ['email' => 'pending@example.com', 'password' => 'StrongPass123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_expired_code_requires_a_new_email_and_cannot_create_an_account(): void
    {
        $code = $this->requestCode();
        $this->travel(301)->seconds();

        $this->post(route('register.email.verify'), ['otp' => $code])->assertSessionHasErrors('otp');
        $this->post(route('register.email.resend'))->assertRedirect(route('register'));
        Mail::assertSentCount(2);
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_profile_cannot_be_submitted_before_email_and_password_steps(): void
    {
        $this->post(route('register.profile'), ['name' => 'Nguyen Van A', 'phone' => '0912345678'])
            ->assertSessionHasErrors('registration');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_templates_and_validation_use_the_selected_language(): void
    {
        $this->get('/dang-ky?lang=en')->assertOk();
        $this->post(route('register.email'), ['email' => 'invalid', 'terms' => '1'])
            ->assertSessionHasErrors(['email' => 'Invalid email address. Please try again.']);

        $otpMail = new RegistrationOtpMail('123456');
        $this->assertSame('FUTA Bus Lines registration verification code', $otpMail->envelope()->subject);
        $this->assertStringContainsString('Your FUTA Bus Lines registration verification code is 123456.', $otpMail->render());

        $activatedMail = new AccountActivatedMail;
        $this->assertSame('Your FUTA Bus Lines account is active', $activatedMail->envelope()->subject);
        $this->assertStringContainsString('You can sign in now.', $activatedMail->render());
    }

    public function test_vietnamese_and_english_auth_translations_have_matching_keys(): void
    {
        $vietnamese = include base_path('packages/FuteBus/Auth/src/resources/lang/vi/app.php');
        $english = include base_path('packages/FuteBus/Auth/src/resources/lang/en/app.php');

        $this->assertEqualsCanonicalizing(array_keys(Arr::dot($vietnamese)), array_keys(Arr::dot($english)));
    }
}
