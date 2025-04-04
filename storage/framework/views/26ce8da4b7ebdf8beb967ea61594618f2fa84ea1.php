<?php $__env->startSection('title','Inventory | Clientes'); ?>


<?php $__env->startSection('page-title','Todos los clientes'); ?>


<?php $__env->startSection('content'); ?>


        <div class="row clearfix">
        	
        	<create-customer></create-customer>

        </div>


        <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                        <!--     <h2>
                                Vendor List
                          
                            </h2> -->
                          
                          <h2>
                          	 <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-customer">
                                Cliente nuevo
                             </button>
                          </h2>
                        </div>

                        <view-customer></view-customer>

                    </div>
                </div>
            </div>

          


<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/customer.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>