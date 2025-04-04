@extends('include.master')

@section('title','Inventory | Entrenamiento de Modelo')

@section('page-title','Entrenamiento de Modelo de Machine Learning')

@section('content')

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="body">
                @if(isset($result))
                    <div class="alert alert-success">
                        <strong>{{ $result['message'] }}</strong><br>
                        <span>MSE del modelo: {{ $result['MSE'] }}</span>
                    </div>
                @endif

                <form id="train-model-form" method="POST" action="{{ route('prediction.train') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label for="csv_file">Archivo CSV</label>
                        <input type="file" id="csv_file" name="csv_file" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="max_depth">Profundidad Máxima (max_depth)</label>
                        <input type="number" id="max_depth" name="max_depth" class="form-control" placeholder="Ejemplo: 5">
                    </div>

                    <div class="form-group">
                        <label for="n_estimators">Número de Estimadores (n_estimators)</label>
                        <input type="number" id="n_estimators" name="n_estimators" class="form-control" placeholder="Ejemplo: 100">
                    </div>

                    <button type="submit" class="btn btn-success">Entrenar Modelo</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@if(isset($error))
    <div class="alert alert-danger">
        <strong>Error:</strong> {{ $error }}
    </div>
@endif

@push('script')
<script type="text/javascript">
    document.getElementById('train-model-form').addEventListener('submit', function(e) {
        // Confirmación antes de enviar el formulario
        if (!confirm('¿Estás seguro de que deseas entrenar el modelo con los datos proporcionados?')) {
            e.preventDefault();
        }
    });
</script>
@endpush
