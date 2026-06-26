<?php

namespace App\Models;

use App\Core\Model;

class Actividad extends Model
{
    protected $table = 'actividades';
    
    protected $fillable = [
        'codigo_segmento', 'segmento', 'codigo_familia', 
        'familia', 'codigo_clase', 'clase', 
        'codigo_producto', 'producto'
    ];
    
    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'actividad_id');
    }
}