@props(['title', 'description' => null, 'compact' => false, 'print' => false, 'inline' => false])

@if($print)
    <div style="padding: 20px; text-align: center; color: #64748b;">
        <img src="{{ public_path('emptyglobal.png') }}" alt="" width="120" style="display: block; margin: 0 auto 10px;">
        <p>{{ $title }}</p>
        @if($description)<p>{{ $description }}</p>@endif
    </div>
@else
    <div {{ $attributes->class(['flex items-center', 'flex-col text-center' => !$inline, 'flex-row gap-2 text-left' => $inline,
        'py-6' => !$compact && !$inline, 'py-3' => $compact && !$inline]) }} data-empty-state>
        <img src="{{ asset('emptyglobal.png') }}" alt="" aria-hidden="true" width="1536" height="1024"
            @class(['h-auto max-w-full shrink-0 object-contain', 'w-44' => !$compact && !$inline,
                'w-24' => $compact && !$inline, 'w-10' => $inline]) loading="lazy" decoding="async">
        <p @class(['font-semibold text-slate-700', 'mt-3' => !$inline, 'text-base' => !$compact && !$inline,
            'text-sm' => $compact || $inline])>{{ $title }}</p>
        @if($description)
            <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">{{ $description }}</p>
        @endif
        @if($slot->isNotEmpty())<div class="mt-3">{{ $slot }}</div>@endif
    </div>
@endif
