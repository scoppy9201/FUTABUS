<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Controllers\Api;

use FuteBus\Auth\Services\ApiOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OtpController extends Controller
{
    public function __construct(private readonly ApiOtpService $otp) {}

    public function startRegistration(Request $request): JsonResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'terms' => ['accepted'],
        ]);

        return $this->challenge($this->otp->startRegistration($data['email']), 201);
    }

    public function startRecovery(Request $request): JsonResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:255']]);

        return $this->challenge($this->otp->startRecovery($data['email']), 202);
    }

    public function resendRegistration(Request $request): JsonResponse
    {
        return $this->resend($request, 'registration');
    }

    public function resendRecovery(Request $request): JsonResponse
    {
        return $this->resend($request, 'recovery');
    }

    public function verifyRegistration(Request $request): JsonResponse
    {
        return $this->verify($request, 'registration');
    }

    public function verifyRecovery(Request $request): JsonResponse
    {
        return $this->verify($request, 'recovery');
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge' => ['required', 'string', 'size:64'],
            'name'      => ['required', 'string', 'min:2', 'max:255'],
            'phone'     => ['required', 'regex:/^(0|\+84)[0-9]{9}$/'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = $this->otp->completeRegistration(
            $data['challenge'], $data['name'], $data['phone'], $data['password']
        );

        return response()->json(['id' => $user->getKey(), 'email' => $user->email], 201)
            ->header('Cache-Control', 'no-store');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge' => ['required', 'string', 'size:64'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $this->otp->resetPassword($data['challenge'], $data['password']);

        return response()->json(status: 204)->header('Cache-Control', 'no-store');
    }

    private function resend(Request $request, string $purpose): JsonResponse
    {
        $data = $request->validate(['challenge' => ['required', 'string', 'size:64']]);
        $this->otp->resend($data['challenge'], $purpose);

        return response()->json(status: 204)->header('Cache-Control', 'no-store');
    }

    private function verify(Request $request, string $purpose): JsonResponse
    {
        $data = $request->validate([
            'challenge' => ['required', 'string', 'size:64'],
            'otp'       => ['required', 'digits:6'],
        ]);
        $this->otp->verify($data['challenge'], $purpose, (string) $data['otp']);

        return response()->json(status: 204)->header('Cache-Control', 'no-store');
    }

    private function challenge(string $token, int $status): JsonResponse
    {
        return response()->json(['challenge' => $token, 'expires_in' => 300], $status)
            ->header('Cache-Control', 'no-store');
    }
}
