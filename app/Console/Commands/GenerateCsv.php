<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateCsv extends Command
{
    protected $signature = 'generate:csv';
    protected $description = 'Generar un CSV con datos optimizados para Machine Learning, centrado en la predicción de la demanda';

    public function handle()
    {
        try {
            // Obtener datos asegurando la correcta relación entre ventas
            $data = DB::select("
                SELECT 
                    STR_TO_DATE(sd.selling_date, '%Y-%m-%d') AS selling_date,
                    sd.product_id,
                    sd.sold_quantity,
                    sd.sold_price AS selling_price,
                    sd.category_id,
                    s.branch_id,
                    MONTH(STR_TO_DATE(sd.selling_date, '%Y-%m-%d')) AS month,
                    DAYOFWEEK(STR_TO_DATE(sd.selling_date, '%Y-%m-%d')) AS weekday,
                    (
                        SELECT AVG(sold_quantity) 
                        FROM sell_details 
                        WHERE product_id = sd.product_id 
                        AND STR_TO_DATE(selling_date, '%Y-%m-%d') BETWEEN DATE_SUB(STR_TO_DATE(sd.selling_date, '%Y-%m-%d'), INTERVAL 7 DAY)
                        AND STR_TO_DATE(sd.selling_date, '%Y-%m-%d')
                    ) AS moving_avg_7d,
                    (
                        SELECT AVG(sold_quantity)
                        FROM sell_details
                        WHERE product_id = sd.product_id
                        AND STR_TO_DATE(selling_date, '%Y-%m-%d') BETWEEN DATE_SUB(STR_TO_DATE(sd.selling_date, '%Y-%m-%d'), INTERVAL 30 DAY)
                        AND STR_TO_DATE(sd.selling_date, '%Y-%m-%d')
                    ) AS moving_avg_30d
                FROM sells s
                JOIN sell_details sd ON s.id = sd.sell_id
                ORDER BY sd.selling_date ASC
            ");

            if (empty($data)) {
                $this->error("⚠️ No se encontraron datos en la base de datos.");
                return;
            }

            // Crear contenido del CSV con encabezados
            $csvData = "selling_date,product_id,sold_quantity,selling_price,category_id,branch_id,month,weekday,moving_avg_7d,moving_avg_30d\n";

            // Iterar sobre los datos obtenidos
            foreach ($data as $row) { // Se agrega el bucle foreach para recorrer los resultados
                $csvData .= sprintf(
                    "%s,%d,%d,%.2f,%d,%d,%d,%d,%.2f,%.2f\n",
                    $row->selling_date, 
                    $row->product_id,
                    $row->sold_quantity,
                    $row->selling_price ?? 0, // Asegura que no haya valores NULL
                    $row->category_id,
                    $row->branch_id,
                    $row->month,
                    $row->weekday,
                    $row->moving_avg_7d ?? 0,
                    $row->moving_avg_30d ?? 0
                );
            }

            // Guardar el archivo CSV en la carpeta pública
            $filePath = 'public/dataset.csv';
            Storage::put($filePath, $csvData);

            $this->info("✅ CSV generado exitosamente en storage/app/{$filePath}");

        } catch (\Exception $e) {
            $this->error("❌ Error al generar el CSV: " . $e->getMessage());
        }
    }
}
