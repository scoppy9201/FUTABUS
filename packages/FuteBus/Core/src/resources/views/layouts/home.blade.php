<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" translate="no">
<head>
    <meta charset="UTF-8" />
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', __('core::app.home.title'))</title>
    <meta name="description" content="@yield('meta_description', __('core::app.home.title'))">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('og_title', __('core::app.home.title'))">
    <meta property="og:description" content="@yield('og_description', __('core::app.home.title'))">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="overflow-x-hidden bg-white text-gray-800 antialiased">
    @yield('content')
</body>
</html>
