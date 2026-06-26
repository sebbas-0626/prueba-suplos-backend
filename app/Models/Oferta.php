<?php

namespace App\Models;

use App\Core\Model;

class Oferta extends Model
{
    protected $table = 'ofertas';
    
    protected $fillable = [
        'consecutivo', 'objeto', 'descripcion', 'moneda', 
        'presupuesto', 'actividad_id', 'fecha_inicio', 
        'hora_inicio', 'fecha_cierre', 'hora_cierre', 'estado'
    ];
    
    protected $casts = [
        'presupuesto' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_cierre' => 'date',
        'hora_inicio' => 'string',
        'hora_cierre' => 'string',
    ];
    
    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }
    
    public function documentos()
    {
        return $this->hasMany(OfertaDocumento::class, 'oferta_id');
    }
    
    // Generar consecutivo automático
    public static function generarConsecutivo()
    {
        $year = date('y');
        $last = self::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->consecutivo, 2, 4)) + 1 : 1;
        return sprintf('O-%04d-%s', $number, $year);
    }
}