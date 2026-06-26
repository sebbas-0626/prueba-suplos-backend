<?php

namespace App\Repositories;

use App\Models\Oferta;
use App\Models\OfertaDocumento;

class OfertaRepository
{
    /**
     * Obtiene todas las ofertas con paginación y búsqueda opcional
     * @param int $page Número de página (default: 1)
     * @param int $limit Número de registros por página (default: 10)
     * @param string|null $search Término de búsqueda (opcional)
     * @return array Arreglo con datos de ofertas y metadatos de paginación
     */
    public function findAll($page = 1, $limit = 10, $search = null)
    {
        $query = Oferta::with('actividad');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('objeto', 'LIKE', "%{$search}%")
                    ->orWhere('descripcion', 'LIKE', "%{$search}%")
                    ->orWhere('consecutivo', 'LIKE', "%{$search}%");
            });
        }

        $total = $query->count();
        $ofertas = $query->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'data' => $ofertas,
            'meta' => [
                'total' => $total,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'last_page' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Obtiene una oferta por su ID, incluyendo actividad y documentos
     * @param int $id ID de la oferta
     * @return \App\Models\Oferta|null La oferta encontrada o null
     */
    public function findById($id)
    {
        return Oferta::with(['actividad', 'documentos'])->find($id);
    }

    /**
     * Crea una nueva oferta
     * @param array $data Datos para crear la oferta
     * @return \App\Models\Oferta La oferta creada
     */
    public function create(array $data)
    {
        return Oferta::create($data);
    }

    /**
     * Actualiza una oferta existente
     * @param int $id ID de la oferta
     * @param array $data Datos para actualizar la oferta
     * @return \App\Models\Oferta|null La oferta actualizada o null si no se encuentra
     */
    public function update($id, array $data)
    {
        $oferta = Oferta::find($id);
        if (!$oferta) {
            return null;
        }
        $oferta->update($data);
        return $oferta;
    }

    /**
     * Elimina una oferta por su ID
     * @param int $id ID de la oferta
     * @return bool True si se eliminó la oferta, false en caso contrario
     */
    public function delete($id)
    {
        $oferta = Oferta::find($id);
        if (!$oferta) {
            return false;
        }
        return $oferta->delete();
    }

    /**
     * Obtiene todas las ofertas para un reporte
     * @return \Illuminate\Database\Eloquent\Collection Colección de ofertas
     */
    public function getAllForReport()
    {
        return Oferta::with('actividad')->get();
    }

    /**
     * Verifica si una oferta existe por su ID
     * @param int $id ID de la oferta
     * @return bool True si la oferta existe, false en caso contrario
     */
    public function exists($id)
    {
        return Oferta::where('id', $id)->exists();
    }
}
