import subprocess
import os
import time

# Definir la ruta del proyecto Laravel
laravel_path = "C:/laragon/www/inventory"

# Definir la ruta del archivo CSV generado por Laravel
csv_path = os.path.join(laravel_path, "storage", "app", "public", "dataset.csv")

# Verificar si la ruta del proyecto existe
if not os.path.exists(laravel_path):
    print(f"Error: La ruta {laravel_path} no existe.")
    exit(1)

# Cambiar el directorio de trabajo a Laravel
os.chdir(laravel_path)

# Comando de Artisan para generar el CSV
command = ["php", "artisan", "generate:csv"]

try:
    # Ejecutar el comando en la terminal con codificación UTF-8
    result = subprocess.run(command, check=True, text=True, capture_output=True, encoding='utf-8')

    # Mostrar salida de Artisan
    print(result.stdout)

    if result.stderr:
        print(f"Advertencia: {result.stderr}")

    # Esperar unos segundos para asegurar que el archivo se genere correctamente
    time.sleep(2)

    # Verificar si el archivo CSV realmente se generó
    if os.path.exists(csv_path):
        print(f"✅ CSV generado exitosamente: {csv_path}")
    else:
        print("⚠️ Error: El archivo CSV no se generó correctamente.")

except FileNotFoundError:
    print("❌ Error: PHP no está instalado o no se encuentra en la variable de entorno PATH.")
except subprocess.CalledProcessError as e:
    print(f"❌ Error al ejecutar el comando Artisan: {e}")
except Exception as e:
    print(f"❌ Ocurrió un error inesperado: {e}")
