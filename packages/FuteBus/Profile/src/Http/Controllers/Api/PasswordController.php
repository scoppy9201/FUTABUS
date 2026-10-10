<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers\Api;

use FuteBus\Profile\Http\Requests\ChangeApiPasswordRequest;
use FuteBus\Profile\Services\PasswordChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PasswordController extends Controller
{
    public function update(ChangeApiPasswordRequest $request, PasswordChangeService $passwords): JsonResponse
    {
        $notified = $passwords->change($request->user(), $request->validated('password'));

        return response()->json([
            'message'           => __($notified ? 'Profile::app.password_change.updated' : 'Profile::app.password_change.mail_failed'),
            'notification_sent' => $notified,
        ])->header('Cache-Control', 'no-store');
    }
}
