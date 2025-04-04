import numpy as np
from sklearn.ensemble import RandomForestRegressor, ExtraTreesRegressor, GradientBoostingRegressor
from sklearn.tree import DecisionTreeRegressor
from sklearn.model_selection import GridSearchCV, KFold
from sklearn.metrics import mean_squared_error, r2_score
from utils import rmsle, wape, mad, mape
import joblib

# Función para entrenar un modelo basado en el tipo y los parámetros proporcionados
def train_model(model_type, X_train, y_train, hyperparameters, hyperparameter_tuning, k_folds):
    # Definir los hiperparámetros para cada tipo de modelo
    param_grid = {
        "RandomForest": {
            "n_estimators": [50, 100],
            "max_depth": [10, 20],
            "min_samples_split": [2, 5],
            "bootstrap": [True, False],
            "max_samples": [0.5, 0.75],
            "oob_score": [True, False]
        },
        "ExtraTrees": {
            "n_estimators": [50, 100],
            "bootstrap": [True, False],
            "max_samples": [0.5, 0.75],
            "min_samples_split": [2, 5]
        },
        "DecisionTree": {
            "criterion": ["squared_error", "friedman_mse", "absolute_error", "poisson"],
            "max_depth": [10, 20, None],
            "min_samples_split": [2, 5, 10],
            "min_samples_leaf": [1, 2, 5],
            "max_features": [None, "sqrt", "log2"]
        },
        "GBM": {
            "n_estimators": [50, 100],
            "learning_rate": [0.01, 0.1],
            "max_depth": [3, 5],
            "subsample": [0.75, 1.0],
            "loss": ["squared_error", "absolute_error"]
        }
    }

    # Definir los modelos disponibles
    models = {
        "ExtraTrees": ExtraTreesRegressor(random_state=42),
        "RandomForest": RandomForestRegressor(random_state=42),
        "DecisionTree": DecisionTreeRegressor(random_state=42),
        "GBM": GradientBoostingRegressor(random_state=42)
    }

    # Validar que el tipo de modelo sea válido
    if model_type not in models:
        raise ValueError(f"Modelo '{model_type}' no válido.")

    # Realizar ajuste de hiperparámetros si está habilitado
    if hyperparameter_tuning:
        grid_search = GridSearchCV(
            models[model_type], 
            param_grid[model_type], 
            cv=k_folds, 
            scoring="neg_mean_squared_error",
            n_jobs=-1
        )
        grid_search.fit(X_train, y_train)
        best_model = grid_search.best_estimator_
        best_params = grid_search.best_params_
    # Realizar validación cruzada si está habilitada
    elif k_folds:
        best_model = models[model_type]
        best_model.set_params(**hyperparameters)
        kf = KFold(n_splits=k_folds)
        cvrmsle_scores = []
        for train_index, test_index in kf.split(X_train):
            X_train_fold, X_test_fold = X_train[train_index], X_train[test_index]
            y_train_fold, y_test_fold = y_train[train_index], y_train[test_index]
            best_model.fit(X_train_fold, y_train_fold)
            y_pred_fold = best_model.predict(X_test_fold)
            cvrmsle_scores.append(rmsle(y_test_fold, y_pred_fold))
        best_params = best_model.get_params()
    # Entrenar el modelo directamente si no se requiere ajuste ni validación cruzada
    else:
        best_model = models[model_type]
        best_model.set_params(**hyperparameters)
        best_model.fit(X_train, y_train)
        best_params = best_model.get_params()

    return best_model, best_params

# Función para cargar un modelo desde un archivo
def load_model(model_filename):
    return joblib.load(model_filename)

# Función para realizar predicciones y calcular métricas
def predict_sales(best_model, dataset, debug_logger):
    predictions = {}
    # Iterar por cada mes del año 2024 para realizar predicciones
    for month in range(1, 13):
        X_predict = dataset[(dataset["month"] == month) & (dataset["selling_date"].dt.year == 2024)]
        if X_predict.empty:
            continue
        X_predict_values = X_predict[["product_id", "selling_price", "category_id", "branch_id", "month", "weekday", "moving_avg_7d", "moving_avg_30d"]].values
        predicted_sales = best_model.predict(X_predict_values)
        for i, (index, row) in enumerate(X_predict.iterrows()):
            product_id = int(row["product_id"])
            y_pred = float(predicted_sales[i])
            if product_id not in predictions:
                predictions[product_id] = {}
            predictions[product_id][month] = y_pred

    # Calcular métricas generales y por mes
    real_sales_2024 = dataset[(dataset["selling_date"].dt.year == 2024)][["product_id", "month", "sold_quantity"]]
    all_actuals = []
    all_preds = []
    rmsle_per_month = {}
    wape_per_month = {}
    mad_per_month = {}
    mape_per_month = {}
    mse_per_month = {}
    r2_per_month = {}

    if not real_sales_2024.empty:
        rmsle_per_product = {}
        wape_per_product = {}
        for product_id in predictions.keys():
            actuals = []
            preds = []
            for month in range(1, 13):
                y_real = real_sales_2024[(real_sales_2024["product_id"] == product_id) & (real_sales_2024["month"] == month)]["sold_quantity"].sum()
                y_pred = predictions[product_id].get(month, 0)
                actuals.append(y_real)
                preds.append(y_pred)
                all_actuals.append(y_real)
                all_preds.append(y_pred)
            rmsle_per_product[product_id] = rmsle(actuals, preds)
            wape_per_product[product_id] = wape(actuals, preds)

        # Calcular métricas por mes
        for month in range(1, 13):
            month_actuals = np.array([real_sales_2024[(real_sales_2024["product_id"] == product_id) & (real_sales_2024["month"] == month)]["sold_quantity"].sum() for product_id in predictions.keys()])
            month_preds = np.array([predictions[product_id].get(month, 0) for product_id in predictions.keys()])
            rmsle_per_month[month] = rmsle(month_actuals, month_preds)
            wape_per_month[month] = wape(month_actuals, month_preds)
            mad_per_month[month] = mad(month_actuals, month_preds)
            mape_per_month[month] = mape(month_actuals, month_preds)
            mse_per_month[month] = mean_squared_error(month_actuals, month_preds)
            r2_per_month[month] = r2_score(month_actuals, month_preds)

    # Calcular métricas generales
    all_actuals = np.array(all_actuals)
    all_preds = np.array(all_preds)
    rmsle_general = rmsle(all_actuals, all_preds) if all_actuals.size else None
    wape_general = wape(all_actuals, all_preds) if all_actuals.size else None
    mad_general = mad(all_actuals, all_preds) if all_actuals.size else None
    mape_general = mape(all_actuals, all_preds) if all_actuals.size else None
    mse_general = mean_squared_error(all_actuals, all_preds) if all_actuals.size else None
    r2_general = r2_score(all_actuals, all_preds) if all_actuals.size else None

    metrics = {
        "rmsle_per_product": rmsle_per_product,
        "wape_per_product": wape_per_product,
        "rmsle_per_month": rmsle_per_month,
        "wape_per_month": wape_per_month,
        "mad_per_month": mad_per_month,
        "mape_per_month": mape_per_month,
        "mse_per_month": mse_per_month,
        "r2_per_month": r2_per_month,
        "rmsle_general": rmsle_general,
        "wape_general": wape_general,
        "mad_general": mad_general,
        "mape_general": mape_general,
        "mse_general": mse_general,
        "r2_general": r2_general
    }

    return predictions, metrics

# Función para realizar validación cruzada y calcular RMSLE por producto
def cross_validation_rmsle_per_product(model, X, y, cv=5):
    product_ids = np.unique(X[:, 0])
    rmsle_per_product = {}
    for product_id in product_ids:
        product_mask = X[:, 0] == product_id
        X_product = X[product_mask]
        y_product = y[product_mask]
        if len(y_product) < cv:
            continue
        kf = KFold(n_splits=cv)
        rmsle_scores = []
        for train_index, test_index in kf.split(X_product):
            X_train_fold, X_test_fold = X_product[train_index], X_product[test_index]
            y_train_fold, y_test_fold = y_product[train_index], y_product[test_index]
            fold_model = model.__class__(**model.get_params())
            fold_model.fit(X_train_fold, y_train_fold)
            y_pred_fold = fold_model.predict(X_test_fold)
            rmsle_value = rmsle(y_test_fold, y_pred_fold)
            rmsle_scores.append(rmsle_value)
        rmsle_per_product[product_id] = {
            "mean_cvrmsle": np.mean(rmsle_scores),
            "std_cvrmsle": np.std(rmsle_scores)
        }
    return rmsle_per_product
