<?php

namespace App\Services;

use App\Repositories\OfertaRepository;
use App\Repositories\OfertaDocumentoRepository;
use App\Validators\OfertaValidator;
use App\Helpers\ConsecutivoHelper;

class OfertaService
{
    private $ofertaRepository;
    private $documentoRepository;
    private $validator;

    public function __construct()
    {
        $this->ofertaRepository = new OfertaRepository();
        $this->documentoRepository = new OfertaDocumentoRepository();
        $this->validator = new OfertaValidator();
    }

    public function list($page = 1, $limit = 10, $search = null)
    {
        return $this->ofertaRepository->findAll($page, $limit, $search);
    }

    public function getDetail($id)
    {
        $oferta = $this->ofertaRepository->findById($id);

        if (!$oferta) {
            throw new \Exception('Oferta no encontrada', 404);
        }

        return $oferta;
    }

    public function create(array $data)
    {
        // Validar
        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            throw new \Exception(json_encode($errors), 422);
        }

        // Generar consecutivo
        $data['consecutivo'] = ConsecutivoHelper::generar();
        $data['estado'] = 'borrador';

        return $this->ofertaRepository->create($data);
    }

    public function update($id, array $data)
    {
        // Verificar si existe
        if (!$this->ofertaRepository->exists($id)) {
            throw new \Exception('Oferta no encontrada', 404);
        }

        // Validar con contexto de actualización
        $this->validator->setUpdateContext($id);
        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            throw new \Exception(json_encode($errors), 422);
        }

        return $this->ofertaRepository->update($id, $data);
    }

    public function getReportData()
    {
        return $this->ofertaRepository->getAllForReport();
    }

    public function addDocumento($ofertaId, array $data, $file)
    {
        // Validar que la oferta existe
        if (!$this->ofertaRepository->exists($ofertaId)) {
            throw new \Exception('Oferta no encontrada', 404);
        }

        // Validar archivo
        $this->validateFile($file);

        // Guardar archivo
        $filePath = $this->saveFile($file, $ofertaId);

        // Crear registro
        $data['oferta_id'] = $ofertaId;
        $data['archivo'] = $filePath;

        return $this->documentoRepository->create($data);
    }

    private function validateFile($file)
    {
        $allowedTypes = ['application/pdf', 'application/zip'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['pdf', 'zip'])) {
            throw new \Exception('Solo se permiten archivos PDF o ZIP', 422);
        }

        if ($file['size'] > 52428800) { // 50MB
            throw new \Exception('El archivo no puede exceder 50MB', 422);
        }
    }

    private function saveFile($file, $ofertaId)
    {
        // 1. Verificar que el archivo temporal existe
        if (!file_exists($file['tmp_name'])) {
            throw new \Exception('El archivo temporal no existe: ' . $file['tmp_name'], 500);
        }

        // 2. Ruta absoluta
        $uploadDir = __DIR__ . '/../../uploads/documentos/';

        // 3. Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new \Exception('No se pudo crear el directorio: ' . $uploadDir, 500);
            }
        }

        // 4. Verificar permisos del directorio
        if (!is_writable($uploadDir)) {
            throw new \Exception('El directorio no tiene permisos de escritura: ' . $uploadDir, 500);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = sprintf('oferta_%d_%s.%s', $ofertaId, uniqid(), $extension);
        $filePath = $uploadDir . $filename;

        // 5. Mover el archivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            // Obtener el último error de PHP
            $error = error_get_last();
            $errorMsg = $error ? $error['message'] : 'Error desconocido';
            throw new \Exception('Error al guardar el archivo: ' . $errorMsg, 500);
        }

        // 6. Verificar que el archivo se movió correctamente
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no se guardó correctamente', 500);
        }

        return 'uploads/documentos/' . $filename;
    }

    public function descargarDocumento($documentoId)
    {
        // Buscar el documento
        $documento = $this->documentoRepository->findById($documentoId);
        
        if (!$documento) {
            throw new \Exception('Documento no encontrado', 404);
        }
        
        // Obtener la ruta del archivo
        $filePath = __DIR__ . '/../../' . $documento->archivo;
        
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe en el servidor', 404);
        }
        
        return [
            'filePath' => $filePath,
            'fileName' => basename($filePath),
            'extension' => strtolower(pathinfo($filePath, PATHINFO_EXTENSION))
        ];
    }
}
