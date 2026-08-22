<section class="bg-[#fff8f5] py-10 sm:py-12">
    <div class="mx-auto w-full max-w-282 px-4 sm:px-6 lg:px-0">
        <div class="relative text-center">
            <h2 class="text-2xl font-extrabold uppercase leading-tight text-[#00613d] xl:text-3xl">
                <?php echo e(__('core::app.home.latest_news.title')); ?>

            </h2>
            <p class="mt-2 text-sm text-[#4a342e] sm:text-base">
                <?php echo e(__('core::app.home.latest_news.subtitle')); ?>

            </p>

            <a
                href="#"
                class="mt-3 inline-flex text-sm font-medium text-[#ef5222] transition-colors hover:text-[#d94316] sm:absolute sm:right-0 sm:top-8 sm:mt-0"
            >
                <?php echo e(__('core::app.home.latest_news.view_all')); ?>

            </a>
        </div>

        <?php if($newsArticles->isEmpty()): ?>
            <div class="mt-8 rounded-xl border border-dashed border-orange-200 bg-white px-6 py-10 text-center text-sm text-gray-500">
                <?php echo e(__('core::app.home.latest_news.empty')); ?>

            </div>
        <?php else: ?>
            <div
                class="mt-7 sm:mt-8"
                x-data="{
                    active: 0,
                    perPage: 3,
                    total: <?php echo e($newsArticles->count()); ?>,
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
                        class="flex transition-transform duration-500 ease-out"
                        :style="`transform: translateX(-${active * 100}%)`"
                    >
                        <?php $__currentLoopData = $newsArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="w-full shrink-0 px-2.5 sm:w-1/2 lg:w-1/3">
                                <a href="#" class="group block">
                                    <div class="h-44 overflow-hidden rounded-xl border border-gray-200 bg-white sm:h-48">
                                        <?php if($article->image): ?>
                                            <img
                                                src="<?php echo e(asset('storage/' . $article->image)); ?>"
                                                alt="<?php echo e(__('core::app.home.latest_news.image_alt', ['title' => $article->title])); ?>"
                                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                loading="lazy"
                                            >
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center bg-linear-to-br from-orange-50 via-[#fff4ef] to-orange-100 text-[#ef5222]">
                                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-newspaper'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-12']); ?>
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
                                                <span class="mt-2 text-sm font-bold uppercase tracking-wide">
                                                    <?php echo e(__('core::app.home.latest_news.placeholder')); ?>

                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <h3 class="mt-3 line-clamp-2 min-h-12 text-base font-semibold uppercase leading-6 text-gray-950 transition-colors group-hover:text-[#ef5222]">
                                        <?php echo e($article->title); ?>

                                    </h3>

                                    <div class="mt-2 flex items-center justify-between gap-4">
                                        <time class="text-sm font-medium text-[#4b5563]" datetime="<?php echo e($article->published_at->toDateString()); ?>">
                                            <?php echo e($article->published_at->format('d/m/Y')); ?>

                                        </time>
                                        <span class="inline-flex items-center gap-1 text-sm font-medium text-[#ef5222]">
                                            <?php echo e(__('core::app.home.latest_news.details')); ?>

                                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-4']); ?>
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
                                </a>
                            </article>
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
        <?php endif; ?>
    </div>
</section>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\Providers/../resources/views/partials/home/latest-news.blade.php ENDPATH**/ ?>