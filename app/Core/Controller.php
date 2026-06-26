<?php

namespace App\Core;

class Controller
{
    /**
     * Envía una respuesta JSON al cliente
     * @param mixed $data Datos a enviar
     * @param int $status Código de estado HTTP (default: 200)
     */
    protected function jsonResponse($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Envía una respuesta de error al cliente
     * @param array $errors Lista de errores
     * @param int $status Código de estado HTTP (default: 422)
     */
    protected function errorResponse($errors, $status = 422)
    {
        $this->jsonResponse(['errors' => $errors], $status);
    }

    /**
     * Envía una respuesta de éxito al cliente
     * @param mixed $data Datos a enviar
     * @param string $message Mensaje de éxito
     * @param int $status Código de estado HTTP (default: 200)
     */
    protected function successResponse($data, $message = 'Operación exitosa', $status = 200)
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}