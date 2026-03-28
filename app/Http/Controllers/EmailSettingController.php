<?php

namespace App\Http\Controllers;

use App\Models\EmailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

class EmailSettingController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'mail_host'         => 'required|string|max:255',
            'mail_port'         => 'required|integer',
            'mail_username'     => 'required|string|max:255',
            'mail_password'     => 'nullable|string|max:255',
            'mail_encryption'   => 'required|in:tls,ssl,starttls',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name'    => 'required|string|max:255',
            'is_active'         => 'sometimes|boolean',
        ]);

        $setting = EmailSetting::first() ?? new EmailSetting();

        // Nếu password rỗng, giữ password cũ
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $setting->fill($validated)->save();

        return back()->with('email_success', 'Đã lưu cấu hình email thành công!');
    }

    public function testMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $setting = EmailSetting::first();
        if (!$setting) {
            return back()->with('email_error', 'Chưa có cấu hình email.');
        }

        // Override config tạm thời để test
        $this->applyConfig($setting);

        try {
            Mail::raw('Email test từ Monexa — cấu hình hoạt động!', function ($message) use ($request, $setting) {
                $message->to($request->test_email)
                    ->from($setting->mail_from_address, $setting->mail_from_name)
                    ->subject('Test Email - Monexa');
            });

            return back()->with('email_success', "Đã gửi email test đến {$request->test_email}!");
        } catch (\Exception $e) {
            return back()->with('email_error', 'Gửi thất bại: ' . $e->getMessage());
        }
    }

    private function applyConfig(EmailSetting $s): void
    {
        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $s->mail_host,
            'port'       => $s->mail_port,
            'username'   => $s->mail_username,
            'password'   => $s->mail_password,
            'encryption' => $s->mail_encryption,
        ]);
        Config::set('mail.from.address', $s->mail_from_address);
        Config::set('mail.from.name',    $s->mail_from_name);
        Config::set('mail.default',      'smtp');
    }
}
