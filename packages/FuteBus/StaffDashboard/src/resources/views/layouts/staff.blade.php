<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>@yield('title') · FUTABUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'packages/FuteBus/Core/src/resources/css/app.css', 'packages/FuteBus/Core/src/resources/js/app.js', 'packages/FuteBus/StaffDashboard/src/resources/css/app.css', 'packages/FuteBus/StaffDashboard/src/resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.13.0/lottie.min.js" defer></script>
</head>
<body class="staff-portal bg-[#F8F9FA] font-[Inter,sans-serif] text-gray-800 antialiased">
    @include('core::components.ui-select-template')
    @include('core::components.ui-calendar-templates')
    @include('core::partials.global-loader')
    @yield('content')
    @include('core::components.confirm-dialog')
    @stack('scripts')
</body>
</html>
