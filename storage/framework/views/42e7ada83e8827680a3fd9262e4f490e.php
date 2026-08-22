<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('core::partials.home.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('core::partials.home.hero', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('core::partials.home.promotions', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('core::partials.home.popular-routes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('core::partials.home.service-quality', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <footer class="bg-gray-900 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center text-sm text-gray-400">
                &copy; <?php echo e(date('Y')); ?> <?php echo e(__('core::app.home.footer.copyright')); ?>

            </div>
        </div>
    </footer>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('core::layouts.home', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\resources\views\home.blade.php ENDPATH**/ ?>