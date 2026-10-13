<?php

declare(strict_types=1);

namespace Staff\Dashboard\Http\Controllers\Api;

use FuteBus\Profile\Http\Controllers\Api\PasswordController;
use FuteBus\Profile\Services\PasswordChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Staff\Dashboard\Http\Requests\ChangeStaffPasswordRequest;

class StaffPasswordController extends Controller
{
    public function update(ChangeStaffPasswordRequest $request, PasswordChangeService $passwords, PasswordController $controller): JsonResponse
    {
        return $controller->update($request, $passwords);
    }
}
