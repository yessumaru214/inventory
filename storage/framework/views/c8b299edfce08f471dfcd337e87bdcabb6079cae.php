<?php $__env->startSection('title','Inventory | Productos'); ?>

<?php $__env->startSection('page-title','Lista de productos'); ?>

<?php $__env->startSection('content'); ?>

<div class="row clearfix">
    <create-product :categorys="<?php echo e(json_encode($category)); ?>"></create-product>
</div>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-product">
                        Producto nuevo
                    </button>
                </h2>
            </div>

            <view-product :categorys="<?php echo e(json_encode($category)); ?>"></view-product>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/product.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>