<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers\Api;

use FuteBus\Profile\Http\Requests\UpdateProfileRequest;
use FuteBus\Profile\Services\ProfileService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->account($request)]);
    }

    public function update(UpdateProfileRequest $request, ProfileService $profiles): JsonResponse
    {
        $profiles->update($request->user(), $request->safe()->except('avatar'), $request->file('avatar'));

        return response()->json(['data' => $this->account($request)]);
    }

    public function avatar(Request $request): StreamedResponse
    {
        $path = $request->user()->avatar;
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        abort_if(! is_string($path) || ! str_starts_with($path, 'avatars/') || ! $disk->exists($path), 404);

        return $disk->response($path);
    }

    private function account(Request $request): array
    {
        $user = $request->user()->refresh();

        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'gender'        => $user->gender,
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'address'       => $user->address,
            'occupation'    => $user->occupation,
            'has_avatar'    => $user->avatar !== null,
        ];
    }
}
