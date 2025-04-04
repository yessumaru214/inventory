import numpy as np
from sklearn.model_selection import KFold

# Calcular el RMSLE (Root Mean Squared Logarithmic Error) entre valores reales y predichos
def rmsle(y_true, y_pred):
    y_true = np.array(y_true)
    y_pred = np.array(y_pred)
    log_true = np.log1p(y_true)  # Aplicar log(1 + x) a los valores reales
    log_pred = np.log1p(y_pred)  # Aplicar log(1 + x) a los valores predichos
    return np.sqrt(np.mean((log_true - log_pred) ** 2))  # Calcular la raíz cuadrada del error cuadrático medio

# Calcular el WAPE (Weighted Absolute Percentage Error) entre valores reales y predichos
def wape(y_true, y_pred):
    y_true = np.array(y_true)
    y_pred = np.array(y_pred)
    total_demand = np.sum(y_true)  # Sumar la demanda total
    if total_demand == 0:  # Evitar división por cero
        return 0
    return np.sum(np.abs(y_true - y_pred)) / total_demand * 100  # Calcular el error absoluto ponderado

# Calcular el MAD (Mean Absolute Deviation) entre valores reales y predichos
def mad(y_true, y_pred):
    y_true = np.array(y_true)
    y_pred = np.array(y_pred)
    return np.mean(np.abs(y_true - y_pred))  # Calcular la desviación absoluta media

# Calcular el MAPE (Mean Absolute Percentage Error) entre valores reales y predichos
def mape(y_true, y_pred):
    y_true = np.array(y_true)
    y_pred = np.array(y_pred)
    non_zero_mask = y_true != 0  # Evitar división por cero
    return np.mean(np.abs((y_true[non_zero_mask] - y_pred[non_zero_mask]) / y_true[non_zero_mask])) * 100

# Realizar validación cruzada y calcular RMSLE por producto
def cross_validation_rmsle_per_product(model, X, y, cv=5):
    product_ids = np.unique(X[:, 0])  # Obtener IDs únicos de productos
    rmsle_per_product = {}
    for product_id in product_ids:
        product_mask = X[:, 0] == product_id  # Filtrar datos por producto
        X_product = X[product_mask]
        y_product = y[product_mask]
        if len(y_product) < cv:  # Saltar productos con datos insuficientes para validación cruzada
            continue
        kf = KFold(n_splits=cv)  # Configurar validación cruzada con K folds
        rmsle_scores = []
        for train_index, test_index in kf.split(X_product):
            X_train_fold, X_test_fold = X_product[train_index], X_product[test_index]
            y_train_fold, y_test_fold = y_product[train_index], y_product[test_index]
            fold_model = model.__class__(**model.get_params())  # Crear una nueva instancia del modelo
            fold_model.fit(X_train_fold, y_train_fold)  # Entrenar el modelo en el fold actual
            y_pred_fold = fold_model.predict(X_test_fold)  # Predecir valores en el fold de prueba
            rmsle_value = rmsle(y_test_fold, y_pred_fold)  # Calcular RMSLE para el fold
            rmsle_scores.append(rmsle_value)
        rmsle_per_product[product_id] = {
            "mean_cvrmsle": np.mean(rmsle_scores),  # Promedio de RMSLE para el producto
            "std_cvrmsle": np.std(rmsle_scores)  # Desviación estándar de RMSLE para el producto
        }
    return rmsle_per_product
