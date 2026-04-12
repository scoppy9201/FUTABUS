<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    /**
     * Đổi mật khẩu
     * POST /api/v1/password/change
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                'regex:/^\S*$/',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'password.required'         => 'Vui lòng nhập mật khẩu mới',
            'password.confirmed'        => 'Xác nhận mật khẩu không khớp',
            'password.different'        => 'Mật khẩu mới phải khác mật khẩu hiện tại',
            'password.regex'            => 'Mật khẩu không được chứa khoảng trắng',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không đúng.',
                'errors'  => [
                    'current_password' => ['Mật khẩu hiện tại không đúng.'],
                ],
            ], 422);
        }

        // Cập nhật mật khẩu
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Đổi mật khẩu thành công!',
        ], 200);
    }
}