<aside class="sidebar">
    <ul class="nav-menu">
        @foreach(config('menu') as $item)
            @php
                $hasChildren = isset($item['children']) && count($item['children']) > 0;
                $isActive = isset($item['route']) && request()->routeIs($item['route']);
                $childActive = false;
                if ($hasChildren) {
                    foreach ($item['children'] as $child) {
                        if (isset($child['route']) && request()->routeIs($child['route'])) {
                            $childActive = true;
                            break;
                        }
                    }
                }
            @endphp

            <li class="nav-item {{ $hasChildren ? 'has-sub' : '' }} {{ $isActive || $childActive ? 'active' : '' }}">
                <a href="{{ (isset($item['route']) && Route::has($item['route'])) ? route($item['route']) : 'javascript:void(0)' }}"
                    class="nav-link"
                    @if(!isset($item['route']) || !$item['route'])
                        onclick="showToast({ type: 'info', title: 'Sắp ra mắt!', message: '{{ $item['title'] }} đang được phát triển và sẽ sớm ra mắt.' }); return false;"
                    @endif
                    >
                    <span class="nav-icon">
                        <img src="{{ asset('images/' . $item['icon']) }}" alt="{{ $item['title'] }}">
                    </span>
                    <span class="nav-text">{{ $item['title'] }}</span>
                </a>

                @if($hasChildren)
                    <ul class="sub-menu">
                        @foreach($item['children'] as $child)
                            @php $isChildActive = isset($child['route']) && request()->routeIs($child['route']); @endphp
                            <li class="{{ $isChildActive ? 'active' : '' }}">
                                <a href="{{ (isset($child['route']) && Route::has($child['route'])) ? route($child['route']) : 'javascript:void(0)' }}"
                                    @if(!isset($child['route']) || !$child['route'])
                                        onclick="showToast({ type: 'info', title: 'Sắp ra mắt!', message: '{{ $child['title'] }} đang được phát triển và sẽ sớm ra mắt.' }); return false;"
                                    @endif
                                    >
                                    <img src="{{ asset('images/' . $child['icon']) }}" class="sub-icon">
                                    <span class="sub-text">{{ $child['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</aside>