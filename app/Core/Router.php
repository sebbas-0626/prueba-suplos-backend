<?php

namespace App\Core;

class Router
{
    private $routes = [];
    
    /**
     * Agrega una ruta al enrutador
     * @param string $method Método HTTP (GET, POST, PUT, DELETE)
     * @param string $path Ruta (ej. /api/ofertas)
     * @param callable|array $handler Función o método del controlador que maneja la ruta
     */
    public function add($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    /**
     * Agrega un recurso al enrutador
     * @param string $prefix Prefijo de la ruta (ej. /api/ofertas)
     * @param string $controller Nombre del controlador
     */
    public function addResource($prefix, $controller)
    {
        $this->add('GET', $prefix, [$controller, 'index']);
        $this->add('GET', $prefix . '/{id}', [$controller, 'show']);
        $this->add('POST', $prefix, [$controller, 'store']);
        $this->add('PUT', $prefix . '/{id}', [$controller, 'update']);
        $this->add('DELETE', $prefix . '/{id}', [$controller, 'delete']);
    }
    
    /**
     * Despacha la solicitud entrante a la ruta correspondiente
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace('/api', '', $uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            
            $pattern = preg_replace('/\{[a-zA-Z]+\}/', '([a-zA-Z0-9]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $handler = $route['handler'];
                
                if (is_array($handler)) {
                    $controller = new $handler[0]();
                    $method = $handler[1];
                    call_user_func_array([$controller, $method], $matches);
                    return;
                }
            }
        }
        
        header('HTTP/1.0 404 Not Found');
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
}