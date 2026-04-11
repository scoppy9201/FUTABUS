<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * POST /api/v1/auth/password/forgot
     * Gửi mã xác thực 6 số về email
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email'    => 'Email không đúng định dạng.',
            'email.exists'   => 'Email không tồn tại trong hệ thống.',
        ]);

        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $code, 'created_at' => Carbon::now()]
        );

        Mail::send('auth.reset-code', ['code' => $code], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Mã xác thực đặt lại mật khẩu - Monexa');
        });

        return response()->json([
            'success' => true,
            'message' => 'Mã xác thực đã được gửi đến email của bạn.',
        ]);
    }

    /**
     * POST /api/v1/auth/password/verify
     * Xác thực mã 6 số
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|digits:6',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.exists'   => 'Email không tồn tại trong hệ thống.',
            'code.required'  => 'Vui lòng nhập mã xác thực.',
            'code.digits'    => 'Mã xác thực phải là 6 chữ số.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực không đúng.',
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(3)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực đã hết hạn. Vui lòng yêu cầu mã mới.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Xác thực thành công.',
        ]);
    }

    /**
     * POST /api/v1/auth/password/reset
     * Đặt lại mật khẩu mới
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'code'                  => 'required|digits:6',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'email.required'    => 'Vui lòng nhập email.',
            'email.exists'      => 'Email không tồn tại trong hệ thống.',
            'code.required'     => 'Vui lòng nhập mã xác thực.',
            'code.digits'       => 'Mã xác thực phải là 6 chữ số.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min'      => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed'=> 'Xác nhận mật khẩu không khớp.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực không hợp lệ. Vui lòng thực hiện lại từ đầu.',
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(3)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực đã hết hạn. Vui lòng yêu cầu mã mới.',
            ], 422);
        }

        User::where('email', $request->email)->update([
            'password' => bcrypt($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.',
        ]);
    }
}