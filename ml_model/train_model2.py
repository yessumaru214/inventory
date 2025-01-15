import pandas as pd
from sklearn.tree import DecisionTreeRegressor
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import mean_absolute_error, mean_squared_error
import joblib
import numpy as np

# Simula la carga de datos (puedes reemplazar con datos reales o una conexión a la base de datos)
data = {
    'product_id': [1, 2, 3, 4],
    'category_id': [1, 2, 1, 2],
    'buying_price': [50, 60, 70, 80],
    'selling_price': [100, 120, 140, 160],
    'current_quantity': [10, 20, 30, 40],
    'month': [1, 2, 3, 4],
    'year': [2023, 2023, 2023, 2023],
    'sold_quantity': [5, 10, 15, 20],
}
df = pd.DataFrame(data)

# Selección de características y objetivo
features = ['product_id', 'category_id', 'buying_price', 'selling_price', 'current_quantity', 'month', 'year']
target = 'sold_quantity'

X = df[features]
y = df[target]

# Normalización
scaler = StandardScaler()
X = scaler.fit_transform(X)

# División de datos en entrenamiento y prueba
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Configuración del modelo con profundidad máxima
model = DecisionTreeRegressor(random_state=42, max_depth=5)

# Entrenamiento del modelo
model.fit(X_train, y_train)

# Evaluación del modelo
y_pred = model.predict(X_test)

# Cálculo de métricas
mae = mean_absolute_error(y_test, y_pred)
mse = mean_squared_error(y_test, y_pred)
mad = np.mean(np.abs(y_test - y_pred))  # Desviación Media Absoluta (MAD)
mape = np.mean(np.abs((y_test - y_pred) / y_test)) * 100  # Error Porcentual Absoluto Medio (MAPE)

# Mostrar resultados
print(f"Error Absoluto Medio (MAE): {mae}")
print(f"Error Cuadrático Medio (MSE): {mse}")
print(f"Desviación Media Absoluta (MAD): {mad}")
print(f"Error Porcentual Absoluto Medio (MAPE): {mape}%")

# Mostrar la profundidad del árbol
print(f"Profundidad del árbol: {model.get_depth()}")

# Guardar el modelo y el escalador
joblib.dump(model, 'decision_tree_model.pkl')
joblib.dump(scaler, 'scaler.pkl')
print("Modelo guardado con éxito.")
