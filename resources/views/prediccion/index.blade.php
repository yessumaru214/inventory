<!-- filepath: /c:/laragon/www/inventory/resources/views/prediccion/index.blade.php -->
@extends('include.master')

@section('content')
<div class="container">
    <h1>Predicción Trimestral de Machine Learning</h1>

    <form action="{{ route('quarterly_predict') }}" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <label for="model">Modelo de Machine Learning</label>
            <select name="model" id="model" class="form-control">
                @foreach ($models as $model)
                    <option value="{{ $model }}">{{ $model }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="year">Año de Entrenamiento</label>
            <select name="year" id="year" class="form-control">
                @foreach ($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" name="use_cross_validation" id="use_cross_validation" class="form-check-input">
            <label class="form-check-label" for="use_cross_validation">Usar Validación Cruzada</label>
        </div>
        <button type="submit" class="btn btn-primary">Realizar Predicción</button>
    </form>
</div>
@endsection