<?php

namespace App\Repositories;

use App\Models\Actividad;

class ActividadRepository
{
    /**
     * Obtiene todas las actividades con búsqueda opcional
     * @param string|null $search Término de búsqueda (opcional)
     * @param int $limit Número máximo de registros a retornar (default: 100)
     * @return \Illuminate\Database\Eloquent\Collection Colección de actividades
     */
    public function findAll($search = null, $limit = 100)
    {
        $query = Actividad::query();

        if ($search) {
            $query->where('producto', 'LIKE', "%{$search}%")
                ->orWhere('codigo_producto', 'LIKE', "%{$search}%");
        }

        return $query->orderBy('producto')->limit($limit)->get();
    }

    /**
     * Obtiene una actividad por su ID
     * @param int $id ID de la actividad
     * @return \App\Models\Actividad|null La actividad encontrada o null
     */
    public function findById($id)
    {
        return Actividad::find($id);
    }

    /**
     * Crea una nueva actividad
     * @param array $data Datos para crear la actividad
     * @return \App\Models\Actividad La actividad creada
     */
    public function create(array $data)
    {
        return Actividad::create($data);
    }

    /**
     * Crea múltiples actividades
     * @param array $data Arreglo de datos para crear las actividades
     * @return bool True si se crearon las actividades, false en caso contrario
     */
    public function bulkCreate(array $data)
    {
        return Actividad::insert($data);
    }

    /**
     * Trunca la tabla de actividades
     * @return bool True si se truncó la tabla, false en caso contrario
     */
    public function truncate()
    {
        return Actividad::truncate();
    }
}
