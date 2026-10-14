<?php

declare(strict_types=1);

namespace FuteBus\Auth\Http\Controllers\Api;

use App\Models\User;
use FuteBus\Auth\Http\Requests\IssueTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    public function store(IssueTokenRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()
            ->where('email', mb_strtolower(trim($credentials['email'])))
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('Auth::app.registration_flow.errors.invalid_credentials'),
            ]);
        }

        if ($user->email_verified_at === null) {
            throw ValidationException::withMessages([
                'email' => __('Auth::app.registration_flow.errors.email_unverified'),
            ]);
        }

        $token = $user->createToken($credentials['device_name'], ['api'], now()->addDays(30));

        return response()->json([
            'token_type'   => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at'   => $token->accessToken->expires_at?->toIso8601String(),
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        abort_unless($token instanceof PersonalAccessToken, 401);

        $token->delete();

        return response()->json(status: 204);
    }
}
