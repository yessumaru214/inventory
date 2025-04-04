import sys
import json
import pandas as pd
from sklearn.tree import DecisionTreeRegressor
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
import warnings  # Para controlar las advertencias


def process_input_data(input_data):
    """Procesa los datos JSON recibidos desde stdin."""
    try:
        # Extraer datos del JSON
        sell_details = input_data['sell_details']
        stocks = input_data['stocks']
        products = input_data['products']

        # Convertir a DataFrames
        sell_details_df = pd.DataFrame(sell_details)
        stocks_df = pd.DataFrame(stocks)
        products_df = pd.DataFrame(products)

        # Validar columnas necesarias
        required_columns = {
            'sell_details': ['stock_id', 'product_id', 'selling_date'],
            'stocks': ['id', 'stock_quantity', 'current_quantity', 'selling_price', 'buying_price'],
            'products': ['id']
        }
        for name, columns in required_columns.items():
            df = locals()[f"{name}_df"]
            for col in columns:
                if col not in df.columns:
                    raise Exception(f"Columna '{col}' no encontrada en {name}")

        # Filtrar datos por año
        sell_details_df['selling_date'] = pd.to_datetime(sell_details_df['selling_date'], errors='coerce')
        sell_details_df = sell_details_df[sell_details_df['selling_date'].dt.year == input_data['year']]

        # Fusionar sell_details con stocks
        merged_data = pd.merge(
            sell_details_df,
            stocks_df,
            left_on='stock_id',
            right_on='id',
            how='inner'
        )

        # Renombrar columnas para evitar conflictos
        merged_data.rename(columns={
            'product_id_x': 'product_id',  # Preservar el product_id original de sell_details
        }, inplace=True)

        # Fusionar con products
        final_data = pd.merge(
            merged_data,
            products_df,
            left_on='product_id',
            right_on='id',
            how='inner'
        )

        # Limpiar datos
        final_data.dropna(inplace=True)

        # Seleccionar características y objetivo
        X = final_data[['stock_quantity', 'current_quantity', 'selling_price', 'buying_price']]
        y = final_data['sold_quantity']

        return X, y
    except Exception as e:
        print(f"Error al procesar los datos: {e}", file=sys.stderr)
        sys.exit(1)


def train_model(X, y, model_type, params):
    """Entrena un modelo y evalúa su desempeño."""
    try:
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

        # Inicializar el modelo según el tipo
        if model_type == 'regression_tree':
            valid_params = {key: params[key] for key in ['max_depth'] if key in params}
            model = DecisionTreeRegressor(**valid_params)
        elif model_type == 'random_forest':
            valid_params = {key: params[key] for key in ['max_depth', 'n_estimators'] if key in params}
            model = RandomForestRegressor(**valid_params)
        else:
            raise ValueError(f"Modelo no soportado: {model_type}")

        # Entrenar el modelo
        model.fit(X_train, y_train)
        y_pred = model.predict(X_test)

        # Calcular métricas
        mae = mean_absolute_error(y_test, y_pred)
        mse = mean_squared_error(y_test, y_pred)
        r2 = r2_score(y_test, y_pred)

        return model, mae, mse, r2, y_pred
    except Exception as e:
        print(f"Error al entrenar el modelo: {e}", file=sys.stderr)
        sys.exit(1)


def main():
    """Punto de entrada principal."""
    try:
        # Redirigir advertencias a stderr
        warnings.simplefilter("always")
        warnings.filterwarnings("always", category=UserWarning, module="sklearn")

        print("Inicio de ejecución del script", file=sys.stderr)

        # Leer datos desde stdin
        input_data = json.load(sys.stdin)

        # Extraer parámetros
        year = input_data['year']
        model_type = input_data['model']
        params = input_data['params']

        # Procesar datos
        X, y = process_input_data(input_data)

        # Entrenar el modelo
        model, mae, mse, r2, y_pred = train_model(X, y, model_type, params)

        # Preparar resultados
        results = {
            'mae': mae,
            'mse': mse,
            'r2': r2 if not pd.isna(r2) else None,  # Corregir NaN
            'predictions': y_pred.tolist()
        }

        # Imprimir solo JSON como salida estándar
        print(json.dumps(results, indent=4))
    except Exception as e:
        print(f"Error en la ejecución: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
