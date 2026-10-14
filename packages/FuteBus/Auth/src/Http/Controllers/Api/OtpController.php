<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Controllers\Api;

use FuteBus\Auth\Http\Requests\CompleteOtpRegistrationRequest;
use FuteBus\Auth\Http\Requests\ResendOtpRequest;
use FuteBus\Auth\Http\Requests\ResetOtpPasswordRequest;
use FuteBus\Auth\Http\Requests\StartRecoveryRequest;
use FuteBus\Auth\Http\Requests\StartRegistrationRequest;
use FuteBus\Auth\Http\Requests\VerifyOtpRequest;
use FuteBus\Auth\Services\ApiOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class OtpController extends Controller
{
    public function __construct(private readonly ApiOtpService $otp) {}

    public function startRegistration(StartRegistrationRequest $request): JsonResponse
    {
        $data = $request->validated();

        return $this->challenge($this->otp->startRegistration($data['email']), 201);
    }

    public function startRecovery(StartRecoveryRequest $request): JsonResponse
    {
        $data = $request->validated();

        return $this->challenge($this->otp->startRecovery($data['email']), 202);
    }

    public function resendRegistration(ResendOtpRequest $request): JsonResponse
    {
        return $this->resend($request, 'registration');
    }

    public function resendRecovery(ResendOtpRequest $request): JsonResponse
    {
        return $this->resend($request, 'recovery');
    }

    public function verifyRegistration(VerifyOtpRequest $request): JsonResponse
    {
        return $this->verify($request, 'registration');
    }

    public function verifyRecovery(VerifyOtpRequest $request): JsonResponse
    {
        return $this->verify($request, 'recovery');
    }

    public function register(CompleteOtpRegistrationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->otp->completeRegistration(
            $data['challenge'], $data['name'], $data['phone'], $data['password']
        );

        return response()->json(['id' => $user->getKey(), 'email' => $user->email], 201)
            ->header('Cache-Control', 'no-store');
    }

    public function resetPassword(ResetOtpPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->otp->resetPassword($data['challenge'], $data['password']);

        return response()->json(status: 204)->header('Cache-Control', 'no-store');
    }

    private function resend(ResendOtpRequest $request, string $purpose): JsonResponse
    {
        $data = $request->validated();
        $this->otp->resend($data['challenge'], $purpose);

        return response()->json(status: 204)->header('Cache-Control', 'no-store');
    }

    private function verify(VerifyOtpRequest $request, string $purpose): JsonResponse
    {
        $data = $request->validated();
        $this->otp->verify($data['challenge'], $purpose, (string) $data['otp']);

        return response()->json(status: 204)->header('Cache-Control', 'no-store');
    }

    private function challenge(string $token, int $status): JsonResponse
    {
        return response()->json(['challenge' => $token, 'expires_in' => 300], $status)
            ->header('Cache-Control', 'no-store');
    }
}
