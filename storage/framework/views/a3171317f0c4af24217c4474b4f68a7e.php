<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" translate="no">
<head>
    <meta charset="UTF-8" />
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $__env->yieldContent('title', __('core::app.home.title')); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', __('core::app.home.title')); ?>">
    <link rel="canonical" href="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">
    <meta property="og:title" content="<?php echo $__env->yieldContent('og_title', __('core::app.home.title')); ?>">
    <meta property="og:description" content="<?php echo $__env->yieldContent('og_description', __('core::app.home.title')); ?>">
    <meta property="og:url" content="<?php echo $__env->yieldContent('canonical', url()->current()); ?>">
    <meta property="og:type" content="website">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="overflow-x-hidden bg-white text-gray-900">
    <?php echo $__env->yieldContent('content'); ?>
</body>
</html>
<?php /**PATH D:\laragon\www\FUTABUS\packages\FuteBus\Core\src\Providers/../resources/views/layouts/home.blade.php ENDPATH**/ ?>