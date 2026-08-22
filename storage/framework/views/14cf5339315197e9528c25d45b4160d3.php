<?php
    $ecosystemItems = [
        [
            'label' => __('core::app.home.futa_ecosystem.items.contract_car'),
            'image' => 'images/futa-ecosystem/contract-car.png',
            'featured' => false,
        ],
        [
            'label' => __('core::app.home.futa_ecosystem.items.buy_ticket'),
            'image' => 'images/service-quality/daily-trips.png',
            'featured' => true,
        ],
        [
            'label' => __('core::app.home.futa_ecosystem.items.delivery'),
            'image' => 'images/futa-ecosystem/delivery.png',
            'featured' => false,
        ],
        [
            'label' => __('core::app.home.futa_ecosystem.items.city_bus'),
            'image' => 'images/futa-ecosystem/city-bus.png',
            'featured' => false,
        ],
    ];
?>

<section class="bg-white py-10 sm:py-12">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        <header class="text-center">
            <h2 class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] xl:text-3xl">
                <?php echo e(__('core::app.home.futa_ecosystem.title')); ?>

            </h2>
            <p class="mx-auto mt-2 max-w-2xl text-sm leading-5 text-[#4a342e] sm:text-base sm:leading-6">
                <?php echo e(__('core::app.home.futa_ecosystem.subtitle')); ?>

            </p>
        </header>

        <div class="mx-auto mt-8 grid max-w-4xl grid-cols-2 gap-x-5 gap-y-8 sm:mt-10 sm:grid-cols-4 sm:gap-6">
            <?php $__currentLoopData = $ecosystemItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="#" class="group flex min-w-0 flex-col items-center text-center">
                    <span class="size-24 overflow-hidden rounded-full bg-[#fff0eb] ring-1 ring-[#fae4dc] transition-transform duration-300 group-hover:-translate-y-1 sm:size-25">
                        <img
                            src="<?php echo e(asset($item['image'])); ?>"
                            alt=""
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </span>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'mt-4 text-lg font-medium leading-6 transition-colors sm:text-xl',
                        'text-[#ef5222]' => $item['featured'],
                        'text-[#4b4b4b] group-hover:text-[#ef5222]' => ! $item['featured'],
                    ]); ?>">
                        <?php echo e($item['label']); ?>

                    </span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\Providers/../resources/views/partials/home/futa-ecosystem.blade.php ENDPATH**/ ?>