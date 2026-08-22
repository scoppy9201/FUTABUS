@php
    $companyLinks = [
        'about',
        'schedule',
        'recruitment',
        'news',
        'network',
        'privacy',
        'payment',
        'pricing',
        'refund',
    ];
    $supportLinks = [
        'lookup',
        'terms',
        'transaction',
        'service',
        'faq',
        'complaint',
        'customer',
        'web_guide',
        'topup_guide',
    ];
    $companyRoutes = [
        'about' => 'about',
        'privacy' => 'privacy',
    ];
    $brandLogos = [
        ['icons/futabus-logo.png', 'FUTA Bus Lines'],
        ['icons/footer-brands/futa-express.png', 'FUTA Express'],
        ['icons/footer-brands/futa-advertising.png', 'FUTA Advertising'],
        ['icons/footer-brands/phuc-loc-rest-stop.png', 'Phúc Lộc Rest Stop'],
    ];
@endphp

<footer class="bg-[#fff8f5] text-base font-medium text-gray-950">
    <div class="mx-auto w-full max-w-285 px-4 py-12 sm:px-6 lg:px-0">
        <div class="grid gap-10 lg:grid-cols-[1.3fr_.75fr_.75fr] lg:gap-14">
            <div>
                <div>
                    <h2 class="font-bold uppercase text-[#00613d]">
                        {{ __('core::app.home.footer.hotline_title') }}
                    </h2>
                    <a href="tel:19006067" class="mt-1 block text-[32px] font-medium leading-tight text-[#ef5222]">
                        {{ __('core::app.home.footer.hotline') }}
                    </a>
                </div>

                <h3 class="mt-6 font-bold uppercase text-[#00613d]">
                    {{ __('core::app.home.footer.company') }}
                </h3>
                <div class="mt-2 space-y-2 leading-6">
                    <p>
                        <span class="font-medium text-gray-600">{{ __('core::app.home.footer.address_label') }}</span>
                        {{ __('core::app.home.footer.address') }}
                    </p>
                    <p>
                        <span class="font-medium text-gray-600">{{ __('core::app.home.footer.email_label') }}</span>
                        <a href="mailto:hotro@futa.vn" class="text-[#ef5222]">{{ __('core::app.home.footer.email') }}</a>
                    </p>
                    <div class="grid gap-1 sm:grid-cols-2">
                        <p><span class="font-medium text-gray-600">{{ __('core::app.home.footer.phone_label') }}</span> {{ __('core::app.home.footer.phone') }}</p>
                        <p><span class="font-medium text-gray-600">{{ __('core::app.home.footer.fax_label') }}</span> {{ __('core::app.home.footer.fax') }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <h3 class="font-bold uppercase text-[#00613d]">{{ __('core::app.home.footer.download_app') }}</h3>
                        <div class="mt-3 flex flex-nowrap items-center gap-2.5">
                            <a href="#" aria-label="{{ __('core::app.home.footer.google_play') }}" class="block shrink-0 transition-transform hover:-translate-y-0.5">
                                <img src="{{ asset('icons/stores/google-play.svg') }}" alt="" class="h-8 w-auto">
                            </a>
                            <a href="#" aria-label="{{ __('core::app.home.footer.app_store') }}" class="block shrink-0 transition-transform hover:-translate-y-0.5">
                                <img src="{{ asset('icons/stores/app-store.svg') }}" alt="" class="h-8 w-auto">
                            </a>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold uppercase text-[#00613d]">{{ __('core::app.home.footer.connect') }}</h3>
                        <div class="mt-3 flex gap-3">
                            <a href="#" aria-label="Facebook" class="block size-8 transition-transform hover:-translate-y-0.5">
                                <img src="{{ asset('icons/social/facebook.svg') }}" alt="" class="size-full">
                            </a>
                            <a href="#" aria-label="YouTube" class="block size-8 transition-transform hover:-translate-y-0.5">
                                <img src="{{ asset('icons/social/youtube.svg') }}" alt="" class="size-full">
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="font-bold text-[#00613d]">{{ __('core::app.home.footer.company_links') }}</h2>
                <ul class="mt-3 space-y-3.5">
                    @foreach($companyLinks as $link)
                        <li class="flex gap-3">
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-gray-300"></span>
                            <a
                                href="{{ isset($companyRoutes[$link]) ? route($companyRoutes[$link]) : '#' }}"
                                class="leading-5 transition-colors hover:text-[#ef5222]"
                            >
                                {{ __("core::app.home.footer.links.{$link}") }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="font-bold text-[#00613d]">{{ __('core::app.home.footer.support_links') }}</h2>
                <ul class="mt-3 space-y-3.5">
                    @foreach($supportLinks as $link)
                        <li class="flex gap-3">
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-gray-300"></span>
                            <a href="#" class="leading-5 transition-colors hover:text-[#ef5222]">
                                {{ __("core::app.home.footer.links.{$link}") }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-12 grid grid-cols-2 items-center gap-x-8 gap-y-6 sm:grid-cols-4">
            @foreach($brandLogos as [$path, $name])
                <img src="{{ asset($path) }}" alt="{{ $name }}" class="mx-auto h-16 w-full object-contain">
            @endforeach
        </div>
    </div>

    <div class="bg-[#17653f] px-4 py-3 text-center text-sm font-semibold leading-5 text-white sm:text-base">
        <span>&copy; 2026 | {{ __('core::app.home.footer.copyright') }}</span>
        <span class="hidden sm:inline"> | </span>
        <span class="block sm:inline">{{ __('core::app.home.footer.developed') }}</span>
    </div>
</footer>
