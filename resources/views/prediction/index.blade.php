@extends('include.master')

@section('title','Inventory | Entrenamiento de Modelo')

@section('page-title','Entrenamiento de Modelo de Machine Learning')

@section('content')

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="body">

               {{-- Tarjetas de métricas --}}
                @if(isset($result) && is_array($result))
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-center bg-light shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">MSE del Modelo</h5>
                                <p class="card-text text-success font-weight-bold">
                                    {{ $result['MSE'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-light shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">MAPE del Modelo</h5>
                                <p class="card-text text-success font-weight-bold">
                                    {{ $result['MAPE'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center bg-light shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">MAD del Modelo</h5>
                                <p class="card-text text-success font-weight-bold">
                                    {{ $result['MAD'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Tabla de recomendaciones --}}
                @if(isset($result['recommendations']) && is_array($result['recommendations']) && count($result['recommendations']) > 0)
                <h4 class="text-primary">Recomendaciones de Compra</h4>
                <div class="table-responsive">
                  <table id="recommendationsTable" class="table table-bordered table-hover table-striped table-sm">

                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">Nombre del Producto</th>
                                <th class="text-center">Predicción de Demanda</th>
                                <th class="text-center">Stock Actual</th>
                                <th class="text-center">Compra Recomendada</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result['recommendations'] as $recommendation)
                                <tr>
                                    <td>{{ $recommendation['product_name'] }}</td>          
                                    <td class="text-right">{{ round($recommendation['predicted_quantity']) }}</td>
                                    <td class="text-right">{{ number_format($recommendation['current_stock'], 2) }}</td>
                                    <td class="text-right">{{ number_format($recommendation['recommended_purchase'], 2) }}</td>                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @else
                <div class="alert alert-warning">
                    <strong>Atención:</strong> No se generaron recomendaciones de compra.
                </div>
                @endif
                @endif

                {{-- Sección de error --}}
                @if(isset($error))
                <div class="alert alert-danger">
                    <strong>Error:</strong> {{ $error }}
                </div>
                @endif

                {{-- Mostrar salida del script Python para depuración --}}
                @if(isset($output))
                <div class="alert alert-info">
                    <strong>Salida del Script Python:</strong>
                    <pre>{{ $output }}</pre>
                </div>
                @endif

                {{-- Formulario de entrenamiento --}}
                <div class="card mt-4 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <strong>Entrenar Modelo de Machine Learning</strong>
                    </div>
                    <div class="card-body">
                        <form id="train-model-form" method="POST" action="{{ route('prediction.train') }}" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            {{-- Campo para archivo CSV --}}
                            <div class="form-group">
                                <label for="csv_file">
                                    <i class="bi bi-file-earmark"></i> Archivo CSV
                                </label>
                                <input type="file" id="csv_file" name="csv_file" class="form-control" required>
                                <small class="form-text text-muted">Sube un archivo CSV válido con las columnas requeridas.</small>
                            </div>

                    
                            <div class="row">
                            <!-- Parámetro max_depth -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="max_depth">
                                        <i class="bi bi-sliders"></i> Profundidad Máxima (max_depth)
                                    </label>
                                    <select id="max_depth" name="max_depth" class="form-control">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="20">20</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Parámetro n_estimators -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="n_estimators">
                                        <i class="bi bi-bar-chart"></i> Número de Estimadores (n_estimators)
                                    </label>
                                    <select id="n_estimators" name="n_estimators" class="form-control">
                                        <option value="50">50</option>
                                        <option value="100" selected>100</option>
                                        <option value="150">150</option>
                                        <option value="200">200</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Parámetro min_samples_split -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="min_samples_split">
                                        <i class="bi bi-graph-up"></i> Muestras para Dividir (min_samples_split)
                                    </label>
                                    <select id="min_samples_split" name="min_samples_split" class="form-control">
                                        <option value="2" selected>2</option>
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Parámetro min_samples_leaf -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="min_samples_leaf">
                                        <i class="bi bi-tree"></i> Muestras por Hoja (min_samples_leaf)
                                    </label>
                                    <select id="min_samples_leaf" name="min_samples_leaf" class="form-control">
                                        <option value="1" selected>1</option>
                                        <option value="2">2</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </div>
                        </div>



                            {{-- Botón de envío --}}
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="bi bi-play-circle"></i> Entrenar Modelo
                            </button>
                        </form>
                    </div>
                </div>


                {{-- Spinner de carga --}}
                <div id="loading-spinner" style="display: none; text-align: center; margin-top: 20px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Cargando, por favor espera...</p>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<script type="text/javascript">
    $(document).ready(function () {

    });
</script>
@endpush
