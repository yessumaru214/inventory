import sys
import json
from sklearn.ensemble import ExtraTreesRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_squared_error, mean_absolute_percentage_error

# Verificar que se pasen todos los argumentos necesarios
if len(sys.argv) != 6:
    error_response = {
        "status": "error",
        "message": "Se requieren 5 argumentos: <ruta_X> <ruta_y> <n_estimators> <max_depth> <random_state>"
    }
    print(json.dumps(error_response))
    sys.exit(1)

# Leer argumentos desde la línea de comandos
try:
    X_path = sys.argv[1]
    y_path = sys.argv[2]
    n_estimators = int(sys.argv[3])
    max_depth = int(sys.argv[4])
    random_state = int(sys.argv[5])
except ValueError as e:
    error_response = {
        "status": "error",
        "message": f"Error al procesar argumentos: {str(e)}"
    }
    print(json.dumps(error_response))
    sys.exit(1)

# Cargar los datos desde los archivos
try:
    with open(X_path, 'r') as f:
        X = json.load(f)
    with open(y_path, 'r') as f:
        y = json.load(f)
except Exception as e:
    error_response = {
        "status": "error",
        "message": f"Error al leer los archivos: {str(e)}"
    }
    print(json.dumps(error_response))
    sys.exit(1)

# Preprocesar los datos
try:
    processed_X = [
        [
            row["stock_quantity"],
            row["current_quantity"],
            row["buying_price"],
            row["selling_price"],
            row["sold_quantity"],
            row["days_since_last_purchase"]
        ] for row in X
    ]
except KeyError as e:
    error_response = {
        "status": "error",
        "message": f"Faltan claves en los datos: {str(e)}"
    }
    print(json.dumps(error_response))
    sys.exit(1)

# Dividir datos en entrenamiento y prueba
try:
    X_train, X_test, y_train, y_test = train_test_split(processed_X, y, test_size=0.2, random_state=random_state)
except Exception as e:
    error_response = {
        "status": "error",
        "message": f"Error al dividir los datos: {str(e)}"
    }
    print(json.dumps(error_response))
    sys.exit(1)

# Crear y entrenar el modelo
try:
    model = ExtraTreesRegressor(
        n_estimators=n_estimators,
        max_depth=max_depth,
        random_state=random_state
    )
    model.fit(X_train, y_train)
except Exception as e:
    error_response = {
        "status": "error",
        "message": f"Error al entrenar el modelo: {str(e)}"
    }
    print(json.dumps(error_response))
    sys.exit(1)

# Evaluar el modelo
try:
    y_pred = model.predict(X_test)
    mse = mean_squared_error(y_test, y_pred)
    mape = mean_absolute_percentage_error(y_test, y_pred)
    mad = sum(abs(y_i - y_pred_i) for y_i, y_pred_i in zip(y_test, y_pred)) / len(y_test)
    predicted_quantities = model.predict(processed_X).tolist()
except Exception as e:
    error_response = {
        "status": "error",
        "message": f"Error al evaluar el modelo: {str(e)}"
    }
    print(json.dumps(error_response))
    sys.exit(1)

# Salida final
success_response = {
    "status": "success",
    "message": "Modelo entrenado con éxito",
    "MSE": mse,
    "MAPE": mape,
    "MAD": mad,
    "predicted_quantities": predicted_quantities
}
print(json.dumps(success_response))
