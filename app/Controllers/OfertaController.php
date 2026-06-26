<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\OfertaService;

class OfertaController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new OfertaService();
    }

    /**
     * Lista todas las ofertas con paginación y búsqueda
     * GET /api/ofertas
     * @param int $page Número de página (default: 1)
     * @param int $limit Registros por página (default: 10)
     * @param string $search Búsqueda por consecutivo, objeto o descripción (opcional)
     * @return json Lista paginada de ofertas o error
     */
    public function index()
    {
        try {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;
            $search = $_GET['search'] ?? null;

            $result = $this->service->list($page, $limit, $search);
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            $this->errorResponse([$e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Obtiene el detalle completo de una oferta (incluye documentos y actividad)
     * GET /api/ofertas/{id}
     * @param int $id ID de la oferta
     * @return json Datos de la oferta o error 404
     */
    public function show($id)
    {
        try {
            $oferta = $this->service->getDetail($id);
            $this->jsonResponse($oferta);
        } catch (\Exception $e) {
            $this->errorResponse([$e->getMessage()], $e->getCode() ?: 404);
        }
    }

    /**
     * Crea una nueva oferta
     * POST /api/ofertas
     * @return json Oferta creada o error
     */
    public function store()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $oferta = $this->service->create($data);
            $this->successResponse($oferta, 'Oferta creada exitosamente', 201);
        } catch (\Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->errorResponse($errors, 422);
            } else {
                $this->errorResponse([$e->getMessage()], $e->getCode() ?: 500);
            }
        }
    }

    /**
     * Actualiza una oferta existente
     * PUT /api/ofertas/{id}
     * @param int $id ID de la oferta
     * @return json Oferta actualizada o error
     */
    public function update($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $oferta = $this->service->update($id, $data);
            $this->successResponse($oferta, 'Oferta actualizada exitosamente');
        } catch (\Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->errorResponse($errors, 422);
            } else {
                $this->errorResponse([$e->getMessage()], $e->getCode() ?: 500);
            }
        }
    }

    /**
     * Sube un documento (PDF o ZIP) a una oferta existente
     * POST /api/ofertas/{id}/documentos
     * @param int $id ID de la oferta
     * @return json Documento creado o error
     */
    public function uploadDocumento($id)
    {
        try {
            // error_log('FILES: ' . print_r($_FILES, true));
            // error_log('POST: ' . print_r($_POST, true));

            if (!isset($_FILES['archivo'])) {
                throw new \Exception('Archivo no proporcionado', 422);
            }

            // Verifica si hubo error en la subida
            if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                $errors = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco',
                    UPLOAD_ERR_EXTENSION => 'Extensión PHP detuvo la subida'
                ];
                $errorMsg = $errors[$_FILES['archivo']['error']] ?? 'Error desconocido en la subida';
                throw new \Exception('Error en la subida: ' . $errorMsg, 422);
            }

            $data = [
                'titulo' => $_POST['titulo'] ?? 'Documento',
                'descripcion' => $_POST['descripcion'] ?? ''
            ];

            $documento = $this->service->addDocumento($id, $data, $_FILES['archivo']);
            $this->successResponse($documento, 'Documento subido exitosamente', 201);
        } catch (\Exception $e) {
            $this->errorResponse([$e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Genera y descarga un reporte en Excel con todas las ofertas
     * GET /api/ofertas/reporte/excel
     * @return void Descarga archivo Excel
     * @throws \Exception Error al generar el reporte
     */
    public function reporteExcel()
    {
        try {
            // Obtener todas las ofertas
            $ofertas = $this->service->getReportData();

            // Crear el Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Encabezados
            $headers = [
                'A1' => 'Consecutivo',
                'B1' => 'Objeto',
                'C1' => 'Descripción',
                'D1' => 'Moneda',
                'E1' => 'Presupuesto',
                'F1' => 'Actividad',
                'G1' => 'Fecha Inicio',
                'H1' => 'Hora Inicio',
                'I1' => 'Fecha Cierre',
                'J1' => 'Hora Cierre',
                'K1' => 'Estado'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }

            // Datos
            $row = 2;
            foreach ($ofertas as $oferta) {
                $sheet->setCellValue('A' . $row, $oferta->consecutivo);
                $sheet->setCellValue('B' . $row, $oferta->objeto);
                $sheet->setCellValue('C' . $row, $oferta->descripcion);
                $sheet->setCellValue('D' . $row, $oferta->moneda);
                $sheet->setCellValue('E' . $row, $oferta->presupuesto);
                $sheet->setCellValue('F' . $row, $oferta->actividad->producto ?? 'N/A');
                $sheet->setCellValue('G' . $row, $oferta->fecha_inicio);
                $sheet->setCellValue('H' . $row, $oferta->hora_inicio);
                $sheet->setCellValue('I' . $row, $oferta->fecha_cierre);
                $sheet->setCellValue('J' . $row, $oferta->hora_cierre);
                $sheet->setCellValue('K' . $row, $oferta->estado ?? 'borrador');
                $row++;
            }

            // Autoajustar columnas
            foreach (range('A', 'K') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Crear el archivo
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            // Configurar headers para descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="reporte_ofertas_' . date('Y-m-d_H-i') . '.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            $this->errorResponse(['Error al generar el reporte: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Descarga un documento asociado a una oferta
     * GET /api/documentos/{id}/descargar
     * @param int $id ID del documento
     * @return void Descarga el archivo
     * @throws \Exception Error al descargar el documento
     */
    public function descargarDocumento($id)
    {
        try {
            // Obtener informacion del documento desde el service
            $documentoInfo = $this->service->descargarDocumento($id);
            
            $filePath = $documentoInfo['filePath'];
            $fileName = $documentoInfo['fileName'];
            $extension = $documentoInfo['extension'];
            
            // Setear headers segun el tipo de archivo
            if ($extension === 'pdf') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $fileName . '"');
            } else {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
            }
            
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: public');
            
            // Enviar el archivo
            readfile($filePath);
            exit;
            
        } catch (\Exception $e) {
            $this->errorResponse([$e->getMessage()], $e->getCode() ?: 500);
        }
    }
}
