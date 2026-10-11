<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use FuteBus\Auth\Mail\PasswordChangedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_authenticated_users_can_change_their_password(): void
    {
        $this->get(route('profile.password.edit'))->assertRedirect(route('login'));
        $this->put(route('profile.password.update'), [])->assertRedirect(route('login'));
    }

    public function test_password_page_shows_email_and_navigation_links(): void
    {
        $user = $this->createUser(['email' => 'customer@example.com']);

        $this->actingAs($user)->get(route('profile.password.edit'))
            ->assertOk()
            ->assertSee('customer@example.com')
            ->assertSee('Để bảo mật tài khoản')
            ->assertSee('Nhập mật khẩu cũ')
            ->assertDontSee('Profile::app.password_change')
            ->assertSee('action="'.route('profile.password.update').'"', false)
            ->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('autocomplete="new-password"', false)
            ->assertSee('novalidate', false)
            ->assertSee('validateForSubmit($event)', false)
            ->assertSee('aria-current="page"', false)
            ->assertDontSee($user->phone);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('href="'.route('profile.password.edit').'"', false);
    }

    public function test_wrong_current_password_and_invalid_new_password_are_rejected(): void
    {
        Mail::fake();
        $user = $this->createUser(['password' => 'OldPassword123']);
        $this->actingAs($user);

        $this->put(route('profile.password.update'), [
            'current_password'      => 'WrongPassword123',
            'password'              => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('profile.password.update'), [
            'current_password'      => 'OldPassword123',
            'password'              => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->put(route('profile.password.update'), [
            'current_password'      => 'OldPassword123',
            'password'              => 'OldPassword123',
            'password_confirmation' => 'OldPassword123',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
        Mail::assertNothingSent();
    }

    public function test_empty_new_password_fields_have_the_same_clear_error(): void
    {
        $user = $this->createUser(['password' => 'OldPassword123']);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password'      => '',
            'password'              => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors([
            'current_password'      => 'Vui lòng nhập mật khẩu cũ.',
            'password'              => 'Vui lòng nhập mật khẩu.',
            'password_confirmation' => 'Vui lòng nhập mật khẩu.',
        ]);
    }

    public function test_wrong_current_password_shows_a_clear_message_below_its_field(): void
    {
        $user = $this->createUser(['password' => 'OldPassword123']);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password'      => 'WrongPassword123',
            'password'              => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertSessionHasErrors(['current_password' => 'Mật khẩu cũ không đúng.']);

        $this->get(route('profile.password.edit'))
            ->assertOk()
            ->assertSee('Mật khẩu cũ không đúng.')
            ->assertSee('border-red-500', false);

        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    public function test_both_new_password_fields_require_mixed_case_and_a_number(): void
    {
        Mail::fake();
        $user = $this->createUser(['password' => 'OldPassword123']);
        $this->actingAs($user);

        foreach (['lowercase12', 'UPPERCASE12', 'NoNumbersHere'] as $weakPassword) {
            $this->put(route('profile.password.update'), [
                'current_password'      => 'OldPassword123',
                'password'              => $weakPassword,
                'password_confirmation' => $weakPassword,
            ])->assertSessionHasErrors(['password', 'password_confirmation']);
        }

        $this->put(route('profile.password.update'), [
            'current_password'      => 'OldPassword123',
            'password'              => 'NewPassword123',
            'password_confirmation' => 'Different123',
        ])->assertSessionHasErrors('password_confirmation');

        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
        Mail::assertNothingSent();
    }

    public function test_valid_change_hashes_password_and_emails_the_account_owner(): void
    {
        Mail::fake();
        $user = $this->createUser([
            'email'          => 'customer@example.com',
            'password'       => 'OldPassword123',
            'remember_token' => 'old-remember-token',
        ]);
        $user->createToken('old-phone', ['api']);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password'      => 'OldPassword123',
            'password'              => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('profile.password.edit'))->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
        $this->assertFalse(Hash::check('OldPassword123', $user->password));
        $this->assertNotSame('old-remember-token', $user->remember_token);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertAuthenticatedAs($user);
        Mail::assertSent(PasswordChangedMail::class, fn (PasswordChangedMail $mail) => $mail->hasTo('customer@example.com'));
    }

    public function test_mail_failure_does_not_undo_a_successful_password_change(): void
    {
        $user = $this->createUser(['email' => 'customer@example.com', 'password' => 'OldPassword123']);
        Mail::shouldReceive('to')->once()->with('customer@example.com')->andThrow(new RuntimeException('SMTP unavailable'));

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password'      => 'OldPassword123',
            'password'              => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('profile.password.edit'))->assertSessionHas('warning');

        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    private function createUser(array $attributes = []): User
    {
        /** @var User $user */
        $user = $this->createTestUser($attributes);

        return $user;
    }
}
