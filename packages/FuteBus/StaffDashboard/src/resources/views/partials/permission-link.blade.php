@if(auth()->user()->hasPermissionTo($item['permission']))
    <a href="{{ route($item['route']) }}"
        @if(request()->routeIs($item['active'])) aria-current="page" @endif
        @class([
            'flex w-full items-center gap-3 px-4 py-3 text-sm font-medium transition',
            'bg-futa-orange-soft font-semibold text-futa-orange border-r-4 border-futa-orange' => request()->routeIs($item['active']),
            'text-gray-600 hover:bg-futa-orange-soft hover:text-futa-orange' => ! request()->routeIs($item['active']),
        ])>
        <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="size-5 shrink-0" />
        <span>{{ $item['label'] }}</span>
    </a>
@else
    <button type="button" aria-disabled="true" title="{{ $item['hint'] }}"
        @click="void window.FutaNotify?.warning(@js($item['hint']))"
        class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-medium text-slate-400">
        <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="size-5 shrink-0" />
        <span>{{ $item['label'] }}</span>
        <x-heroicon-o-lock-closed class="ml-auto size-4 shrink-0" aria-hidden="true" />
    </button>
@endif
