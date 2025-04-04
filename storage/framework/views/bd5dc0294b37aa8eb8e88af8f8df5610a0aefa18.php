<?php $__env->startSection('title','Inventory | Configuración'); ?>


<?php $__env->startSection('page-title','Configuración'); ?>


<?php $__env->startSection('content'); ?>




<div class="row clearfix">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card">
			<div class="header">
				<h2>
					
					Información de la empresa
					
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
                
                <?php if(Session::has('message')): ?>
                 <p class="alert alert-info"><?php echo e(Session::get('message')); ?></p>
                <?php endif; ?>
				
                <form action="<?php echo e(route('company.store')); ?>" method="post">
                    
                    <?php echo e(csrf_field()); ?>

					
					<div class="row">
						<div class="col-md-6">
                            <p>Nombre de la empresa</p>
                            <div class="input-group">
                               
                                <span class="input-group-addon">
                                    <i class="material-icons">store</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" value="<?php echo e($company->name); ?>" class="form-control" name="name" placeholder="Nombre de la empresa">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                                <p>Teléfono de la empresa</p>
                            <div class="input-group">
                               
                                <span class="input-group-addon">
                                    <i class="material-icons">phone</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" value="<?php echo e($company->phone); ?>" class="form-control" name="phone" placeholder="Teléfono de la empresa">
                                </div>
                            </div>
                        </div>

						<div class="col-md-12">
                                <p>Dirección de la empresa</p>
                            <div class="input-group">
                                
                                <span class="input-group-addon">
                                    <i class="material-icons">map</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" value="<?php echo e($company->address); ?>" class="form-control" name="address" placeholder="Dirección de la empresa">
                                </div>
                            </div>
                        </div>
					</div>


					<div class="row text-center">
						<button type="submit" class="btn bg-teal">Actualizar</button>
					</div>


				</form>

			</div>

		</div>
	</div>
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>