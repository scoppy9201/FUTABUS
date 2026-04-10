<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Đăng nhập bằng email + password
     * POST /api/monaxe/auth/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Chống brute force
        $this->checkTooManyFailedAttempts($request);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($this->throttleKey($request), 60);

            return response()->json([
                'message' => 'Email hoặc mật khẩu không đúng.',
            ], 401);
        }

        RateLimiter::clear($this->throttleKey($request));

        $user  = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Đăng nhập thành công!',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ], 200);
    }

    /**
     * Redirect sang Google login
     * GET /api/monaxe/auth/google
     */
    public function redirectToGoogle()
    {
        // Redirect thẳng - chấp nhận được với OAuth2
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Nhận callback từ Google
     * GET /api/monaxe/auth/google/callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect('/login?error=google_failed');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
                'password'  => Hash::make(Str::random(16)),
            ]);
        } else {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Redirect về dashboard kèm token
        return redirect('/dashboard?token=' . $token);
    }
    /**
     * Đăng xuất
     * POST /api/monaxe/auth/logout
     */
    public function logout(Request $request)
    {
        // Xóa token hiện tại
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Đã đăng xuất thành công!',
        ], 200);
    }

    /**
     * Chống brute force
     */
    protected function checkTooManyFailedAttempts(Request $request)
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        return response()->json([
            'message' => "Quá nhiều lần đăng nhập sai. Vui lòng thử lại sau {$seconds} giây.",
        ], 429);
    }

    protected function throttleKey(Request $request)
    {
        return Str::transliterate(
            Str::lower($request->input('email')).'|'.$request->ip()
        );
    }
}

