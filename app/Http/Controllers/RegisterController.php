<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Xử lý đăng ký tài khoản mới
     * POST /api/v1/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[A-Za-z0-9._%+-]+@gmail\.com$/i',
            ],
            'password' => [
                'required',
                'confirmed',
                'regex:/^\S*$/',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'name.required'      => 'Vui lòng nhập họ tên',
            'name.min'           => 'Họ tên phải có ít nhất 2 ký tự',
            'name.max'           => 'Họ tên không được quá 255 ký tự',
            'email.required'     => 'Vui lòng nhập email',
            'email.email'        => 'Email không hợp lệ',
            'email.unique'       => 'Email này đã được sử dụng',
            'email.regex'        => 'Chỉ chấp nhận email từ miền gmail.com',
            'password.required'  => 'Vui lòng nhập mật khẩu',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.regex'     => 'Mật khẩu không được chứa khoảng trắng',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Sanctum stateful: login vào session, không trả token
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Đăng ký tài khoản thành công! Chào mừng bạn đến với Monexa.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }
}