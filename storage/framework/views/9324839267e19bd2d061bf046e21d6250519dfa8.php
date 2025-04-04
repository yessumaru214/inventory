<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Predicción de Ventas con Machine Learning</h1>

    <form id="prediction-form" action="<?php echo e(route('machine_learning.train')); ?>" method="POST">
        <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
        <div class="row align-items-end">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="model">Modelo de Machine Learning</label>
                    <select name="model" id="model" class="form-control">
                    <option value="DecisionTree">Árbol de Decisión</option>  
                    <option value="ExtraTrees">Extra Trees</option>  
                    <option value="RandomForest">Random Forest</option>
                     <option value="GBM">Gradient Boosting Machines</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="year">Año de Predicción</label>
                    <select name="year" id="year" class="form-control">
                        <option value="2023">2023</option>
                        <option value="2024" selected>2024</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="month">Mes de Predicción</label>
                    <select name="month" id="month" class="form-control">
                        <?php for($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4 mt-3">
                <button type="submit" class="btn btn-primary w-100">Generar Predicción</button>
            </div>
        </div>
    </form>

    <?php if(!empty($result ?? [])): ?>
    <div class="alert alert-info mt-4">
        <div class="row">
            <div class="col-12 ps-3">
                <h4>Información del Entrenamiento</h4>
            </div>
        </div>
        <div class="row ps-3">
            <div class="col-md-4"><strong>Modelo Usado:</strong> <?php echo e($model ?? 'Desconocido'); ?></div>
            <div class="col-md-4"><strong>Año de Predicción:</strong> <?php echo e($year ?? 'No especificado'); ?></div>
            <div class="col-md-4"><strong>Mes de Predicción:</strong> <?php echo e($month ?? 'No especificado'); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sección de Errores -->
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger mt-3"><?php echo e($error); ?></div>
    <?php endif; ?>

    <!-- Resultados de la predicción -->
    <?php if(!empty($result ?? [])): ?>
    <div class="alert alert-success mt-4">
        <div class="row">
            <div class="col-12 ps-3">
                <h4>Métricas Generales del Modelo (Ponderadas)</h4>
            </div>
        </div>
        <div class="row ps-3">
            <div class="col-md-3"><strong>RMSLE:</strong> <?php echo e(number_format($result['RMSLE_sales'] ?? 0, 2)); ?></div>
            <div class="col-md-3"><strong>RMSLE (Validación Cruzada):</strong> <?php echo e(number_format($result['RMSLE_CV_sales'] ?? 0, 2)); ?></div>
            <div class="col-md-3"><strong>Error Medio Absoluto (MAD):</strong> <?php echo e(number_format($result['MAD_sales'] ?? 0, 2)); ?></div>
            <div class="col-md-3"><strong>Error Cuadrático Medio (MSE):</strong> <?php echo e(number_format($result['MSE_sales'] ?? 0, 2)); ?></div>
            <div class="col-md-3"><strong>WAPE Ponderado:</strong> <?php echo e(number_format($result['WAPE_sales'] ?? 0, 2)); ?>%</div>
            <div class="col-md-3"><strong>R²:</strong> <?php echo e(number_format($result['R2_sales'] ?? 0, 2)); ?></div> <!-- Nueva métrica añadida -->
        </div>
    </div>

    <h4 class="mt-4">Métricas por Producto</h4>

    <?php if(!empty($result['product_metrics']) && is_array($result['product_metrics'])): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID del Producto</th>
                    <th>Predicción de Ventas</th>
                    <th>Venta Real</th>
                    <th>RMSLE</th>
                    <th>RMSLE (Validación Cruzada)</th>
                    <th>MAD</th>
                    <th>MSE</th>
                    <th>WAPE</th>
                    <th>Bias</th> <!-- ✅ Nueva métrica añadida -->
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $result['product_metrics']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $productId => $metrics): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($productId); ?></td>
                        <td><?php echo e(number_format((float) $metrics['predicted_sales'], 2)); ?></td>
                        <td><?php echo e(number_format((float) $metrics['actual_sales'], 2)); ?></td>
                        <td><?php echo e(number_format((float) $metrics['RMSLE'], 2)); ?></td>
                        <td><?php echo e(number_format((float) $metrics['RMSLE_CV'], 2)); ?></td>
                        <td><?php echo e(number_format((float) $metrics['MAD'], 2)); ?></td>
                        <td><?php echo e(number_format((float) $metrics['MSE'], 2)); ?></td>
                        <td><?php echo e(number_format((float) $metrics['WAPE'], 2)); ?>%</td>
                        <td><?php echo e(number_format((float) $metrics['Bias'], 2)); ?></td> <!-- ✅ Bias añadido -->
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning mt-3">No hay datos de predicción disponibles.</div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>