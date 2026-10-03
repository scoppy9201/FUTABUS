<?php

declare(strict_types=1);

namespace FuteBus\Profile\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileService
{
    public function update(User $user, array $details, ?UploadedFile $avatar): void
    {
        $oldAvatar = $user->avatar;
        try {
            $newAvatar = $avatar?->store('avatars', 'public');
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['avatar' => __('Profile::app.validation.avatar_store_failed')]);
        }

        if ($newAvatar === false) {
            throw ValidationException::withMessages(['avatar' => __('Profile::app.validation.avatar_store_failed')]);
        }

        try {
            $user->fill($details);

            if ($newAvatar !== null) {
                $user->avatar = $newAvatar;
            }

            $user->save();
        } catch (Throwable $exception) {
            if ($newAvatar !== null) {
                Storage::disk('public')->delete($newAvatar);
            }

            throw $exception;
        }

        if ($newAvatar !== null && is_string($oldAvatar) && str_starts_with($oldAvatar, 'avatars/')) {
            Storage::disk('public')->delete($oldAvatar);
        }
    }
}
