import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor, ExtraTreesRegressor
from sklearn.metrics import mean_squared_error, mean_absolute_error
from sklearn.preprocessing import LabelEncoder, StandardScaler
import os

# Ruta del archivo CSV
csv_path = r"C:\laragon\www\inventory\public\storage\training_data_2023.csv"

# Cargar datos
data = pd.read_csv(csv_path)

# Procesar columnas no numéricas
categorical_columns = data.select_dtypes(include=['object']).columns
for col in categorical_columns:
    encoder = LabelEncoder()
    data[col] = encoder.fit_transform(data[col])

# Definir columna objetivo y características
target_column = 'sold_quantity'
features = data.drop(columns=['product_id', target_column])

# Variables objetivo y características
X = features
y = data[target_column]

# Escalar los datos
scaler = StandardScaler()
X = scaler.fit_transform(X)

# Dividir los datos en conjuntos de entrenamiento y prueba
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Hiperparámetros comunes para los modelos
hyperparameters = {
    'RandomForest': {
        'n_estimators': [50, 100, 200],
        'max_depth': [5, 10, 20],
        'min_samples_split': [2, 5],
        'min_samples_leaf': [1, 2]
    },
    'ExtraTrees': {
        'n_estimators': [50, 100, 200],
        'max_depth': [5, 10, 20],
        'min_samples_split': [2, 5],
        'min_samples_leaf': [1, 2]
    }
}

# Crear un archivo para registrar los resultados
output_file = r"C:\laragon\www\inventory\ml_model\hyperparameter_results.csv"
os.makedirs(os.path.dirname(output_file), exist_ok=True)
with open(output_file, 'w') as f:
    f.write("Model,Hyperparameters,MSE,MAD,MAPE\n")

# Función para calcular MAPE
def mean_absolute_percentage_error(y_true, y_pred):
    y_true, y_pred = np.array(y_true), np.array(y_pred)
    nonzero_mask = y_true != 0  # Evitar división por cero
    if nonzero_mask.sum() == 0:
        return float('inf')  # Si todos los valores reales son cero
    return np.mean(np.abs((y_true[nonzero_mask] - y_pred[nonzero_mask]) / y_true[nonzero_mask])) * 100

# Iterar sobre los modelos y sus hiperparámetros
for model_name, params in hyperparameters.items():
    for n_estimators in params['n_estimators']:
        for max_depth in params['max_depth']:
            for min_samples_split in params['min_samples_split']:
                for min_samples_leaf in params['min_samples_leaf']:
                    try:
                        # Crear y entrenar el modelo
                        if model_name == 'RandomForest':
                            model = RandomForestRegressor(
                                n_estimators=n_estimators,
                                max_depth=max_depth,
                                min_samples_split=min_samples_split,
                                min_samples_leaf=min_samples_leaf,
                                random_state=42
                            )
                        elif model_name == 'ExtraTrees':
                            model = ExtraTreesRegressor(
                                n_estimators=n_estimators,
                                max_depth=max_depth,
                                min_samples_split=min_samples_split,
                                min_samples_leaf=min_samples_leaf,
                                random_state=42
                            )
                        
                        model.fit(X_train, y_train)

                        # Realizar predicciones
                        y_pred = model.predict(X_test)

                        # Calcular métricas
                        mse = mean_squared_error(y_test, y_pred)
                        mad = mean_absolute_error(y_test, y_pred)
                        mape = mean_absolute_percentage_error(y_test, y_pred)

                        # Guardar resultados
                        with open(output_file, 'a') as f:
                            f.write(f"{model_name},n_estimators={n_estimators},max_depth={max_depth},min_samples_split={min_samples_split},min_samples_leaf={min_samples_leaf},{mse:.2f},{mad:.2f},{mape:.2f}\n")
                    except Exception as e:
                        print(f"Error en {model_name} con n_estimators={n_estimators}, max_depth={max_depth}, min_samples_split={min_samples_split}, min_samples_leaf={min_samples_leaf}: {e}")

print(f"Resultados guardados en {output_file}")
