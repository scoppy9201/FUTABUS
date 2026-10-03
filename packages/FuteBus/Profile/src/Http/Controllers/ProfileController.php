<?php

declare(strict_types=1);

namespace FuteBus\Profile\Http\Controllers;

use FuteBus\Profile\Http\Requests\UpdateProfileRequest;
use FuteBus\Profile\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('Profile::show', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request, ProfileService $profiles): RedirectResponse
    {
        $profiles->update($request->user(), $request->safe()->except('avatar'), $request->file('avatar'));

        return redirect()->route('profile.show')->with('status', __('Profile::app.updated'));
    }

    public function avatar(Request $request): StreamedResponse
    {
        $path = $request->user()->avatar;
        abort_if(! is_string($path) || ! str_starts_with($path, 'avatars/') || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }
}
