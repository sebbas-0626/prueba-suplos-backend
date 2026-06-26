<?php

namespace App\Services;

use App\Repositories\ActividadRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ActividadService
{
    private $actividadRepository;

    public function __construct()
    {
        $this->actividadRepository = new ActividadRepository();
    }

    public function list($search = null)
    {
        return $this->actividadRepository->findAll($search);
    }

    /**
     * Cargar actividades desde archivo Excel UNSPSC
     */
    public function cargarDesdeExcel($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Limpiar tabla
        $this->actividadRepository->truncate();

        $data = [];
        // Saltar encabezados (asumiendo que la primera fila es header)
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty($row[0])) continue;

            $data[] = [
                'codigo_segmento' => (int)$row[0],
                'segmento' => $row[1] ?? '',
                'codigo_familia' => (int)$row[2],
                'familia' => $row[3] ?? '',
                'codigo_clase' => (int)$row[4],
                'clase' => $row[5] ?? '',
                'codigo_producto' => (int)$row[6],
                'producto' => $row[7] ?? '',
            ];

            // Insertar en lotes de 1000
            if (count($data) >= 1000) {
                $this->actividadRepository->bulkCreate($data);
                $data = [];
            }
        }

        // Insertar lo que quede
        if (!empty($data)) {
            $this->actividadRepository->bulkCreate($data);
        }

        return true;
    }
}