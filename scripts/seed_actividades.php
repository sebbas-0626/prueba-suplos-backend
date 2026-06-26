<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use Illuminate\Database\Capsule\Manager as DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

// Aumentar límite de memoria
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

// Iniciar base de datos
Database::getInstance();

try {
    echo "=== CARGANDO ACTIVIDADES DESDE EXCEL ===\n\n";

    $excelPath = __DIR__ . '/unspcs-clasificador-de-bienes-y-servicios-de-naciones-unidas-en-espanol.xlsx';
    
    if (!file_exists($excelPath)) {
        echo "Error: No se encontró el archivo Excel\n";
        echo "  Coloca el archivo en: scripts/unspcs-clasificador-de-bienes-y-servicios-de-naciones-unidas-en-espanol.xlsx\n";
        exit(1);
    }

    echo "1. Limpiando tabla actividades...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    DB::table('actividades')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    echo "   ✓ Tabla limpiada\n\n";

    echo "2. Leyendo archivo Excel por chunks...\n";
    
    // Crear lector para archivos grandes
    $reader = new Xlsx();
    $reader->setReadDataOnly(true);
    $reader->setReadEmptyCells(false);
    
    // Cargar el archivo
    $spreadsheet = $reader->load($excelPath);
    $worksheet = $spreadsheet->getActiveSheet();
    
    // Obtener la última fila con datos
    $highestRow = $worksheet->getHighestRow();
    $totalFilas = $highestRow - 5; // Restar las 5 filas de encabezados
    echo "   Total de filas a procesar: $totalFilas\n\n";

    echo "3. Insertando actividades...\n";
    
    $data = [];
    $total = 0;
    $batchSize = 1000;
    
    // Procesar desde la fila 6
    for ($row = 6; $row <= $highestRow; $row++) {
        // Leer celdas de la fila
        $codigoSegmento = $worksheet->getCell('A' . $row)->getValue();
        $nombreSegmento = $worksheet->getCell('B' . $row)->getValue();
        $codigoFamilia  = $worksheet->getCell('C' . $row)->getValue();
        $nombreFamilia  = $worksheet->getCell('D' . $row)->getValue();
        $codigoClase    = $worksheet->getCell('E' . $row)->getValue();
        $nombreClase    = $worksheet->getCell('F' . $row)->getValue();
        $codigoProducto = $worksheet->getCell('G' . $row)->getValue();
        $nombreProducto = $worksheet->getCell('H' . $row)->getValue();
        
        // Saltar filas vacías
        if (empty($codigoSegmento) || empty($codigoProducto)) continue;
        
        $data[] = [
            'codigo_segmento' => (int)$codigoSegmento,
            'segmento'        => trim((string)$nombreSegmento),
            'codigo_familia'  => (int)$codigoFamilia,
            'familia'         => trim((string)$nombreFamilia),
            'codigo_clase'    => (int)$codigoClase,
            'clase'           => trim((string)$nombreClase),
            'codigo_producto' => (int)$codigoProducto,
            'producto'        => trim((string)$nombreProducto),
        ];
        
        $total++;
        
        // Insertar en lotes
        if (count($data) >= $batchSize) {
            DB::table('actividades')->insert($data);
            echo "   Insertadas $total filas...\n";
            $data = [];
        }
    }
    
    // Insertar el resto
    if (!empty($data)) {
        DB::table('actividades')->insert($data);
        echo "   Insertadas $total filas...\n";
    }

    // Liberar memoria
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet, $reader);

    echo "\n4. Verificando...\n";
    $totalBD = DB::table('actividades')->count();
    echo "   Total de actividades en BD: $totalBD\n";

    echo "\n=== COMPLETADO EXITOSAMENTE ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " línea " . $e->getLine() . "\n";
}