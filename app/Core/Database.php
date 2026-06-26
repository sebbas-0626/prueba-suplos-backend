<?php

namespace App\Core;

use Illuminate\Database\Capsule\Manager as Capsule;

// Cargar variables de entorno
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

class Database
{
    private static $instance = null;
    private $capsule;

    private function __construct()
    {
        $this->capsule = new Capsule;
        
        $this->capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
            'database'  => $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE'),
            'username'  => $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME'),
            'password'  => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal(); 
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCapsule()
    {
        return $this->capsule;
    }
}