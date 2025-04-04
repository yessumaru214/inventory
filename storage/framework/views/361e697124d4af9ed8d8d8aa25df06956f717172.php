<?php $__env->startSection('title','Inventory | Proveedores'); ?>

<?php $__env->startSection('page-title','Lista de proveedores'); ?>

<?php $__env->startSection('content'); ?>

<div class="row clearfix">
    <create-vendor></create-vendor>
</div>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-vendor">
                        Proveedor nuevo
                    </button>
                </h2>
            </div>

            <view-vendor></view-vendor>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/vendor.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>