import pandas as pd
from sqlalchemy import create_engine
from sklearn.tree import DecisionTreeRegressor
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import mean_absolute_error, mean_squared_error
import joblib  # Para guardar y cargar el modelo

# Configuración de la conexión a la base de datos
db_user = "root"
db_password = 
db_host = "localhost"
db_name = "db_inventory"

engine = create_engine(f"mysql+pymysql://{db_user}:{db_password}@{db_host}/{db_name}")

# Paso 1: Cargar los datos desde la base de datos
query = """
SELECT 
    s.sell_date, 
    sd.product_id, 
    sd.sold_quantity, 
    p.category_id, 
    st.buying_price, 
    st.selling_price, 
    st.current_quantity 
FROM 
    sells s
INNER JOIN 
    sell_details sd ON s.id = sd.sell_id
INNER JOIN 
    products p ON sd.product_id = p.id
INNER JOIN 
    stocks st ON st.product_id = p.id
"""

print("Cargando datos desde la base de datos...")
df = pd.read_sql(query, con=engine)

# Paso 2: Limpieza de datos
print("Limpiando datos...")
df['sell_date'] = pd.to_datetime(df['sell_date'])
df['month'] = df['sell_date'].dt.month
df['year'] = df['sell_date'].dt.year
df.fillna(0, inplace=True)
df['category_id'] = df['category_id'].astype('category').cat.codes
df.drop(columns=['sell_date'], inplace=True)

# Paso 3: Preparar características y variable objetivo
print("Preparando características y variable objetivo...")
features = ['product_id', 'category_id', 'buying_price', 'selling_price', 'current_quantity', 'month', 'year']
target = 'sold_quantity'

X = df[features]
y = df[target]

# Normalizar las características
scaler = StandardScaler()
X = scaler.fit_transform(X)

# Paso 4: Dividir los datos en entrenamiento y prueba
print("Dividiendo datos en conjuntos de entrenamiento y prueba...")
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Paso 5: Entrenar el modelo
print("Entrenando el modelo...")
model = DecisionTreeRegressor(random_state=42)
model.fit(X_train, y_train)

# Paso 6: Evaluar el modelo
print("Evaluando el modelo...")
y_pred = model.predict(X_test)
mae = mean_absolute_error(y_test, y_pred)
mse = mean_squared_error(y_test, y_pred)
print(f"Error Absoluto Medio (MAE): {mae}")
print(f"Error Cuadrático Medio (MSE): {mse}")

# Paso 7: Guardar el modelo entrenado
print("Guardando el modelo entrenado...")
joblib.dump(model, 'decision_tree_model.pkl')
joblib.dump(scaler, 'scaler.pkl')

# Paso 8: Cargar el modelo y realizar predicciones (opcional)
print("Cargando el modelo y realizando predicciones de prueba...")
loaded_model = joblib.load('decision_tree_model.pkl')
loaded_scaler = joblib.load('scaler.pkl')

# Realizar una predicción de ejemplo
example_data = [[1, 2, 50.0, 70.0, 100, 12, 2024]]  # Ejemplo de datos
example_data_scaled = loaded_scaler.transform(example_data)
prediction = loaded_model.predict(example_data_scaled)
print(f"Predicción para el ejemplo: {prediction[0]}")
