<?php $__env->startSection('title','Inventory | Categorías'); ?>

<?php $__env->startSection('page-title','Lista de categorías'); ?>

<?php $__env->startSection('content'); ?>

<div class="row clearfix">

    <create-category></create-category>

</div>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="header">
                <h2>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-category">
                        Nueva categoría
                    </button>
                </h2>
            </div>

            <view-category></view-category>

        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/category.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>