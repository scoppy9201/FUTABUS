<header class="futa-header-pattern relative z-20 text-white">
    <div class="h-15.5">
        <div class="relative mx-auto flex h-15.5 w-[calc(100%-24px)] max-w-282 items-center justify-between sm:w-[calc(100%-32px)]">
            <div class="flex items-center gap-3.5">
                
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button type="button" @click="open = !open" class="flex items-center gap-1.5 text-sm font-bold" aria-label="<?php echo e(__('core::app.home.navbar.language_selector')); ?>">
                        <span class="inline-flex size-6.5 items-center justify-center overflow-hidden rounded-full">
                            <?php if(app()->getLocale() === 'vi'): ?>
                                <img src="<?php echo e(asset('icons/flags/vi.svg')); ?>" alt="" class="h-full w-full object-cover">
                            <?php else: ?>
                                <img src="<?php echo e(asset('icons/flags/en.svg')); ?>" alt="" class="h-full w-full object-cover">
                            <?php endif; ?>
                        </span>
                        <span><?php echo e(strtoupper(app()->getLocale())); ?></span>
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-3 w-3 transition-transform duration-200',':class' => 'open ? \'rotate-180\' : \'\'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute left-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-lg border border-white/20 bg-white py-1 shadow-xl"
                        style="display: none;"
                    >
                        <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors <?php echo e(app()->getLocale() === 'vi' ? 'bg-orange-50 text-[#ef5222]' : 'text-gray-700 hover:bg-gray-50'); ?>">
                            <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full">
                                <img src="<?php echo e(asset('icons/flags/vi.svg')); ?>" alt="" class="h-full w-full object-cover">
                            </span>
                            <span>Tiếng Việt</span>
                        </a>
                        <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold transition-colors <?php echo e(app()->getLocale() === 'en' ? 'bg-orange-50 text-[#ef5222]' : 'text-gray-700 hover:bg-gray-50'); ?>">
                            <span class="inline-flex size-5.5 items-center justify-center overflow-hidden rounded-full">
                                <img src="<?php echo e(asset('icons/flags/en.svg')); ?>" alt="" class="h-full w-full object-cover">
                            </span>
                            <span>English</span>
                        </a>
                    </div>
                </div>

                <span class="hidden h-6 w-px bg-white/70 sm:block"></span>

                <a href="#" class="hidden items-center gap-1.5 text-sm font-bold sm:flex">
                    <span class="grid size-6.25 place-items-center rounded-full bg-white text-[#22a55b]">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-device-phone-mobile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </span>
                    <span><?php echo e(__('core::app.home.navbar.download_app')); ?></span>
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-3 w-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                </a>
            </div>

            <a href="<?php echo e(route('home')); ?>" class="absolute left-1/2 top-0 grid h-17 w-80 -translate-x-1/2 place-items-center max-lg:w-66 max-sm:w-47.5" aria-label="<?php echo e(__('core::app.home.navbar.home_aria')); ?>">
                <img
                    src="<?php echo e(asset('icons/illustrations/navbar-logo-panel.svg')); ?>"
                    alt=""
                    class="absolute inset-0 h-full w-full drop-shadow-[0_2px_1px_rgba(103,42,11,.08)]"
                    aria-hidden="true"
                >
                <img src="<?php echo e(asset('icons/futabus-logo.png')); ?>" alt="<?php echo e(__('core::app.home.navbar.logo_alt')); ?>" class="futa-brand-logo relative z-10 h-12 w-48.5 object-contain max-lg:scale-125 max-sm:w-33 max-sm:scale-110">
            </a>

            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="flex min-h-8.75 items-center gap-2 rounded-full bg-white px-4.5 text-sm font-bold text-gray-900 shadow-sm max-sm:h-9 max-sm:w-9 max-sm:justify-center max-sm:p-0">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-user-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    <span class="max-sm:hidden"><?php echo e(Auth::user()->name); ?></span>
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="flex min-h-8.75 items-center gap-2 rounded-full bg-white px-4.5 text-sm font-bold text-gray-900 shadow-sm max-sm:h-9 max-sm:w-9 max-sm:justify-center max-sm:p-0">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-user-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    <span class="max-sm:hidden"><?php echo e(__('core::app.home.navbar.login')); ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="h-17" aria-label="<?php echo e(__('core::app.home.navbar.primary_navigation')); ?>">
        <div class="mx-auto flex h-17 max-w-250 items-center justify-center gap-[clamp(28px,3.2vw,58px)] overflow-x-auto px-4 max-md:justify-start">
            <a href="<?php echo e(route('home')); ?>" class="futa-nav-active relative py-5.75 text-sm font-extrabold whitespace-nowrap"><?php echo e(__('core::app.home.navbar.home')); ?></a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap"><?php echo e(__('core::app.home.navbar.schedules')); ?></a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap"><?php echo e(__('core::app.home.navbar.lookup')); ?></a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap"><?php echo e(__('core::app.home.navbar.news')); ?></a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap"><?php echo e(__('core::app.home.navbar.invoice')); ?></a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap"><?php echo e(__('core::app.home.navbar.contact')); ?></a>
            <a href="#" class="py-5.75 text-sm font-extrabold whitespace-nowrap"><?php echo e(__('core::app.home.navbar.about')); ?></a>
        </div>
    </nav>
</header>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\Providers/../resources/views/partials/home/navbar.blade.php ENDPATH**/ ?>