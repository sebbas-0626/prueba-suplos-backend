<?php

namespace App\Helpers;

use App\Models\Oferta;

class ConsecutivoHelper
{
    /**
     * Genera el consecutivo en formato O-XXXX-YY
     */
    public static function generar(): string
    {
        $year = date('y');
        $last = Oferta::orderBy('id', 'desc')->first();
        
        if ($last && $last->consecutivo) {
            $number = intval(substr($last->consecutivo, 2, 4)) + 1;
        } else {
            $number = 1;
        }
        
        return sprintf('O-%04d-%s', $number, $year);
    }
}