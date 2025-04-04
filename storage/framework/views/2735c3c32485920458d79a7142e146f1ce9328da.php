<?php $__env->startSection('content'); ?>
<div class="container">
    <h2>Predicción de Demanda</h2>

    <div class="card">
        <div class="card-body">
            <form id="predictionForm">
                <div class="form-group">
                    <label for="modelo">Seleccionar Modelo:</label>
                    <select id="modelo" class="form-control">
                        <option value="RandomForest">Random Forest</option>
                        <option value="GradientBoosting">Gradient Boosting</option>
                        <option value="ExtraTrees">Extra Trees</option>
                        <option value="DecisionTree">Decision Tree</option>
                        <option value="Ridge">Ridge</option>
                    </select>
                </div>

                <div class="form-group">
                    <input type="checkbox" id="gridsearch"> Usar GridSearchCV
                </div>

                <button type="button" class="btn btn-primary" onclick="ejecutarPrediccion()">Generar Predicción</button>
            </form>
        </div>
    </div>

    <div id="resultado" class="mt-4"></div>
</div>

<script>
function ejecutarPrediccion() {
    let modelo = document.getElementById("modelo").value;
    let gridsearch = document.getElementById("gridsearch").checked;

    fetch("<?php echo e(url('/predict')); ?>", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
        },
        body: JSON.stringify({ modelo, gridsearch })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Predicción:", data);
        mostrarResultados(data);
    })
    .catch(error => {
        console.error("Error en la predicción:", error);
        document.getElementById("resultado").innerHTML = `<p class='text-danger'>Error al generar la predicción.</p>`;
    });
}

function mostrarResultados(data) {
    if (!data || data.error) {
        document.getElementById("resultado").innerHTML = `<p class='text-danger'>Error en la respuesta del servidor.</p>`;
        return;
    }

    let resultadoDiv = document.getElementById("resultado");
    resultadoDiv.innerHTML = `
        <h3>Resultados</h3>
        <table class='table table-bordered'>
            <tr><th>Modelo</th><td>${data.modelo}</td></tr>
            <tr><th>MSE</th><td>${data.MSE.toFixed(4)}</td></tr>
            <tr><th>MAPE</th><td>${data.MAPE.toFixed(4)}</td></tr>
            <tr><th>MAD</th><td>${data.MAD.toFixed(4)}</td></tr>
            <tr><th>MSE Validación Cruzada</th><td>${data.validacion_cruzada_MSE.toFixed(4)}</td></tr>
        </table>
        <h4>Predicción para los próximos 12 meses:</h4>
        <table class='table table-striped'>
            <thead><tr><th>Mes</th><th>Predicción</th></tr></thead>
            <tbody>
                ${data.predicciones_12_meses.map((p, i) => `<tr><td>Mes ${i+1}</td><td>${p.toFixed(2)}</td></tr>`).join('')}
            </tbody>
        </table>`;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('include.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>