<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
    // Configuraciones globales del modelo
    // Esto permite que todos nuestros modelos extiendan de esta clase
    // y puedan usar todas las funcionalidades de Eloquent
}