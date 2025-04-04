@extends('include.master')

@section('content')
<div class="container">
    <h1>Predicción de Demanda Mensual</h1>

    {{-- Formulario de Selección --}}
    <form action="{{ route('quarterly_predict') }}" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <label for="model">Seleccione el Modelo de Machine Learning:</label>
            <select name="model" id="model" class="form-control" required>
                <option value="RandomForest">Random Forest</option>
                <option value="ExtraTrees">Extra Trees</option>
                <option value="DecisionTree">Decision Tree</option>
            </select>
        </div>

        <div class="form-group">
            <label for="year">Seleccione el Año de Predicción:</label>
            <select name="year" id="year" class="form-control" required>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
            </select>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="use_gridsearch" id="use_gridsearch">
            <label class="form-check-label" for="use_gridsearch">Usar GridSearchCV</label>
        </div>

        <div class="form-check">
            <input type="checkbox" class="form-check-input" name="use_cross_validation" id="use_cross_validation">
            <label class="form-check-label" for="use_cross_validation">Usar Validación Cruzada</label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Generar Predicción</button>
    </form>

    {{-- Mostrar Resultados --}}
    @if (isset($result))
        <div class="alert alert-success mt-4">
            <h3>Resultados de Predicción</h3>
        </div>

        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>Nombre del Producto</th>
                    <th>Predicción Mensual</th>
                    <th>MSE</th>
                    <th>MAPE</th>
                    <th>MAD</th>
                    <th>Métrica Combinada</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($result['predictions']) && is_array($result['predictions']))
                    @foreach ($result['predictions'] as $prediction)
                        <tr>
                            <td>{{ $prediction['product_name'] ?? 'Desconocido' }}</td>
                            <td>{{ $prediction['predicted_quantity'] ?? 'N/A' }}</td>
                            <td>{{ $prediction['metrics']['MSE'] ?? 'N/A' }}</td>
                            <td>{{ $prediction['metrics']['MAPE'] ?? 'N/A' }}</td>
                            <td>{{ $prediction['metrics']['MAD'] ?? 'N/A' }}</td>
                            <td>{{ $prediction['combined_metric'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">⚠️ No se encontraron predicciones.</td>
                    </tr>
                @endif
            </tbody>

        </table>
    @endif
</div>
@endsection
