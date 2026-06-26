<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ActividadService;

class ActividadController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new ActividadService();
    }

    /**
     * Lista todas las actividades
     * GET /api/actividades
     * @param string $search Búsqueda opcional
     * @return json Lista de actividades o error
     */
    public function index()
    {
        try {
            $search = $_GET['search'] ?? null;
            $actividades = $this->service->list($search);
            $this->jsonResponse($actividades);
        } catch (\Exception $e) {
            $this->errorResponse([$e->getMessage()], 500);
        }
    }
}
