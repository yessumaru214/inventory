@extends('include.master')
@section('content')
<div class="container-fluid">
    <form id="prediction-form" action="{{ route('machine_learning_2.train') }}" method="POST" onsubmit="formatHyperparameters(); showThrobber(); removeDeveloperOptions()">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <div class="row text-center " style="background-color:rgb(0, 150, 136); color: white;"> 
            <div class="col-xs-12 col-sm-3 col-md-3  m-t-20 m-b-10"><!-- Título -->
                <strong style="font-size: 18px;">Predicción de Ventas</strong>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-3 m-t-15 m-b-10"><!-- Botón -->
                <button type="submit" class="btn btn-primary btn-block"><strong style="font-size: 15px;">Generar Predicciones</strong></button>
            </div>
            <div class="col-xs-12 col-sm-3 col-md-3 m-t-15 m-b-10"></div>
            <div class="col-xs-12 col-sm-3 col-md-3 m-t-20 m-b-10"><!-- Checkbox -->
                <input type="checkbox" class="form-check-input" id="developer_options" name="developer_options" onchange="toggleDeveloperOptions()">
                <label class="form-check-label" for="developer_options"><strong style="font-size: 15px;">Opciones de desarrollador</strong></label>
            </div>
        </div>
        <div id="developer-options-section" style="display: none;">
            <div class="row align-items-center m-t-15">
                <div class="col-md-3 ">
                    <div class="form-group">
                        <select name="model" id="model" class="form-control" onchange="updateHyperparameters()">
                            <option value="DecisionTree" {{ old('model') == 'DecisionTree' ? 'selected' : '' }}>Árbol de Decisión</option>  
                            <option value="ExtraTrees" {{ old('model', 'ExtraTrees') == 'ExtraTrees' ? 'selected' : '' }}>Extra Trees</option>  
                            <option value="RandomForest" {{ old('model') == 'RandomForest' ? 'selected' : '' }}>Random Forest</option>
                            <option value="GBM" {{ old('model') == 'GBM' ? 'selected' : '' }}>Gradient Boosting Machines</option>
                        </select>
                    </div>
                </div>

                <div class="col-xs-4 col-md-3">
                    <div class="form-group d-flex align-items-center">
                        <input type="checkbox" class="form-check-input" id="cross_validation" name="cross_validation" {{ old('cross_validation', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cross_validation">Validación Cruzada</label>
                    </div>
                </div>

                <div class="col-xs-4 col-md-3">
                    <div class="form-group d-flex align-items-center">
                        <input type="checkbox" class="form-check-input" id="hyperparameter_tuning" name="hyperparameter_tuning" {{ old('hyperparameter_tuning', true) ? 'checked' : '' }} onchange="toggleHyperparameters()">
                        <label class="form-check-label" for="hyperparameter_tuning">Autoajuste de Hiperparámetros</label>
                    </div>
                </div>

                <div class="col-xs-4 col-md-3">
                    <div class="form-group d-flex align-items-center">
                        <input type="checkbox" class="form-check-input" id="metrics_per_product" name="metrics_per_product" {{ old('metrics_per_product') ? 'checked' : '' }}>
                        <label class="form-check-label" for="metrics_per_product">Calcular métricas por producto</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-12 text-left m-b-10"><!-- Título -->
                    <strong>Hiperparámetros del modelo:</strong>
                </div>
            </div>
            <div class="row">
                <div id="hyperparameters" class="col-12">
                    <!-- Los hiperparámetros dinámicos se insertarán aquí -->
                </div>
            </div>
        </div>
        <div class="row  text-center">
            <div class="col-12 m-t-20">
                <div id="throbber" class="spinner-border text-primary" role="status" style="display: none;">
                    <span class="visually-hidden"><h4> GENERANDO PREDICCIONES ... </h4></span>
                </div>
            </div>
        </div>
    </form>

    @if (!empty($result ?? []))
        <script>
            document.getElementById('throbber').style.display = 'none';
        </script>
        <div>
            <button id="toggleTrainingInfo" class="btn btn-primary btn-block m-t-5 m-b-5" onclick="toggleVisibility('trainingInfo')" style="display: none;">
                <strong>Mostrar/Ocultar Información del Entrenamiento</strong>
            </button>
            <div id="trainingInfo" class="table-container">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Modelo Usado</th>
                                <th>Tiempo de Ejecución</th>
                                <th>RMSLE General</th>
                                <th>WAPE General</th>
                                @if (isset($result['mean_cvrmsle_general']) && (isset($cross_validation) && $cross_validation || isset($metrics_per_product) && $metrics_per_product))
                                    <th>RMSLE<sub>CV</sub>  General</th>
                                    <th>Varianza RMSLE<sub>CV</sub>  General</th>
                                @endif
                                <th>MAD General</th>
                                <th>MAPE General</th>
                                <th>MSE General</th>
                                <th>R2 General</th>
                                <th>Validación Cruzada</th>
                                @if (isset($result['k_folds']))
                                    <th>Número de K-Folds</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $model ?? 'Desconocido' }}</td>
                                <td>{{ isset($result['execution_time']) ? $result['execution_time'] . ' segundos' : 'No disponible' }}</td>
                                <td>{{ isset($result['rmsle_general']) ? number_format((float) $result['rmsle_general'], 5, ',', '.') : 'No disponible' }}</td>
                                <td>{{ isset($result['wape_general']) ? number_format((float) $result['wape_general'], 5, ',', '.') : 'No disponible' }}</td>
                                @if (isset($result['mean_cvrmsle_general']) && (isset($cross_validation) && $cross_validation || isset($metrics_per_product) && $metrics_per_product))
                                    <td>{{ number_format((float) $result['mean_cvrmsle_general'], 5, ',', '.') }}</td>
                                    <td>{{ number_format((float) $result['var_cvrmsle_general'], 5, ',', '.') }}</td>
                                @endif
                                <td>{{ isset($result['mad_general']) ? number_format((float) $result['mad_general'], 5, ',', '.') : 'No disponible' }}</td>
                                <td>{{ isset($result['mape_general']) ? number_format((float) $result['mape_general'], 5, ',', '.') : 'No disponible' }}</td>
                                <td>{{ isset($result['mse_general']) ? number_format((float) $result['mse_general'], 5, ',', '.') : 'No disponible' }}</td>
                                <td>{{ isset($result['r2_general']) ? number_format((float) $result['r2_general'], 5, ',', '.') : 'No disponible' }}</td>
                                <td>{{ $cross_validation ? 'Sí' : 'No' }}</td>
                                @if (isset($result['k_folds']))
                                    <td>{{ $result['k_folds'] }}</td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if (isset($result['best_params']))
                    <button id="toggleHyperparametersUsed" class="btn btn-primary btn-block m-t-5 m-b-5" onclick="toggleVisibility('hyperparametersUsed')" style="display: none;">
                        <strong>Mostrar/Ocultar Hiperparámetros Usados</strong>
                    </button>
                    <div id="hyperparametersUsed" class="table-container">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        @foreach ($result['best_params'] as $param => $value)
                                            <th>{{ $param }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach ($result['best_params'] as $value)
                                            <td>{{ is_null($value) ? 'null' : (is_array($value) ? json_encode($value) : $value) }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
            @endif
        </div>
    @endif

    @if (!empty($error))
        <div class="alert alert-danger mt-3">{{ $error }}</div>
    @endif

    @if (!empty($result ?? []))
        <button id="toggleMetricasTable" class="btn btn-primary btn-block m-t-5 m-b-15" onclick="toggleVisibility('metricasTable')" style="display: none;">
            <strong>Mostrar/Ocultar Metricas por mes</strong>
        </button>
   
        <div id="metricasTable" class="table-container"> 
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Mes</th>
                            <th>RMSLE</th>
                            <th>WAPE</th>
                            <th>MAD</th>
                            <th>MAPE</th>
                            <th>MSE</th>
                            <th>R2</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                        @endphp
                        @for ($i = 1; $i <= 12; $i++)
                            <tr>
                                <td>{{ $meses[$i-1] }}</td>
                                <td>{{ number_format((float) ($result['rmsle_per_month'][$i] ?? 0.0001), 5, ',', '.') }}</td>
                                <td>{{ number_format((float) ($result['wape_per_month'][$i] ?? 0.0001), 5, ',', '.') }}%</td>
                                <td>{{ number_format((float) ($result['mad_per_month'][$i] ?? 0.0001), 5, ',', '.') }}</td>
                                <td>{{ number_format((float) ($result['mape_per_month'][$i] ?? 0.0001), 5, ',', '.') }}%</td>
                                <td>{{ number_format((float) ($result['mse_per_month'][$i] ?? 0.0001), 5, ',', '.') }}</td>
                                <td>{{ number_format((float) ($result['r2_per_month'][$i] ?? 0.0001), 5, ',', '.') }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <h4>Predicciones de Ventas para 2024</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Producto</th>
                        @php
                            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                        @endphp
                        @for ($i = 0; $i < 12; $i++)
                            <th class="d-none d-md-table-cell">{{ $meses[$i] }}</th>
                        @endfor
                        @if ($metrics_per_product)
                            <th>RMSLE</th>
                            <th>WAPE</th>
                            @if (isset($result['cvrmsle_per_product']))
                                <th>RMSLE<sub>CV</sub> </th>
                                <th>Varianza RMSLE<sub>CV</sub> </th>
                            @endif
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['predictions'] as $productId => $months)
                        <tr>
                            <td>{{ $productId }}</td>
                            <td>{{ $productNames[$productId] ?? $productId }}</td>
                            @for ($i = 1; $i <= 12; $i++)
                                <td class="d-none d-md-table-cell" style="{{ ($months[$i] ?? 0) == 0 ? 'color: #A0A0A0;' : '' }}">{{ number_format((float) ($months[$i] ?? 0), 2, ',', '.') }}</td>
                            @endfor
                            @if ($metrics_per_product)
                                <td>{{ number_format((float) ($result['rmsle_per_product'][$productId] ?? 0.0001), 3, ',', '.') }}</td>
                                <td>{{ number_format((float) ($result['wape_per_product'][$productId] ?? 0.0001), 3, ',', '.') }}%</td>
                                @if (isset($result['cvrmsle_per_product']))
                                    <td>{{ number_format((float) ($result['cvrmsle_per_product'][$productId]['mean_cvrmsle'] ?? 0.0001), 3, ',', '.') }}</td>
                                    <td>{{ number_format((float) ($result['cvrmsle_per_product'][$productId]['std_cvrmsle'] ?? 0.0001), 3, ',', '.') }}</td>
                                @endif
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif
</div>
<script>
    function toggleDeveloperOptions() {
        var section = document.getElementById('developer-options-section');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';

        var buttons = ['toggleTrainingInfo', 'toggleHyperparametersUsed', 'toggleMetricasTable'];
        buttons.forEach(function(buttonId) {
            var button = document.getElementById(buttonId);
            if (button) {
                button.style.display = section.style.display;
            }
        });

        if (section.style.display === 'none') {
            var elementsToHide = ['trainingInfo', 'hyperparametersUsed', 'metricasTable'];
            elementsToHide.forEach(function(elementId) {
                var element = document.getElementById(elementId);
                if (element) {
                    element.style.display = 'none';
                }
            });
        }
    }

    function toggleVisibility(id) {
        var element = document.getElementById(id);
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }

    function removeDeveloperOptions() {
        document.getElementById('developer_options').removeAttribute('name');
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleVisibility('trainingInfo');
        toggleVisibility('hyperparametersUsed');
        toggleVisibility('metricasTable');
    });

    
    function updateHyperparameters() {
        const model = document.getElementById('model').value;
        const hyperparametersDiv = document.getElementById('hyperparameters');
        hyperparametersDiv.innerHTML = '';

        const hyperparametersConfig = {
            DecisionTree: [
                { name: 'criterion', options: ['squared_error', 'friedman_mse', 'absolute_error', 'poisson'] },
                { name: 'max_depth', options: [10, 20, 'None'] },
                { name: 'min_samples_split', options: [2, 5, 10] },
                { name: 'min_samples_leaf', options: [1, 2, 5] },
                { name: 'max_features', options: ['auto', 'sqrt', 'log2'] }
            ],
            ExtraTrees: [
                { name: 'n_estimators', options: [50, 100, 200] },
                { name: 'bootstrap', options: [true, false] },
                { name: 'max_samples', options: [0.5, 0.75, 1.0] },
                { name: 'min_samples_split', options: [2, 5, 10] }
            ],
            RandomForest: [
                { name: 'n_estimators', options: [50, 100, 200] },
                { name: 'bootstrap', options: [true, false] },
                { name: 'max_samples', options: [0.5, 0.75, 1.0] },
                { name: 'max_depth', options: [10, 20, 'None'] },
                { name: 'oob_score', options: [true, false] }
            ],
            GBM: [
                { name: 'n_estimators', options: [50, 100, 200] },
                { name: 'learning_rate', options: [0.01, 0.1, 0.2] },
                { name: 'max_depth', options: [3, 5, 10] },
                { name: 'subsample', options: [0.5, 0.75, 1.0] },
                { name: 'loss', options: ['ls', 'lad', 'huber'] }
            ]
        };

        const hyperparameters = hyperparametersConfig[model] || [];
        
        hyperparameters.forEach(param => {
            hyperparametersDiv.appendChild(createHyperparameterElement(param));
        });
    }

    function createHyperparameterElement(param) {
        const div = document.createElement('div');
        div.className = 'col-xs-2 col-md-2';

        const label = document.createElement('label');
        label.innerText = param.name;

        const select = document.createElement('select');
        select.name = param.name;
        select.className = 'form-control';

        param.options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option;
            opt.innerText = option;
            select.appendChild(opt);
        });

        div.appendChild(label);
        div.appendChild(select);
        return div;
    }

    function formatHyperparameters() {
        document.querySelectorAll('#hyperparameters select').forEach(select => {
            let value = select.value.trim(); // Eliminamos espacios en blanco
            
            switch (value) {
                case 'true':
                    select.value = true;
                    break;
                case 'false':
                    select.value = false;
                    break;
                case 'None':
                    select.value = null;
                    break;
                default:
                    if (isFinite(value)) { // Comprueba si es un número válido
                        select.value = parseFloat(value);
                    }
            }
        });
    }


    function toggleVisibility(elementId) {
        var element = document.getElementById(elementId);
        if (element.style.display === "none") {
            element.style.display = "block";
        } else {
            element.style.display = "none";
        }
    }

    function toggleHyperparameters() {
        const hyperparameterTuning = document.getElementById('hyperparameter_tuning').checked;
        const selects = document.getElementById('hyperparameters').getElementsByTagName('select');
        for (let select of selects) {
            select.disabled = hyperparameterTuning;
        }
    }

    function showThrobber() {
        document.getElementById('throbber').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateHyperparameters();
        toggleHyperparameters(); // Asegúrate de que el estado inicial sea correcto
    });
</script>

@endsection
