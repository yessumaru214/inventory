from flask import Flask, request, jsonify
import json
import os
import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor, ExtraTreesRegressor, GradientBoostingRegressor
from sklearn.tree import DecisionTreeRegressor
from sklearn.metrics import mean_squared_log_error, mean_squared_error, r2_score
from sklearn.model_selection import KFold, GridSearchCV
from flask_cors import CORS
import logging

# Configurar logging para depuración
debug_logger = logging.getLogger("debug_logger")
debug_logger.setLevel(logging.DEBUG)
console_handler = logging.StreamHandler()
console_handler.setLevel(logging.DEBUG)
debug_logger.addHandler(console_handler)

# Inicializar la aplicación Flask y permitir CORS
app = Flask(__name__)
CORS(app)

def rmsle(y_true, y_pred):
    """ Calcula el RMSLE (Root Mean Squared Logarithmic Error). """
    y_true = np.array(y_true)
    y_pred = np.array(y_pred)
    
    log_true = np.log1p(y_true)
    log_pred = np.log1p(y_pred)
    
    return np.sqrt(np.mean((log_true - log_pred) ** 2))


def wape(y_true, y_pred):
    """ Calcula el WAPE (Weighted Absolute Percentage Error). """
    y_true = np.array(y_true)
    y_pred = np.array(y_pred)
    total_demand = np.sum(y_true)
    
    if total_demand == 0:
        return 0  # Evitar divisiones por cero

    return np.sum(np.abs(y_true - y_pred)) / total_demand * 100


def bias_error(y_true, y_pred):
    """ Calcula el Bias como el promedio de las diferencias entre predicción y valores reales. """
    y_true = np.array(y_true)
    y_pred = np.array(y_pred)
    return np.mean(y_pred - y_true)


def load_csv_data(path):
    """ Carga los datos desde un archivo CSV y los convierte en un DataFrame """
    if not os.path.exists(path):
        raise FileNotFoundError(f"Archivo no encontrado: {path}")

    df = pd.read_csv(path)
    if df.empty:
        raise ValueError(f"El archivo {path} está vacío o mal formado.")

    return df

@app.route('/predict', methods=['POST'])
def predict():
    """ Endpoint de predicción """
    data = request.get_json()
    required_keys = ["dataset_path", "model", "predict_year", "predict_month"]
    missing_keys = [key for key in required_keys if key not in data]
    if missing_keys:
        return jsonify({"status": "error", "message": f"Faltan parámetros: {', '.join(missing_keys)}"}), 400

    try:
        # Cargar y procesar el dataset
        dataset = load_csv_data(data['dataset_path'])
        dataset["selling_date"] = pd.to_datetime(dataset["selling_date"])
        predict_year = int(data["predict_year"])
        predict_month = int(data["predict_month"])

        # Filtrar datos de entrenamiento y datos reales
        train_data = dataset[dataset["selling_date"] < f"{predict_year}-{predict_month:02d}-01"]
        actual_data = dataset[(dataset["selling_date"].dt.year == predict_year) & (dataset["selling_date"].dt.month == predict_month)]

        if train_data.empty:
            return jsonify({"status": "error", "message": f"No hay datos de entrenamiento antes de {predict_year}-{predict_month:02d}."}), 400

        # Variables de entrenamiento
        X_train = train_data[["product_id", "selling_price", "category_id", "branch_id", "month", "weekday", "moving_avg_7d", "moving_avg_30d"]].values
        y_train = train_data["sold_quantity"].values
        product_ids_train = train_data["product_id"].values

        # Definir los hiperparámetros a optimizar según el modelo (Moverlo aquí)
        param_grid = {
            "RandomForest": {
                "n_estimators": [50, 100, 200],
                "max_depth": [10, 20, None],
                "min_samples_split": [2, 5, 10]
            },
            "ExtraTrees": {
                "n_estimators": [50, 100, 200],
                "max_depth": [10, 20, None],
                "min_samples_split": [2, 5, 10]
            },
            "DecisionTree": {
                "max_depth": [10, 20, None],
                "min_samples_split": [2, 5, 10],
                "min_samples_leaf": [1, 2, 5]
            },
            "GBM": {
                "n_estimators": [50, 100, 200],
                "learning_rate": [0.01, 0.1, 0.2],
                "max_depth": [3, 5, 10]
            }
        }

        # Selección del modelo
        model_type = data.get('model')
        models = {
            "ExtraTrees": ExtraTreesRegressor(),
            "RandomForest": RandomForestRegressor(),
            "DecisionTree": DecisionTreeRegressor(),
            "GBM": GradientBoostingRegressor()
        }
        if model_type not in models:
            return jsonify({"status": "error", "message": f"Modelo '{model_type}' no válido."}), 400

        # Nombre del archivo del modelo según el mes
        model_filename = f"best_model_{model_type}_{predict_year}_{predict_month}.pkl"

        # Si ya existe un modelo para ese mes, lo cargamos
        if os.path.exists(model_filename):
            debug_logger.info(f"🔄 Cargando modelo guardado para {predict_year}-{predict_month}: {model_filename}")
            best_model = joblib.load(model_filename)
        else:
            debug_logger.info(f"🚀 Entrenando un nuevo modelo para {predict_year}-{predict_month}")

            grid_search = GridSearchCV(
                models[model_type], 
                param_grid[model_type], 
                cv=3, 
                scoring="neg_mean_squared_error", 
                n_jobs=-1
            )
            grid_search.fit(X_train, y_train)

            best_model = grid_search.best_estimator_
            joblib.dump(best_model, model_filename)
            debug_logger.info(f"✅ Modelo guardado para {predict_year}-{predict_month}: {model_filename}")

        # ❌ Eliminado el segundo GridSearchCV innecesario
         # ✅ Inicializar `product_rmsle` antes de usarlo
        product_rmsle = {}
        debug_logger.info(f"✅ Modelo listo para predicciones: {model_filename}")
        rmsle_cv_per_product = {}
        debug_logger.info(f"📊 RMSLE por producto antes de calcular la media: {product_rmsle}")
        debug_logger.info(f"📊 RMSLE por producto en validación cruzada: {rmsle_cv_per_product}")
       

        # Validación cruzada (5-fold) para obtener RMSLE por producto
        kf = KFold(n_splits=min(len(X_train), 5), shuffle=True, random_state=42)

        for train_index, val_index in kf.split(X_train):
            X_train_fold, X_val_fold = X_train[train_index], X_train[val_index]
            y_train_fold, y_val_fold = y_train[train_index], y_train[val_index]
            product_ids_fold = product_ids_train[val_index]

            best_model.fit(X_train_fold, y_train_fold)
            y_pred_fold = best_model.predict(X_val_fold)

            for i, product_id in enumerate(product_ids_fold):
                # ✅ Ajustar valores para evitar log(0)
                y_true_adj = max(y_val_fold[i], 1)
                y_pred_adj = max(y_pred_fold[i], 1)

                rmsle_value = rmsle([y_true_adj], [y_pred_adj])
                if np.isnan(rmsle_value) or np.isinf(rmsle_value):
                    rmsle_value = 0  # Reemplazar valores no válidos con 0

                if product_id not in product_rmsle:
                    product_rmsle[product_id] = []
                product_rmsle[product_id].append(rmsle_value)

        # ✅ Verificar si product_rmsle está vacío
        if not product_rmsle:
            debug_logger.warning("⚠️ product_rmsle está vacío después de la validación cruzada.")

        # ✅ Evitar listas vacías en el cálculo final
        rmsle_cv_per_product = {pid: np.mean(rmsles) if len(rmsles) > 0 else 0 for pid, rmsles in product_rmsle.items()}
        if not rmsle_cv_per_product:
            rmsle_cv_per_product = {0: 0}  # Evitar errores con listas vacías

        # ✅ Registrar valores después de validación cruzada
        debug_logger.info(f"📊 RMSLE por producto en validación cruzada: {rmsle_cv_per_product}")


        # Promediar RMSLE por producto
        rmsle_cv_per_product = {pid: np.mean(rmsles) if len(rmsles) > 0 else 0 for pid, rmsles in product_rmsle.items()}

        # Si sigue estando vacío, logueamos el problema
        if not rmsle_cv_per_product:
            debug_logger.warning("⚠️ RMSLE en validación cruzada está vacío después del cálculo. Verifica los datos.")


        # Filtrar datos para predicción
        X_predict = dataset[(dataset["month"] == predict_month) & (dataset["selling_date"].dt.year == predict_year)]
        if X_predict.empty:
            return jsonify({"status": "error", "message": f"No hay datos de predicción para {predict_year}-{predict_month:02d}."}), 400

        X_predict_values = X_predict[["product_id", "selling_price", "category_id", "branch_id", "month", "weekday", "moving_avg_7d", "moving_avg_30d"]].values
        predicted_sales = best_model.predict(X_predict_values)

        # Variables para calcular métricas generales
        weighted_wape_sum = 0
        weighted_mse_sum = 0
        total_sales = 0
        y_true_global = []
        y_pred_global = []
        product_metrics = {}

        # Calcular métricas por producto
        for i, (index, row) in enumerate(X_predict.iterrows()):
            product_id = int(row["product_id"])
            y_pred = float(predicted_sales[i])
            y_true = actual_data[actual_data["product_id"] == product_id]["sold_quantity"].sum()
            y_true = float(y_true) if y_true > 0 else 1

            mse = mean_squared_error([y_true], [y_pred])
            rmsle_value = rmsle([y_true], [y_pred])
            wape_value = wape([y_true], [y_pred])
            mad = np.abs(y_true - y_pred)
            bias = bias_error([y_true], [y_pred])

            y_true_global.append(y_true)
            y_pred_global.append(y_pred)

            product_metrics[product_id] = {
                "MSE": mse,
                "RMSLE": rmsle_value,
                "RMSLE_CV": rmsle_cv_per_product.get(product_id, 0),
                "WAPE": wape_value,
                "MAD": mad,
                "Bias": bias,
                "predicted_sales": y_pred,
                "actual_sales": y_true
            }

            weighted_wape_sum += wape_value * y_true
            weighted_mse_sum += mse * y_true
            total_sales += y_true

        # Calcular métricas generales ponderadas
        wape_sales = weighted_wape_sum / total_sales if total_sales > 0 else 0
        mse_sales = weighted_mse_sum / total_sales if total_sales > 0 else 0
        mad_sales = np.mean([p["MAD"] for p in product_metrics.values()])
        rmsle_sales = rmsle(y_true_global, y_pred_global) if len(y_true_global) > 1 else 0
        r2_sales = r2_score(y_true_global, y_pred_global) if len(y_true_global) > 1 else 0

        debug_logger.info(f"📊 y_true_global: {y_true_global}")
        debug_logger.info(f"📊 y_pred_global: {y_pred_global}")

        # Registrar métricas finales
        debug_logger.info(f"📊 Métricas Generales - MSE: {mse_sales}, WAPE: {wape_sales}, MAD: {mad_sales}, RMSLE: {rmsle_sales}, R2: {r2_sales}")

        return jsonify({
            "status": "success",
            "MSE_sales": mse_sales,
            "WAPE_sales": wape_sales,
            "MAD_sales": mad_sales,
            "RMSLE_sales": rmsle_sales,
            "R2_sales": r2_sales,
            "RMSLE_CV_sales": np.mean(list(rmsle_cv_per_product.values())) if len(rmsle_cv_per_product) > 0 else 0,
            "product_metrics": product_metrics
        })

    
    except Exception as e:
        debug_logger.error(f"❌ Error en la predicción: {str(e)}")
        return jsonify({"status": "error", "message": f"Error en la predicción: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
