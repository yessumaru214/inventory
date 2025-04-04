<?php $__env->startSection('title','Inventory | Report'); ?>


<?php $__env->startSection('page-title','Report'); ?>


<?php $__env->startSection('content'); ?>




<div class="row clearfix">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card">
			<div class="header">
				<h2>
					
					Generate Report
					
				</h2>
			</div>


			<div class="body">

				
				<?php if($errors->any()): ?>
				<div class="alert alert-danger">
					<ul>
						<?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<li><?php echo e($error); ?></li>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</ul>
				</div>
				<?php endif; ?>
				
				<form action="<?php echo e(route('report.store')); ?>" method="GET">
					
					<div class="row">
						<report-form :user="<?php echo e($user); ?>" :customer="<?php echo e($customer); ?>" :category="<?php echo e($category); ?>" :vendor="<?php echo e($vendor); ?>"></report-form>
					</div>


					<div class="row text-center">
						<button type="submit" class="btn bg-teal">Get Report</button>
					</div>


				</form>

			</div>


		</div>
	</div>
</div>




<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>

<script type="text/javascript" src="<?php echo e(url('public/js/report.js')); ?>"></script>

<?php $__env->stopPush(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>