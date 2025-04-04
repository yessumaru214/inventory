<?php $__env->startSection('title','Inventory | Existencias'); ?>


<?php $__env->startSection('page-title','Lista de Existencias'); ?>


<?php $__env->startSection('content'); ?>


<div class="row clearfix">

    <create-stock :date="<?php echo e(json_encode(date('Y-m-d'))); ?>" :vendors="<?php echo e($vendor); ?>" :categorys="<?php echo e($category); ?>"></create-stock>

</div>


<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-stock">
                        Agregar Existencias
                    </button>
                </h2>
            </div>

            <view-stock :vendors="<?php echo e($vendor); ?>" :categorys="<?php echo e($category); ?>" :products="<?php echo e($product); ?>"></view-stock>

        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/stock.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>