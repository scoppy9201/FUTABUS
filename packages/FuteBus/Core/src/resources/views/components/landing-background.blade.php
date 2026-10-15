@once
    @push('styles')
        <link rel="preload" as="image" href="{{ asset('images/banners/Abstract Orange Gradient Waves.png') }}">
    @endpush
@endonce

<div {{ $attributes->class(['home-page', 'home-landing'])->merge([
    'style' => "background-image: url('".asset('images/banners/Abstract Orange Gradient Waves.png')."')",
]) }}>
    {{ $slot }}
</div>
