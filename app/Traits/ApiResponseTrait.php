<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'OK',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(
        string $message = 'Error',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
