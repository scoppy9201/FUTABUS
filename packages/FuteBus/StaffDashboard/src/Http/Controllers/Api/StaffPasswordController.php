<?php

declare(strict_types=1);

namespace FuteBus\StaffDashboard\Http\Controllers\Api;

use FuteBus\Profile\Http\Controllers\Api\PasswordController;
use FuteBus\Profile\Services\PasswordChangeService;
use FuteBus\StaffDashboard\Http\Requests\ChangeStaffPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class StaffPasswordController extends Controller
{
    public function update(ChangeStaffPasswordRequest $request, PasswordChangeService $passwords, PasswordController $controller): JsonResponse
    {
        return $controller->update($request, $passwords);
    }
}
