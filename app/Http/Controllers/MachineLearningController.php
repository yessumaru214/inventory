<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class MachineLearningController extends Controller
{
    /**
     * Muestra la vista inicial sin resultados.
     */
    public function index()
    {
        return view('machine_learning.index', [
            'result' => null,
            'error' => null,
            'output' => null,
        ]);
    }

    /**
     * Maneja la solicitud de entrenamiento y predicción.
     */
    public function train(Request $request)
    {
        Log::info('✅ Inicio del proceso de entrenamiento mensual.');
        Log::info('📌 Solicitud recibida: ' . json_encode($request->all()));

        // 🔹 Validar parámetros de entrada
        $request->validate([
            'model' => 'required|string|in:RandomForest,ExtraTrees,DecisionTree,GBM',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->input('year');
        $month = str_pad($request->input('month'), 2, '0', STR_PAD_LEFT); // Asegurar formato 2 dígitos

        // 📂 Ruta del archivo CSV con los datos
        $csvPath = storage_path("app/public/dataset.csv");

        if (!file_exists($csvPath)) {
            return response()->json(['error' => "🚨 El archivo de entrenamiento no existe."], 400);
        }

        try {
            // 🔹 Leer y validar archivo CSV
            $file = fopen($csvPath, 'r');
            $headers = fgetcsv($file);

            if (!$headers) {
                throw new \Exception('🚨 El archivo CSV está vacío o tiene un formato inválido.');
            }

            // 🔹 Validar columnas requeridas en el CSV
            $requiredColumns = ["selling_date", "product_id", "sold_quantity", "selling_price", "category_id", "branch_id", "month", "weekday", "moving_avg_7d", "moving_avg_30d"];
            if (array_diff($requiredColumns, $headers)) {
                throw new \Exception("🚨 El archivo CSV no contiene las columnas necesarias: " . json_encode($requiredColumns));
            }

            $headers = array_map('trim', $headers);
            Log::info('📌 Columnas encontradas en el CSV: ' . json_encode($headers));

            // 🔹 Cargar datos del CSV en un array asociativo
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

        // 🔹 Filtrar datos para entrenamiento (antes del mes seleccionado)
        $trainData = array_filter($dataset, function ($row) use ($year, $month) {
            return isset($row['selling_date']) && substr($row['selling_date'], 0, 7) < "{$year}-{$month}";
        });

        // 🔹 Filtrar datos del mes seleccionado (datos reales para comparar)
        $actualData = array_filter($dataset, function ($row) use ($year, $month) {
            return isset($row['selling_date']) && substr($row['selling_date'], 0, 7) === "{$year}-{$month}";
        });

        if (empty($trainData)) {
            Log::error("❌ No hay datos de entrenamiento antes de {$year}-{$month}.");
            return response()->json(['error' => "🚨 No hay datos de entrenamiento previos a {$year}-{$month}."], 400);
        }

        Log::info("✅ Datos filtrados para entrenamiento: " . count($trainData));

        // 🔹 Agrupar datos de entrenamiento por producto
        $groupedData = [];
        foreach ($trainData as $row) {
            $productId = intval($row['product_id'] ?? 0);
            if ($productId === 0) continue;

            if (!isset($groupedData[$productId])) {
                $groupedData[$productId] = [
                    "product_id" => $productId,
                    "selling_price" => floatval($row['selling_price'] ?? 0),
                    "category_id" => intval($row['category_id'] ?? 0),
                    "branch_id" => intval($row['branch_id'] ?? 0),
                    "month" => intval($row['month'] ?? 0),
                    "weekday" => intval($row['weekday'] ?? 0),
                    "moving_avg_7d" => floatval($row['moving_avg_7d'] ?? 0),
                    "moving_avg_30d" => floatval($row['moving_avg_30d'] ?? 0),
                    "sold_quantity" => floatval($row['sold_quantity']),
                ];
            }
        }

        if (empty($groupedData)) {
            Log::error('❌ No se encontraron productos válidos en los datos de entrenamiento.');
            return response()->json(['error' => '🚨 No se encontraron productos válidos en los datos.'], 400);
        }

        Log::info("✅ Total productos en entrenamiento: " . count($groupedData));

        // 🔹 Llamada a la API de predicción
        try {
            $apiUrl = env('ML_API_URL', 'http://127.0.0.1:5000') . '/predict';
            Log::info('📡 Llamando a la API de predicción en URL: ' . $apiUrl);

            $client = new Client();
            $response = $client->post($apiUrl, [
                'json' => [
                    'dataset_path' => $csvPath,
                    'model' => $request->input('model'),
                    'predict_year' => $year,
                    'predict_month' => $month
                ]
            ]);

            $output = json_decode($response->getBody(), true);
            //Log::info('📩 Respuesta de la API recibida.', ['output' => $output]);

            // 🔹 Validar respuesta de la API
            if (!isset($output['product_metrics']) || !is_array($output['product_metrics'])) {
                Log::error('❌ Error: La API no devolvió datos válidos.');
                return view('machine_learning.index', [
                    'result' => [],
                    'error' => '🚨 La API no devolvió datos válidos.'
                ]);
            }

            // 🔹 Agregar datos reales a la predicción
            foreach ($output['product_metrics'] as $productId => &$metrics) {
                $actualSales = array_sum(array_column(array_filter($actualData, function ($row) use ($productId) {
                    return intval($row['product_id']) === intval($productId);
                }), 'sold_quantity'));

                $metrics['actual_sales'] = $actualSales;
            }

            return view('machine_learning.index', [
                'result' => $output,
                'error' => null,
                'model' => $request->input('model'),
                'year' => $year,
                'month' => $month
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error al llamar a la API de predicción.', ['error' => $e->getMessage()]);
            return view('machine_learning.index', [
                'result' => [],
                'error' => '🚨 Error al llamar a la API de predicción.'
            ]);
        }
    }
}
