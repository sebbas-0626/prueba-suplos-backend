<?php

namespace App\Models;

use App\Core\Model;

class OfertaDocumento extends Model
{
    protected $table = 'ofertas_documentos';
    
    protected $fillable = [
        'oferta_id', 'titulo', 'descripcion', 'archivo'
    ];
    
    // Relación con la oferta a la que pertenece el documento donde 'oferta_id' es la clave foránea en la tabla 'ofertas_documentos'
    public function oferta()
    {
        return $this->belongsTo(Oferta::class);
    }
}