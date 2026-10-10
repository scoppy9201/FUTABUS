<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    public function test_login_page_uses_the_vietnamese_url(): void
    {
        $this->get('/dang-nhap?lang=vi')
            ->assertOk()
            ->assertSeeText('Đăng nhập tài khoản')
            ->assertSee('name="email"', false)
            ->assertSee('data-auth-email-input', false)
            ->assertSee('id="auth-email-error"', false)
            ->assertSee('name="password"', false);
    }

    public function test_register_page_uses_the_vietnamese_url(): void
    {
        $this->get('/dang-ky?lang=vi')
            ->assertOk()
            ->assertSeeText('Tạo tài khoản')
            ->assertSee('name="email"', false)
            ->assertSee('data-auth-email-input', false)
            ->assertSee('id="auth-email-error"', false)
            ->assertSee('name="terms"', false)
            ->assertSee(route('register.email'), false);
    }

    public function test_legacy_english_auth_urls_are_not_registered(): void
    {
        $this->get('/login')->assertNotFound();
        $this->get('/register')->assertNotFound();
    }

    public function test_forgot_password_page_uses_the_vietnamese_url(): void
    {
        $this->get('/quen-mat-khau?lang=vi')
            ->assertOk()
            ->assertSeeText('Quên mật khẩu')
            ->assertSeeText('Gửi mã xác thực')
            ->assertSee('name="email"', false)
            ->assertSee(route('password.email'), false);
    }
}
