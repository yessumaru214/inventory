from flask import Flask, request, jsonify
from flask_cors import CORS
import logging
import time
import os
import joblib
import pandas as pd
import numpy as np
from utils import rmsle, wape, mad, mape, cross_validation_rmsle_per_product
from model import train_model, load_model, predict_sales

# Configurar logging para depuración
debug_logger = logging.getLogger("debug_logger")
debug_logger.setLevel(logging.DEBUG)
console_handler = logging.StreamHandler()
console_handler.setLevel(logging.DEBUG)
debug_logger.addHandler(console_handler)

# Inicializar la aplicación Flask y permitir CORS
app = Flask(__name__)
CORS(app)

# Ruta para realizar predicciones
@app.route('/predict', methods=['POST'])
def predict():
    start_time = time.time()  # Registrar el tiempo de inicio
    data = request.get_json()  # Obtener datos JSON del cuerpo de la solicitud

    # Validar que los parámetros requeridos estén presentes
    required_keys = ["dataset_path", "model"]
    missing_keys = [key for key in required_keys if key not in data]
    if missing_keys:
        return jsonify({"status": "error", "message": f"Faltan parámetros: {', '.join(missing_keys)}"}), 400

    # Leer parámetros opcionales
    cross_validation = data.get('cross_validation', False)
    metrics_per_product = data.get('metrics_per_product', False)
    hyperparameter_tuning = data.get('hyperparameter_tuning', False)
    hyperparameters = data.get('hyperparameters', {})
    k_folds = data.get('k_folds', 5) if cross_validation or hyperparameter_tuning else None

    # Registrar información de depuración
    debug_logger.info(f"🔧 Parámetro hyperparameter_tuning: {hyperparameter_tuning}")
    debug_logger.info(f"🔧 Parámetro cross_validation: {cross_validation}")
    debug_logger.info(f"🔧 Hiperparámetros recibidos: {hyperparameters}")
    debug_logger.info(f"🔧 Número de k-folds: {k_folds}")

    # Validar y convertir hiperparámetros
    if not isinstance(hyperparameters, dict):
        hyperparameters = {}

    for key, value in hyperparameters.items():
        if value == 'true':
            hyperparameters[key] = True
        elif value == 'false':
            hyperparameters[key] = False
        elif value == 'None' or value == 'auto':
            hyperparameters[key] = None
        elif value == 'ls':
            hyperparameters[key] = 'squared_error'
        elif value.isdigit():
            hyperparameters[key] = int(value)
        else:
            try:
                hyperparameters[key] = float(value)
            except ValueError:
                pass

    if 'bootstrap' in hyperparameters and not hyperparameters['bootstrap']:
        hyperparameters['max_samples'] = None

    try:
        # Cargar el dataset y preparar los datos de entrenamiento
        dataset = pd.read_csv(data['dataset_path'])
        dataset["selling_date"] = pd.to_datetime(dataset["selling_date"])
        train_data = dataset[dataset["selling_date"] < "2024-01-01"]

        if train_data.empty:
            return jsonify({"status": "error", "message": "No hay datos de entrenamiento antes de 2024."}), 400

        # Seleccionar características y etiquetas para el modelo
        X_train = train_data[["product_id", "selling_price", "category_id", "branch_id", "month", "weekday", "moving_avg_7d", "moving_avg_30d"]].values
        y_train = train_data["sold_quantity"].values

        # Determinar el tipo de modelo y cargar o entrenar uno nuevo
        model_type = data.get('model')
        model_filename = f"best_model_{model_type}.pkl"

        if os.path.exists(model_filename) and not hyperparameter_tuning and not hyperparameters:
            debug_logger.info(f"🔄 Cargando modelo guardado: {model_filename}")
            best_model = load_model(model_filename)
            best_params = best_model.get_params()
        else:
            debug_logger.info(f"🚀 Entrenando un nuevo modelo")
            best_model, best_params = train_model(model_type, X_train, y_train, hyperparameters, hyperparameter_tuning, k_folds)
            joblib.dump(best_model, model_filename)
            debug_logger.info(f"✅ Modelo guardado: {model_filename}")

        # Realizar predicciones y calcular métricas
        predictions, metrics = predict_sales(best_model, dataset, debug_logger)

        # Validación cruzada si está habilitada
        if cross_validation:
            debug_logger.info(f"🔄 Ejecutando validación cruzada")
            cvrmsle_per_product = cross_validation_rmsle_per_product(best_model, X_train, y_train, cv=k_folds)
            debug_logger.info(f"📊 CVRMSLE por producto calculado correctamente")

            cvrmsle_values = [v["mean_cvrmsle"] for v in cvrmsle_per_product.values()]
            var_cvrmsle_values = [v["std_cvrmsle"] for v in cvrmsle_per_product.values()]

            mean_cvrmsle_general = np.mean(cvrmsle_values) if cvrmsle_values else None
            var_cvrmsle_general = np.var(var_cvrmsle_values) if var_cvrmsle_values else None
        else:
            cvrmsle_per_product = {}
            mean_cvrmsle_general = None
            var_cvrmsle_general = None

        # Calcular el tiempo de ejecución
        end_time = time.time()
        execution_time = round(end_time - start_time, 2)

        # Construir la respuesta
        response = {
            "status": "success",
            "predictions": predictions,
            **metrics,
            "execution_time": execution_time,
            "best_params": best_params,
            "cvrmsle_per_product": {int(k): v for k, v in cvrmsle_per_product.items()},
            "mean_cvrmsle_general": mean_cvrmsle_general,
            "var_cvrmsle_general": var_cvrmsle_general
        }

        if cross_validation:
            response["k_folds"] = k_folds
            response["hyperparameters"] = hyperparameters

        return jsonify(response)

    except Exception as e:
        # Manejo de errores
        debug_logger.error(f"❌ Error en la predicción: {str(e)}")
        return jsonify({"status": "error", "message": f"Error en la predicción: {str(e)}"}), 500

# Punto de entrada de la aplicación
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)