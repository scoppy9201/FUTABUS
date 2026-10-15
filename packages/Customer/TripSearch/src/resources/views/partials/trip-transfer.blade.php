<div x-show="openPanel === 'transfer'" class="trip-card__detail-content trip-card__transfer-content">
    <h3 class="mb-0! text-base! leading-6! font-bold!">{{ __('core::trip-search.transfer_information') }}</h3>
    @foreach (__('core::trip-search.transfer_items') as $item)
        <p class="text-[15px] leading-6 text-slate-950">- {{ $item['label'] }} : <em class="italic">{{ $item['detail'] }}</em></p>
    @endforeach
</div>
