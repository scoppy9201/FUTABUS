<?php

declare(strict_types=1);

namespace FuteBus\Profile\Services;

use App\Models\User;
use FuteBus\Auth\Mail\PasswordChangedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PasswordChangeService
{
    public function change(User $user, string $password): bool
    {
        $user->password = $password;
        $user->remember_token = Str::random(60);
        $user->save();
        $user->tokens()->delete();

        try {
            Mail::to($user->email)->send(new PasswordChangedMail);
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }

        return true;
    }
}
