<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['vi', 'en'];
        $defaultLocale = config('app.locale', 'vi');

        if ($request->has('lang')) {
            $locale = $request->get('lang');
        } elseif ($request->hasSession() && $request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } elseif ($request->cookies->has('locale')) {
            $locale = $request->cookie('locale');
        } elseif (Auth::check() && filled(Auth::user()->language)) {
            $locale = Auth::user()->language;
        } else {
            $locale = $defaultLocale;
        }

        $locale = in_array($locale, $supportedLocales, true)
            ? $locale
            : (in_array($defaultLocale, $supportedLocales, true) ? $defaultLocale : 'vi');

        App::setLocale($locale);

        if ($request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
