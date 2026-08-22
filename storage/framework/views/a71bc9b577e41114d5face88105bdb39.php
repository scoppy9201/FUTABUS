<?php
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
    $brandLogos = [
        ['icons/futabus-logo.png', 'FUTA Bus Lines'],
        ['icons/footer-brands/futa-express.png', 'FUTA Express'],
        ['icons/footer-brands/futa-advertising.png', 'FUTA Advertising'],
        ['icons/footer-brands/phuc-loc-rest-stop.png', 'Phúc Lộc Rest Stop'],
    ];
?>

<footer class="bg-[#fff8f5] text-base font-medium text-gray-950">
    <div class="mx-auto w-full max-w-285 px-4 py-12 sm:px-6 lg:px-0">
        <div class="grid gap-10 lg:grid-cols-[1.3fr_.75fr_.75fr] lg:gap-14">
            <div>
                <div>
                    <h2 class="font-bold uppercase text-[#00613d]">
                        <?php echo e(__('core::app.home.footer.hotline_title')); ?>

                    </h2>
                    <a href="tel:19006067" class="mt-1 block text-[32px] font-medium leading-tight text-[#ef5222]">
                        <?php echo e(__('core::app.home.footer.hotline')); ?>

                    </a>
                </div>

                <h3 class="mt-6 font-bold uppercase text-[#00613d]">
                    <?php echo e(__('core::app.home.footer.company')); ?>

                </h3>
                <div class="mt-2 space-y-2 leading-6">
                    <p>
                        <span class="font-medium text-gray-600"><?php echo e(__('core::app.home.footer.address_label')); ?></span>
                        <?php echo e(__('core::app.home.footer.address')); ?>

                    </p>
                    <p>
                        <span class="font-medium text-gray-600"><?php echo e(__('core::app.home.footer.email_label')); ?></span>
                        <a href="mailto:hotro@futa.vn" class="text-[#ef5222]"><?php echo e(__('core::app.home.footer.email')); ?></a>
                    </p>
                    <div class="grid gap-1 sm:grid-cols-2">
                        <p><span class="font-medium text-gray-600"><?php echo e(__('core::app.home.footer.phone_label')); ?></span> <?php echo e(__('core::app.home.footer.phone')); ?></p>
                        <p><span class="font-medium text-gray-600"><?php echo e(__('core::app.home.footer.fax_label')); ?></span> <?php echo e(__('core::app.home.footer.fax')); ?></p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <h3 class="font-bold uppercase text-[#00613d]"><?php echo e(__('core::app.home.footer.download_app')); ?></h3>
                        <div class="mt-3 flex flex-nowrap items-center gap-2.5">
                            <a href="#" aria-label="<?php echo e(__('core::app.home.footer.google_play')); ?>" class="block shrink-0 transition-transform hover:-translate-y-0.5">
                                <img src="<?php echo e(asset('icons/stores/google-play.svg')); ?>" alt="" class="h-8 w-auto">
                            </a>
                            <a href="#" aria-label="<?php echo e(__('core::app.home.footer.app_store')); ?>" class="block shrink-0 transition-transform hover:-translate-y-0.5">
                                <img src="<?php echo e(asset('icons/stores/app-store.svg')); ?>" alt="" class="h-8 w-auto">
                            </a>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold uppercase text-[#00613d]"><?php echo e(__('core::app.home.footer.connect')); ?></h3>
                        <div class="mt-3 flex gap-3">
                            <a href="#" aria-label="Facebook" class="block size-8 transition-transform hover:-translate-y-0.5">
                                <img src="<?php echo e(asset('icons/social/facebook.svg')); ?>" alt="" class="size-full">
                            </a>
                            <a href="#" aria-label="YouTube" class="block size-8 transition-transform hover:-translate-y-0.5">
                                <img src="<?php echo e(asset('icons/social/youtube.svg')); ?>" alt="" class="size-full">
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="font-bold text-[#00613d]"><?php echo e(__('core::app.home.footer.company_links')); ?></h2>
                <ul class="mt-3 space-y-3.5">
                    <?php $__currentLoopData = $companyLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex gap-3">
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-gray-300"></span>
                            <a href="#" class="leading-5 transition-colors hover:text-[#ef5222]">
                                <?php echo e(__("core::app.home.footer.links.{$link}")); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <div>
                <h2 class="font-bold text-[#00613d]"><?php echo e(__('core::app.home.footer.support_links')); ?></h2>
                <ul class="mt-3 space-y-3.5">
                    <?php $__currentLoopData = $supportLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex gap-3">
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-gray-300"></span>
                            <a href="#" class="leading-5 transition-colors hover:text-[#ef5222]">
                                <?php echo e(__("core::app.home.footer.links.{$link}")); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>

        <div class="mt-12 grid grid-cols-2 items-center gap-x-8 gap-y-6 sm:grid-cols-4">
            <?php $__currentLoopData = $brandLogos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$path, $name]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <img src="<?php echo e(asset($path)); ?>" alt="<?php echo e($name); ?>" class="mx-auto h-16 w-full object-contain">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="bg-[#17653f] px-4 py-3 text-center text-sm font-semibold leading-5 text-white sm:text-base">
        <span>&copy; 2026 | <?php echo e(__('core::app.home.footer.copyright')); ?></span>
        <span class="hidden sm:inline"> | </span>
        <span class="block sm:inline"><?php echo e(__('core::app.home.footer.developed')); ?></span>
    </div>
</footer>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\Providers/../resources/views/partials/home/footer.blade.php ENDPATH**/ ?>