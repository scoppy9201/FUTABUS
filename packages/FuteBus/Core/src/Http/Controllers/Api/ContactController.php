<?php

declare(strict_types=1);

namespace FuteBus\Core\Http\Controllers\Api;

use FuteBus\Core\Http\Requests\ContactRequest;
use FuteBus\Core\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        $message = ContactMessage::create($request->validated());

        return response()->json(['data' => ['id' => $message->id]], 201);
    }
}
