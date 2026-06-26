<?php

namespace App\Repositories;

use App\Models\OfertaDocumento;

class OfertaDocumentoRepository
{
    /**
     * Obtiene un documento por su ID
     * @param int $id ID del documento
     * @return \App\Models\OfertaDocumento|null El documento encontrado o null
     */
    public function findById($id)
    {
        return OfertaDocumento::find($id);
    }
    
    /**
     * Obtiene todos los documentos asociados a una oferta
     * @param int $ofertaId ID de la oferta
     * @return \Illuminate\Database\Eloquent\Collection Colección de documentos
     */
    public function findByOfertaId($ofertaId)
    {
        return OfertaDocumento::where('oferta_id', $ofertaId)->get();
    }

    /**
     * Crea un nuevo documento de oferta
     * @param array $data Datos para crear el documento
     * @return \App\Models\OfertaDocumento El documento creado
     */
    public function create(array $data)
    {
        return OfertaDocumento::create($data);
    }

    /**
     * Elimina un documento por su ID
     * @param int $id ID del documento
     * @return bool True si se eliminó el documento, false en caso contrario
     */
    public function delete($id)
    {
        $documento = OfertaDocumento::find($id);
        if (!$documento) {
            return false;
        }
        return $documento->delete();
    }

    /**
     * Cuenta los documentos asociados a una oferta
     * @param int $ofertaId ID de la oferta
     * @return int Número de documentos
     */
    public function countByOferta($ofertaId)
    {
        return OfertaDocumento::where('oferta_id', $ofertaId)->count();
    }

    /**
     * Obtiene la ruta del archivo de un documento por su ID
     * @param int $id ID del documento
     * @return string|null La ruta del archivo o null si no se encuentra
     */
    public function getFilePath($id)
    {
        $documento = $this->findById($id);
        if (!$documento) {
            return null;
        }
        return $documento->archivo;
    }
}