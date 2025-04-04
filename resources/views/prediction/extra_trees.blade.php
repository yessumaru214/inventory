@extends('include.master')

@section('title', 'Inventory | Extra Trees Model')

@section('page-title', 'Entrenamiento del Modelo Extra Trees')

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
                                    <td class="text-right">{{ number_format($recommendation['predicted_quantity'], 2) }}</td>
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
                        <strong>Entrenar Modelo Extra Trees</strong>
                    </div>
                    <div class="card-body">
                        <form id="train-model-form" method="POST" action="{{ route('extra_trees.train') }}" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group">
                                <label for="csv_file">
                                    <i class="bi bi-file-earmark"></i> Archivo CSV
                                </label>
                                <input type="file" id="csv_file" name="csv_file" class="form-control" required>
                                <small class="form-text text-muted">Sube un archivo CSV válido con las columnas requeridas.</small>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="max_depth">Profundidad Máxima (max_depth)</label>
                                    <input type="number" id="max_depth" name="max_depth" class="form-control" placeholder="Ejemplo: 5">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="n_estimators">Número de Estimadores (n_estimators)</label>
                                    <input type="number" id="n_estimators" name="n_estimators" class="form-control" placeholder="Ejemplo: 100">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="min_samples_split">Mín. Muestras por División (min_samples_split)</label>
                                    <input type="number" id="min_samples_split" name="min_samples_split" class="form-control" placeholder="Ejemplo: 2">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="min_samples_leaf">Mín. Muestras por Hoja (min_samples_leaf)</label>
                                    <input type="number" id="min_samples_leaf" name="min_samples_leaf" class="form-control" placeholder="Ejemplo: 1">
                                </div>
                            </div>

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
        // Inicialización de DataTables
        const table = $('#recommendationsTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" // Traducción al español
            },
            order: [[0, 'asc']], // Orden inicial por la primera columna (Nombre del Producto)
            paging: true, // Habilitar paginación
            searching: true, // Habilitar barra de búsqueda
            info: true // Mostrar información de la tabla
        });

        // Mostrar spinner de carga al enviar el formulario
        $('#train-model-form').on('submit', function (e) {
            const csvFileInput = document.getElementById('csv_file');
            if (!csvFileInput.value) {
                alert('Por favor, selecciona un archivo CSV antes de enviar.');
                e.preventDefault();
                return;
            }

            document.getElementById('loading-spinner').style.display = 'block';
        });
    });
</script>
@endpush
