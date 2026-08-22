<?php
    $qualityStats = __('core::app.home.service_quality.stats');
    $qualityImages = [
        'images/service-quality/passengers.png',
        'images/service-quality/ticket-offices.png',
        'images/service-quality/daily-trips.png',
    ];
?>

<section class="bg-white py-12 sm:py-16">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        <header class="text-center">
            <h2 class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] sm:text-3xl">
                <?php echo e(__('core::app.home.service_quality.title')); ?>

            </h2>
            <p class="mt-2 text-sm text-[#4a342e] sm:text-base">
                <?php echo e(__('core::app.home.service_quality.subtitle')); ?>

            </p>
        </header>

        <div class="mt-9 grid items-center gap-10 lg:grid-cols-[1.05fr_.95fr] lg:gap-14">
            <div class="space-y-5 sm:space-y-6">
                <?php $__currentLoopData = $qualityStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <article class="flex items-center gap-4 sm:gap-5">
                        <div class="size-24 shrink-0 overflow-hidden rounded-full border border-[#f9ded4] bg-[#fff0eb] shadow-sm">
                            <img
                                src="<?php echo e(asset($qualityImages[$loop->index])); ?>"
                                alt=""
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-baseline gap-x-2">
                                <h3 class="text-2xl font-black leading-tight text-gray-950 sm:text-[30px]">
                                    <?php echo e($stat['value']); ?>

                                </h3>
                                <span class="font-bold text-gray-950"><?php echo e($stat['label']); ?></span>
                            </div>
                            <p class="mt-1 max-w-md text-sm leading-6 text-[#637083] sm:text-base">
                                <?php echo e($stat['description']); ?>

                            </p>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mx-auto flex h-90 w-full max-w-xl items-center justify-center lg:h-96">
                <img
                    src="<?php echo e(asset('images/service-quality/travel-illustration.png')); ?>"
                    alt="<?php echo e(__('core::app.home.service_quality.illustration_alt')); ?>"
                    class="max-h-full w-full object-contain"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
</section>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\resources\views\partials\home\service-quality.blade.php ENDPATH**/ ?>