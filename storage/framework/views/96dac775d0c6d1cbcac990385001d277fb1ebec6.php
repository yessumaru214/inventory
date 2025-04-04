<!DOCTYPE html>
<html>
<head>
	<title>Inventory | Login</title>
	<?php echo $__env->make('include.header', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
</head>
<body class="login-page">
    <div class="login-box">
        <div class="logo">
            <a href="javascript:void(0);"><img class="img-fluid" src="<?php echo e(url('images/logo.png')); ?>" alt="inventory logo"> </a>
            <!-- <small>A Inventory Softwaare</small> -->
        </div>
        <div class="card">
            <div class="body">
                <form id="sign_in" method="POST" action="<?php echo e(route('login')); ?>">
                	  <?php echo e(csrf_field()); ?>

                    <div class="msg">Iniciar sesión</div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
                        <div class="form-line">
                            <input type="text" class="form-control" name="email" placeholder="Correo electrónico" required autofocus>
                        </div>
                           <?php if($errors->has('email')): ?>
                                    <span class="help-block">
                                        <strong><?php echo e($errors->first('email')); ?></strong>
                                    </span>
                                <?php endif; ?>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
                        <div class="form-line">
                            <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                               <?php if($errors->has('password')): ?>
                                    <span class="help-block">
                                        <strong><?php echo e($errors->first('password')); ?></strong>
                                    </span>
                                <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-8 p-t-5">
                            <input type="checkbox" name="remember" <?php echo e(old('remember') ? 'checked' : ''); ?> id="rememberme" class="filled-in chk-col-pink">
                            <label for="rememberme">Recuérdame</label>
                        </div>
                        <div class="col-xs-4">
                            <button class="btn btn-block bg-pink waves-effect" type="submit">Ingresar</button>
                        </div>
                    </div>
                    <div class="row m-t-15 m-b--20">
                        <div class="col-xs-6">
                            <!-- <a href="sign-up.html">Register Now!</a> -->
                        </div>
                        <!--<div class="col-xs-6 align-right">
                            <a href="href="<?php echo e(route('password.request')); ?>"">¿Olvidaste tu contraseña?</a>
                        </div>-->
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php echo $__env->make('include.footer', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
</body>
</html>