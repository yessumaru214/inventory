import pandas as pd
from sklearn.tree import DecisionTreeRegressor
from sklearn.model_selection import train_test_split, GridSearchCV
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import mean_absolute_error, mean_squared_error
import joblib
import numpy as np

# *** ETAPA 1: CARGA Y PREPARACIÓN DE LOS DATOS ***

# Cargar datos de compras y ventas del 2023
# Estos datos se usarán para entrenar el modelo de predicción
compras_2023 = pd.read_excel(r'C:\laragon\www\inventory\ml_model\compras_2023.xlsx')
ventas_2023 = pd.read_excel(r'C:\laragon\www\inventory\ml_model\ventas_2023.xlsx')

# Renombrar columnas en compras para estandarizar los nombres
compras_2023.rename(columns={"purchased_quantity": "current_quantity_compras"}, inplace=True)

# Mostrar una muestra de los datos cargados para verificar
print("Datos de ventas 2023 cargados:")
print(ventas_2023.head())
print("Datos de compras 2023 cargados:")
print(compras_2023.head())

# Fusionar datos de ventas y compras en un solo DataFrame
# La fusión se realiza por claves comunes como product_id, category_id, month, year
data_2023 = pd.merge(ventas_2023, compras_2023, on=["product_id", "category_id", "month", "year"], how="inner")

# Crear columnas adicionales para identificar sobrestock y desabastecimiento
data_2023['overstock'] = data_2023['current_quantity_compras'] - data_2023['sold_quantity']
data_2023['understock'] = data_2023['sold_quantity'] - data_2023['current_quantity_compras']
data_2023['status'] = data_2023.apply(
    lambda row: 'overstock' if row['overstock'] > 0 else ('understock' if row['understock'] > 0 else 'balanced'), axis=1)

# Seleccionar características (X) y la variable objetivo (y) para el modelo
features = ['product_id', 'category_id', 'current_quantity_compras', 'month', 'year']
target = 'sold_quantity'

X = data_2023[features]  # Variables independientes
y = data_2023[target]  # Variable dependiente

# Normalización de las características para garantizar que todas tengan el mismo peso en el modelo
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Dividir los datos en conjuntos de entrenamiento (80%) y prueba (20%)
X_train, X_test, y_train, y_test = train_test_split(X_scaled, y, test_size=0.2, random_state=42)

# *** ETAPA 2: ENTRENAMIENTO DEL MODELO ***

# Configuración de los hiperparámetros para GridSearchCV
param_grid = {
    'max_depth': [3, 5, 7, 10, None],  # Control de la profundidad máxima del árbol
    'min_samples_split': [2, 5, 10, 15, 20],  # Mínimo de muestras para dividir un nodo
    'min_samples_leaf': [1, 2, 5, 10, 15],  # Mínimo de muestras por hoja
    'max_features': [None, 'sqrt', 'log2']  # Límite de características usadas en cada división
}

# Búsqueda de hiperparámetros óptimos usando validación cruzada
grid_search = GridSearchCV(
    DecisionTreeRegressor(random_state=42),
    param_grid,
    cv=5,  # Validación cruzada con 5 particiones
    scoring='neg_mean_absolute_error'  # Métrica de evaluación: MAE
)
grid_search.fit(X_train, y_train)

# Seleccionar el mejor modelo encontrado por GridSearchCV
best_model = grid_search.best_estimator_
print("Mejores parámetros encontrados:", grid_search.best_params_)

# Evaluar el mejor modelo en el conjunto de prueba
y_pred = best_model.predict(X_test)
mae = mean_absolute_error(y_test, y_pred)
mse = mean_squared_error(y_test, y_pred)
mad = np.mean(np.abs(y_test - y_pred))
mape = np.mean(np.abs((y_test - y_pred) / y_test)) * 100

# Mostrar métricas iniciales del modelo
print(f"Error Absoluto Medio (MAE): {mae}")
print(f"Error Cuadrático Medio (MSE): {mse}")
print(f"Desviación Media Absoluta (MAD): {mad}")
print(f"Error Porcentual Absoluto Medio (MAPE): {mape}%")
print(f"Profundidad del árbol: {best_model.get_depth()}")

# Guardar el modelo entrenado y el escalador
joblib.dump(best_model, r'C:\laragon\www\inventory\ml_model\decision_tree_model.pkl')
joblib.dump(scaler, r'C:\laragon\www\inventory\ml_model\scaler.pkl')
print("Modelo guardado con éxito.")

# *** ETAPA 3: GENERACIÓN DE PREDICCIONES PARA 2024 ***

# Cargar ventas reales del 2024 para evaluar el modelo
ventas_2024 = pd.read_excel(r'C:\laragon\www\inventory\ml_model\ventas_2024.xlsx')

# Crear predicciones basadas en los datos de compras del 2023
data_2024 = compras_2023.copy()
data_2024['year'] = 2024  # Cambiar el año a 2024
data_2024_scaled = scaler.transform(data_2024[features])
data_2024['predicted_purchases'] = best_model.predict(data_2024_scaled)

# Guardar las predicciones generadas para 2024 en un archivo Excel
predictions_path = r'C:\laragon\www\inventory\ml_model\compras_predichas_2024.xlsx'
data_2024.to_excel(predictions_path, index=False)
print(f"Predicciones guardadas en: {predictions_path}")

# Combinar predicciones con ventas reales del 2024 para comparar resultados
data_comparison = data_2024.merge(ventas_2024, on=["product_id", "category_id", "month", "year"], how="inner")

# Filtro: Excluir productos con ventas reales menores o iguales a 0
data_comparison = data_comparison[data_comparison['sold_quantity'] > 0]

# Calcular métricas finales de evaluación
mae_final = mean_absolute_error(data_comparison['sold_quantity'], data_comparison['predicted_purchases'])
mse_final = mean_squared_error(data_comparison['sold_quantity'], data_comparison['predicted_purchases'])
mad_final = np.mean(np.abs(data_comparison['sold_quantity'] - data_comparison['predicted_purchases']))
mape_final = np.mean(np.abs((data_comparison['sold_quantity'] - data_comparison['predicted_purchases']) / data_comparison['sold_quantity'])) * 100

# Calcular WAPE (Weighted Absolute Percentage Error)
total_sales = data_comparison['sold_quantity'].sum()
wape_final = (data_comparison['sold_quantity'] - data_comparison['predicted_purchases']).abs().sum() / total_sales * 100

# Mostrar métricas finales
print("Métricas finales comparando predicciones con ventas reales:")
print(f"Error Absoluto Medio (MAE): {mae_final}")
print(f"Error Cuadrático Medio (MSE): {mse_final}")
print(f"Desviación Media Absoluta (MAD): {mad_final}")
print(f"Error Porcentual Absoluto Medio (MAPE): {mape_final}%")
print(f"WAPE: {wape_final}%")
