<?php

namespace App\Models;

use App\Core\Model;

class OfertaDocumento extends Model
{
    protected $table = 'ofertas_documentos';
    
    protected $fillable = [
        'oferta_id', 'titulo', 'descripcion', 'archivo'
    ];
    
    public function oferta()
    {
        return $this->belongsTo(Oferta::class);
    }
}