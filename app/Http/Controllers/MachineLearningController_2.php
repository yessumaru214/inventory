<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Product; // importar el modelo Product

class MachineLearningController_2 extends Controller
{
    /**
     * Muestra la vista inicial sin resultados.
     */
    public function index()
    {
        return view('machine_learning_2.index', [
            'result' => null,
            'error' => null,
            'output' => null,
            'productNames' => [], // Pasar un array vacío inicialmente
            'k_folds' => null // Inicializar k_folds como null
        ]);
    }

    /**
     * Maneja la solicitud de entrenamiento y predicción.
     */
    public function train(Request $request)
    {
        Log::info('✅ Inicio del proceso de entrenamiento mensual.');
        Log::info('📌 Solicitud recibida: ' . json_encode($request->all()));

        // Validar parámetros de entrada
        $request->validate([
            'model' => 'required|string|in:RandomForest,ExtraTrees,DecisionTree,GBM',
        ]);

        // Ruta del archivo CSV con los datos
        $csvPath = storage_path("app/public/dataset.csv");

        if (!file_exists($csvPath)) {
            return response()->json(['error' => "🚨 El archivo de entrenamiento no existe."], 400);
        }

        try {
            // Leer y validar archivo CSV
            $file = fopen($csvPath, 'r');
            $headers = fgetcsv($file);

            if (!$headers) {
                throw new \Exception('🚨 El archivo CSV está vacío o tiene un formato inválido.');
            }

            // Validar columnas requeridas en el CSV
            $requiredColumns = ["selling_date", "product_id", "sold_quantity", "selling_price", "category_id", "branch_id", "month", "weekday", "moving_avg_7d", "moving_avg_30d"];
            if (array_diff($requiredColumns, $headers)) {
                throw new \Exception("🚨 El archivo CSV no contiene las columnas necesarias: " . json_encode($requiredColumns));
            }

            $headers = array_map('trim', $headers);
            Log::info('📌 Columnas encontradas en el CSV: ' . json_encode($headers));

            // Cargar datos del CSV en un array asociativo
            $dataset = [];
            while (($row = fgetcsv($file)) !== false) {
                $dataset[] = array_combine($headers, array_map('trim', $row));
            }
            fclose($file);

            if (empty($dataset)) {
                throw new \Exception("🚨 No hay datos disponibles en el CSV.");
            }

            Log::info("✅ Datos cargados correctamente. Registros: " . count($dataset));
        } catch (\Exception $e) {
            Log::error('❌ Error al procesar el archivo CSV: ' . $e->getMessage());
            return response()->json(['error' => '🚨 Error al procesar el archivo CSV.'], 400);
        }

        // Llamada a la API de predicción
        try {
            $apiUrl = env('ML_API_URL', 'http://127.0.0.1:5000') . '/predict';
            Log::info('📡 Llamando a la API de predicción en URL: ' . $apiUrl);

            $client = new Client();
            $hyperparameters = $request->has('hyperparameter_tuning') ? [] : $request->except(['_token', 'model', 'cross_validation', 'metrics_per_product', 'hyperparameter_tuning']);
            $response = $client->post($apiUrl, [
                'json' => [
                    'dataset_path' => $csvPath,
                    'model' => $request->input('model'),
                    'cross_validation' => $request->has('cross_validation'), // Pasar el parámetro de validación cruzada
                    'metrics_per_product' => $request->has('metrics_per_product'), // Pasar el parámetro de métricas por producto
                    'hyperparameter_tuning' => $request->has('hyperparameter_tuning'), // Pasar el parámetro de ajuste de hiperparámetros
                    'hyperparameters' => $hyperparameters, // Pasar los valores de los hiperparámetros solo si el ajuste no está habilitado
                    'k_folds' => $request->has('cross_validation') ? 5 : null // Establecer el número de k-folds utilizados en la validación cruzada solo si la validación cruzada está habilitada
                ]
            ]);

            $output = json_decode($response->getBody(), true);
            //Log::info('📩 Respuesta de la API recibida.', ['output' => $output]);

            // Validar respuesta de la API
            if (!isset($output['predictions']) || !is_array($output['predictions'])) {
                Log::error('❌ Error: La API no devolvió datos válidos.');
                return view('machine_learning_2.index', [
                    'result' => [],
                    'error' => '🚨 La API no devolvió datos válidos.',
                    'productNames' => [],
                    'metrics_per_product' => $request->has('metrics_per_product'),
                    'cross_validation' => $request->has('cross_validation'),
                    'hyperparameter_tuning' => $request->has('hyperparameter_tuning'),
                    'k_folds' => $request->has('cross_validation') ? 5 : null 
                ]);
            }

            // Obtener nombres de productos
            
            $productIds = array_keys($output['predictions']);// Obtener los IDs de los productos de las predicciones
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');// Consultar los productos en la base de datos usando los IDs obtenidos
            // Crear un array asociativo con los nombres de los productos, usando los IDs como claves
            $productNames = $products->mapWithKeys(function ($product) {
                return [$product->id => $product->product_name];
            });

            return view('machine_learning_2.index', [
                'result' => $output,
                'error' => null,
                'model' => $request->input('model'),
                'productNames' => $productNames,
                'avgRmslePerProduct' => $output['avg_rmsle_per_product'] ?? [], // Asegúrate de que la clave siempre esté presente
                'cvrmslePerProduct' => $output['cvrmsle_per_product'] ?? [], // Pasar los nuevos datos a la vista
                'rmslePerMonth' => $output['rmsle_per_month'] ?? [], // Pasar los nuevos datos a la vista
                'wapePerMonth' => $output['wape_per_month'] ?? [], // Pasar los nuevos datos a la vista
                'metrics_per_product' => $request->has('metrics_per_product'), // Pasar el parámetro a la vista
                'cross_validation' => $request->has('cross_validation'), // Pasar el parámetro a la vista
                'hyperparameter_tuning' => $request->has('hyperparameter_tuning'), // Pasar el parámetro a la vista
                'k_folds' => $request->has('cross_validation') ? 5 : null // Pasar el número de k-folds a la vista solo si la validación cruzada está habilitada
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al llamar a la API de predicción.', ['error' => $e->getMessage()]);
            return view('machine_learning_2.index', [
                'result' => [],
                'error' => '🚨 Error al llamar a la API de predicción.',
                'productNames' => [],
                'metrics_per_product' => $request->has('metrics_per_product'),
                'cross_validation' => $request->has('cross_validation'),
                'hyperparameter_tuning' => $request->has('hyperparameter_tuning')
            ]);
        }
    }
}
