<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * GET /api/v1/settings
     * Trả về toàn bộ settings cần thiết cho trang settings
     * (UI settings lưu localStorage, chỉ cần trả email config từ DB)
     */
    public function index(): JsonResponse
    {
        $emailSetting = EmailSetting::first();

        return response()->json([
            'email' => $emailSetting ? [
                'mail_host'         => $emailSetting->mail_host,
                'mail_port'         => $emailSetting->mail_port,
                'mail_username'     => $emailSetting->mail_username,
                'mail_encryption'   => $emailSetting->mail_encryption,
                'mail_from_address' => $emailSetting->mail_from_address,
                'mail_from_name'    => $emailSetting->mail_from_name,
                'is_active'         => $emailSetting->is_active,
            ] : null,
        ]);
    }
}