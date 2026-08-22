
<?php if($promotions->isNotEmpty()): ?>
<section class="bg-white py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8 text-center">
            <h2 class="text-2xl font-black uppercase tracking-wider text-[#1E603C] sm:text-3xl">
                <?php echo e(__('core::app.home.promotions.title')); ?>

            </h2>
        </div>

        
        <div x-data="{ active: 0, total: <?php echo e($promotions->count()); ?>, perPage: 3 }" class="relative">
            
            <div class="overflow-hidden">
                <div
                    class="flex transition-transform duration-500 ease-in-out"
                    :style="`transform: translateX(-${active * (100 / perPage)}%)`"
                >
                    <?php $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="w-full shrink-0 px-3 sm:w-1/2 lg:w-1/3">
                        <div class="group h-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg">
                            <a href="<?php echo e($promo->link ?? '#'); ?>" class="block">
                                <div class="relative aspect-video overflow-hidden bg-gray-100">
                                    <?php if($promo->image): ?>
                                        <img
                                            src="<?php echo e(asset('storage/' . $promo->image)); ?>"
                                            alt="<?php echo e($promo->title); ?>"
                                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                        >
                                    <?php else: ?>
                                        <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-orange-100 to-orange-200">
                                            <span class="text-orange-400">
                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-megaphone'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-12 w-12']); ?>
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
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h3 class="line-clamp-2 text-sm font-bold text-gray-800 group-hover:text-orange-600 transition">
                                        <?php echo e($promo->title); ?>

                                    </h3>
                                    <?php if($promo->description): ?>
                                        <p class="mt-1.5 line-clamp-2 text-xs text-gray-500">
                                            <?php echo e($promo->description); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <?php if($promotions->count() > 3): ?>
            <div class="mt-6 flex justify-center gap-2">
                <?php
                    $totalSlides = (int) ceil($promotions->count() / 3);
                ?>
                <?php for($i = 0; $i < $totalSlides; $i++): ?>
                    <button
                        type="button"
                        @click="active = <?php echo e($i * 3); ?>; if(active >= total) active = 0"
                        :class="active === <?php echo e($i * 3); ?> ? 'bg-orange-500 w-8' : 'bg-gray-300 w-3'"
                        class="h-3 rounded-full transition-all duration-300"
                    ></button>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\Providers/../resources/views/partials/home/promotions.blade.php ENDPATH**/ ?>