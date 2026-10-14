@props(['title', 'description' => null, 'compact' => false, 'print' => false])

@if($print)
    <div style="padding: 20px; text-align: center; color: #64748b;">
        <img src="{{ public_path('emptyglobal.png') }}" alt="" width="120" style="display: block; margin: 0 auto 10px;">
        <p>{{ $title }}</p>
        @if($description)<p>{{ $description }}</p>@endif
    </div>
@else
    <div {{ $attributes->class(['flex flex-col items-center text-center', 'py-6' => !$compact, 'py-3' => $compact]) }} data-empty-state>
        <img src="{{ asset('emptyglobal.png') }}" alt="" aria-hidden="true" width="1536" height="1024"
            @class(['h-auto max-w-full object-contain', 'w-44' => !$compact, 'w-24' => $compact]) loading="lazy" decoding="async">
        <p @class(['mt-3 font-semibold text-slate-700', 'text-base' => !$compact, 'text-sm' => $compact])>{{ $title }}</p>
        @if($description)
            <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
        @endif
        @if($slot->isNotEmpty())<div class="mt-3">{{ $slot }}</div>@endif
    </div>
@endif
