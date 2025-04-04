<?php $__env->startSection('title','Inventory | Facturación'); ?>

<?php $__env->startSection('page-title','Facturación'); ?>

<?php $__env->startSection('content'); ?>


<div class="row clearfix">

    <create-invoice :categorys="<?php echo e($category); ?>" :customers="<?php echo e($customer); ?>"></create-invoice>

</div>


<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
            
                <h2 style="visibility: hidden;">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-stock">
                        Nueva factura
                    </button>
                </h2>
            </div>

            <view-invoice :categorys="<?php echo e($category); ?>" :customers="<?php echo e($customer); ?>"></view-invoice>

        </div>
    </div>
</div>




<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/invoice.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>