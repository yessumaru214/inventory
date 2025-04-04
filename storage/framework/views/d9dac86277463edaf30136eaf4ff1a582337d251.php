<?php $__env->startSection('title','Inventory | Dashboard'); ?>


<?php $__env->startSection('page-title','Dashboard'); ?>


<?php $__env->startSection('content'); ?>
            <info-box></info-box>

<?php $__env->stopSection(); ?>


<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/dashboard.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>