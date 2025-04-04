<?php $__env->startSection('title','Inventory | Configuración'); ?>


<?php $__env->startSection('page-title','Configuración'); ?>


<?php $__env->startSection('content'); ?>




<div class="row clearfix">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card">
			<div class="header">
				<h2>
					
                   Cambiar la contraseña					
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
				
                <form action="<?php echo e(route('password.store')); ?>" method="post">
                    
                    <?php echo e(csrf_field()); ?>

					
					<div class="row">
						<div class="col-md-12">
                           
                            <div class="input-group">
                               
                                <span class="input-group-addon">
                                    <i class="material-icons">password</i>
                                </span>
                                <div class="form-line">
                                    <input type="password" 
                                     class="form-control"
                                     name="old_password" placeholder="Ingresá tu contraseña actual">
                                </div>
                            </div>

                            <div class="input-group">
                               
                                <span class="input-group-addon">
                                    <i class="material-icons">lock</i>
                                </span>
                                <div class="form-line">
                                    <input type="password" 
                                     class="form-control"
                                     name="password" placeholder="Ingresá tu nueva contraseña">
                                </div>
                            </div>

                            <div class="input-group">
                               
                                <span class="input-group-addon">
                                    <i class="material-icons">lock_open</i>
                                </span>
                                <div class="form-line">
                                    <input type="password" 
                                     class="form-control"
                                     name="password_confirmation" placeholder="Reingresá tu nueva contraseña">
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