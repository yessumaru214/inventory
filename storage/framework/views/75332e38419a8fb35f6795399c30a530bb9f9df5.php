<?php $__env->startSection('title','Inventory | Usuarios'); ?>


<?php $__env->startSection('page-title','Usuarios'); ?>


<?php $__env->startSection('content'); ?>


        <div class="row clearfix">
        	
        	<create-user></create-user>

        </div>


        <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                        <!--     <h2>
                                Vendor List
                          
                            </h2> -->
                          
                          <h2>
                          	 <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-user">
                                Usuario nuevo
                             </button>
                          </h2>
                        </div>

                        <view-user></view-user>

                    </div>
                </div>
            </div>

          


<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/user.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>