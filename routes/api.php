<?php

use App\Core\Router;
use App\Controllers\OfertaController;
use App\Controllers\ActividadController;

$router = new Router();

// Ofertas
$router->add('GET', '/ofertas', [OfertaController::class, 'index']);
$router->add('GET', '/ofertas/{id}', [OfertaController::class, 'show']);
$router->add('POST', '/ofertas', [OfertaController::class, 'store']);
$router->add('PUT', '/ofertas/{id}', [OfertaController::class, 'update']);
$router->add('POST', '/ofertas/{id}/documentos', [OfertaController::class, 'uploadDocumento']);
$router->add('GET', '/ofertas/reporte/excel', [OfertaController::class, 'reporteExcel']);
$router->add('GET', '/documentos/{id}/descargar', [OfertaController::class, 'descargarDocumento']);

// Actividades
$router->add('GET', '/actividades', [ActividadController::class, 'index']);
