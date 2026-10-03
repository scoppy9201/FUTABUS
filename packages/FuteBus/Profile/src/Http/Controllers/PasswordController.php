<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers;

use App\Models\User;
use FuteBus\Profile\Http\Requests\ChangePasswordRequest;
use FuteBus\Profile\Services\PasswordChangeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('Profile::password', ['user' => $request->user()]);
    }

    public function update(ChangePasswordRequest $request, PasswordChangeService $passwords): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var string $password */
        $password = $request->validated('password');
        $notified = $passwords->change($user, $password);

        return redirect()->route('profile.password.edit')->with(
            $notified ? 'status' : 'warning',
            __($notified ? 'Profile::app.password_change.updated' : 'Profile::app.password_change.mail_failed'),
        );
    }
}
