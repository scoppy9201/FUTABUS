<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Controllers\Api;

use FuteBus\Auth\Services\PasswordChangeService;
use FuteBus\StaffDashboard\Http\Requests\ChangeStaffPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class StaffPasswordController extends Controller
{
    public function update(ChangeStaffPasswordRequest $request, PasswordChangeService $passwords): JsonResponse
    {
        $notified = $passwords->change($request->user(), $request->validated('password'));

        return response()->json([
            'message'           => __($notified ? 'Profile::app.password_change.updated' : 'Profile::app.password_change.mail_failed'),
            'notification_sent' => $notified,
        ])->header('Cache-Control', 'no-store');
    }
}
