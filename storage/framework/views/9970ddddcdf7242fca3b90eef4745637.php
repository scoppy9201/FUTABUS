
<?php if($promotions->isNotEmpty()): ?>
<section class="bg-white py-10 sm:py-12">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        
        <div class="mb-7 text-center sm:mb-8">
            <h2 class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] xl:text-3xl">
                <?php echo e(__('core::app.home.promotions.title')); ?>

            </h2>
        </div>

        
        <div
            class="relative"
            x-data="{
                active: 0,
                perPage: 3,
                total: <?php echo e($promotions->count()); ?>,
                totalPages: 1,
                init() {
                    this.syncLayout();
                },
                syncLayout() {
                    this.perPage = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1;
                    this.totalPages = Math.max(1, Math.ceil(this.total / this.perPage));
                    this.active = Math.min(this.active, this.totalPages - 1);
                },
            }"
            @resize.window.debounce.150ms="syncLayout()"
        >
            
            <div class="overflow-hidden">
                <div
                    class="flex transition-transform duration-500 ease-in-out"
                    :style="`transform: translateX(-${active * 100}%)`"
                >
                    <?php $__currentLoopData = $promotions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $promoImageExists = $promo->image
                            && Storage::disk('public')->exists($promo->image);
                    ?>
                    <div class="w-full shrink-0 px-2.5 sm:w-1/2 lg:w-1/3">
                        <div class="group h-full overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-lg">
                            <a href="<?php echo e($promo->link ?? '#'); ?>" class="block">
                                <div class="relative h-44 overflow-hidden bg-gray-100 sm:h-48">
                                    <?php if($promoImageExists): ?>
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
                                        <p class="mt-1.5 line-clamp-2 text-xs font-medium text-gray-600">
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

            
            <div class="mt-6 flex justify-center gap-2" x-show="totalPages > 1">
                <template x-for="page in totalPages" :key="page">
                    <button
                        type="button"
                        class="h-2.5 rounded-full transition-all duration-300"
                        :class="active === page - 1 ? 'w-7 bg-[#ef5222]' : 'w-2.5 bg-gray-300'"
                        @click="active = page - 1"
                        :aria-label="`Trang ${page}`"
                        :aria-current="active === page - 1 ? 'page' : null"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\Providers/../resources/views/partials/home/promotions.blade.php ENDPATH**/ ?>