<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $this->checkTooManyFailedAttempts($request);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($this->throttleKey($request), 60);

            return response()->json([
                'message' => 'Email hoac mat khau khong dung.',
            ], 401);
        }

        RateLimiter::clear($this->throttleKey($request));

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Dang nhap thanh cong!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 200);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

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
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(16)),
            ]);
        } else {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect('/dashboard?token=' . $token);
    }

    public function logout(Request $request)
    {
        if ($request->user()?->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        if (Auth::check()) {
            Auth::logout();
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Da dang xuat thanh cong!',
            ], 200);
        }

        return redirect()->route('login');
    }

    protected function checkTooManyFailedAttempts(Request $request)
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        abort(response()->json([
            'message' => "Qua nhieu lan dang nhap sai. Vui long thu lai sau {$seconds} giay.",
        ], 429));
    }

    protected function throttleKey(Request $request)
    {
        return Str::transliterate(
            Str::lower($request->input('email')) . '|' . $request->ip()
        );
    }
}
