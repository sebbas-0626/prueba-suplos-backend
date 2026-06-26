-- Crear base de datos
CREATE DATABASE IF NOT EXISTS suplos_db;
USE suplos_db;

-- Tabla de actividades (clasificador UNSPSC)
CREATE TABLE IF NOT EXISTS actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_segmento INT NOT NULL,
    segmento VARCHAR(200) NOT NULL,
    codigo_familia INT NOT NULL,
    familia VARCHAR(200) NOT NULL,
    codigo_clase INT NOT NULL,
    clase VARCHAR(200) NOT NULL,
    codigo_producto INT NOT NULL,
    producto VARCHAR(200) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo_producto (codigo_producto),
    INDEX idx_producto (producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de ofertas
CREATE TABLE IF NOT EXISTS ofertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consecutivo VARCHAR(20) UNIQUE NOT NULL,
    objeto VARCHAR(150) NOT NULL,
    descripcion VARCHAR(400) NOT NULL,
    moneda VARCHAR(3) NOT NULL,
    presupuesto DECIMAL(15,2) NOT NULL,
    actividad_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    fecha_cierre DATE NOT NULL,
    hora_cierre TIME NOT NULL,
    estado VARCHAR(20) DEFAULT 'borrador',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (actividad_id) REFERENCES actividades(id),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_inicio, fecha_cierre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de documentos de ofertas
CREATE TABLE IF NOT EXISTS ofertas_documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    oferta_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descripcion VARCHAR(200),
    archivo VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (oferta_id) REFERENCES ofertas(id) ON DELETE CASCADE,
    INDEX idx_oferta_id (oferta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;