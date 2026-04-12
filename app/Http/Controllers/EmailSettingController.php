<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class EmailSettingController extends Controller
{
    /**
     * GET /api/v1/settings/email
     * Lấy cấu hình email hiện tại
     */
    public function show(): JsonResponse
    {
        $setting = EmailSetting::first();

        return response()->json([
            'setting' => $setting ? [
                'mail_host'         => $setting->mail_host,
                'mail_port'         => $setting->mail_port,
                'mail_username'     => $setting->mail_username,
                'mail_encryption'   => $setting->mail_encryption,
                'mail_from_address' => $setting->mail_from_address,
                'mail_from_name'    => $setting->mail_from_name,
                'is_active'         => $setting->is_active,
            ] : null,
        ]);
    }

    /**
     * PATCH /api/v1/settings/email
     * Lưu cấu hình email
     */
    public function update(Request $request): JsonResponse
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
        ], [
            'mail_host.required'         => 'Vui lòng nhập SMTP Host',
            'mail_port.required'         => 'Vui lòng nhập Port',
            'mail_username.required'     => 'Vui lòng nhập Email/Username',
            'mail_encryption.required'   => 'Vui lòng chọn mã hóa',
            'mail_encryption.in'         => 'Mã hóa không hợp lệ',
            'mail_from_address.required' => 'Vui lòng nhập email gửi đi',
            'mail_from_address.email'    => 'Email gửi đi không hợp lệ',
            'mail_from_name.required'    => 'Vui lòng nhập tên hiển thị',
        ]);

        $setting = EmailSetting::first() ?? new EmailSetting();

        // Giữ password cũ nếu không nhập mới
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $setting->fill($validated)->save();

        return response()->json([
            'message' => 'Đã lưu cấu hình email thành công!',
            'setting' => $setting->fresh(),
        ]);
    }

    /**
     * POST /api/v1/settings/email/test
     * Gửi email test
     */
    public function testMail(Request $request): JsonResponse
    {
        $request->validate([
            'test_email' => 'required|email',
        ], [
            'test_email.required' => 'Vui lòng nhập email nhận test',
            'test_email.email'    => 'Email không hợp lệ',
        ]);

        $setting = EmailSetting::first();
        if (!$setting) {
            return response()->json([
                'message' => 'Chưa có cấu hình email. Vui lòng lưu cấu hình trước!',
            ], 422);
        }

        $this->applyConfig($setting);

        try {
            Mail::raw('Email test từ Monexa — cấu hình hoạt động!', function ($message) use ($request, $setting) {
                $message->to($request->test_email)
                    ->from($setting->mail_from_address, $setting->mail_from_name)
                    ->subject('Test Email - Monexa');
            });

            return response()->json([
                'message' => "Đã gửi email test đến {$request->test_email} thành công!",
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gửi thất bại: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PATCH /api/v1/settings/email/toggle
     * Bật / tắt cấu hình email
     */
    public function toggle(): JsonResponse
    {
        $setting = EmailSetting::first();
        if (!$setting) {
            return response()->json(['message' => 'Chưa có cấu hình email!'], 422);
        }

        $setting->update(['is_active' => !$setting->is_active]);

        return response()->json([
            'message'   => $setting->is_active ? 'Đã bật cấu hình email!' : 'Đã tắt cấu hình email!',
            'is_active' => $setting->is_active,
        ]);
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