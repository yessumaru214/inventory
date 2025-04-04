<!-- filepath: /c:/laragon/www/inventory/resources/views/prediccion/index.blade.php -->


<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Predicción Trimestral de Machine Learning</h1>

    <form action="<?php echo e(route('quarterly_predict')); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
        <div class="form-group">
            <label for="model">Modelo de Machine Learning</label>
            <select name="model" id="model" class="form-control">
                <?php $__currentLoopData = $models; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($model); ?>"><?php echo e($model); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="year">Año de Entrenamiento</label>
            <select name="year" id="year" class="form-control">
                <?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($year); ?>"><?php echo e($year); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" name="use_cross_validation" id="use_cross_validation" class="form-check-input">
            <label class="form-check-label" for="use_cross_validation">Usar Validación Cruzada</label>
        </div>
        <button type="submit" class="btn btn-primary">Realizar Predicción</button>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>