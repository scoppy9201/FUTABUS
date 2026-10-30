@if(auth()->user()->hasPermissionTo($item['permission']))
    <a href="{{ route($item['route']) }}"
        @if(request()->routeIs($item['active'])) aria-current="page" @endif
        class="futa-portal-nav-link">
        <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="size-5 shrink-0" />
        <span>{{ $item['label'] }}</span>
    </a>
@else
    <button type="button" aria-disabled="true" title="{{ $item['hint'] }}"
        @click="void window.FutaNotify?.warning(@js($item['hint']))"
        class="futa-portal-nav-link">
        <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="size-5 shrink-0" />
        <span>{{ $item['label'] }}</span>
        <x-heroicon-o-lock-closed class="ml-auto size-4 shrink-0" aria-hidden="true" />
    </button>
@endif
