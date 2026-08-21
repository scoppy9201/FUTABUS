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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        futa: {
                            orange: '#f97316',
                            'orange-dark': '#ea580c',
                            green: '#16a34a',
                            'green-dark': '#15803d',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-gray-800">
    @yield('content')
</body>
</html>
