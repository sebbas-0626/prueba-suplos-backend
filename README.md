# Backend - Suplos Licitaciones

Backend para la prueba técnica FullStack PHP, desarrollado con PHP 7.0+ sin frameworks, arquitectura MVC y Eloquent ORM standalone.

## 📋 Requisitos

- PHP 7.0 o superior
- MySQL 5.7 o superior / MariaDB 10.2+
- Composer
- Extensiones PHP: PDO, PDO_MySQL, mbstring

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/sebbas-0626/prueba-suplos-backend.git
cd backend
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar variables de entorno

Crea el archivo `.env` en la raíz del proyecto:

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus credenciales de base de datos:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=suplos_db
DB_USERNAME=root
DB_PASSWORD=123456

API_URL=http://localhost:8000
```

### 4. Crear la base de datos

```bash
mysql -u root -p < bd/schema.sql
```

### 5. Cargar actividades desde el clasificador UNSPSC

Coloca el archivo Excel `unspcs-clasificador-de-bienes-y-servicios-de-naciones-unidas-en-espanol.xlsx` en la carpeta `scripts/` y ejecuta:

```bash
php scripts/seed_actividades.php
```

**Nota:** El archivo Excel contiene aproximadamente 50,000 registros y el proceso puede tomar varios minutos.

### 6. Iniciar el servidor

```bash
php -S localhost:8000 -t public
```

El backend estará disponible en: `http://localhost:8000`

## 📁 Estructura del Proyecto

```
backend/
├── app/
│   ├── Controllers/       # Controladores (lógica HTTP)
│   ├── Models/            # Modelos Eloquent
│   ├── Repositories/      # Repositorios (consultas a BD)
│   ├── Services/          # Servicios (lógica de negocio)
│   ├── Validators/        # Validaciones
│   ├── Helpers/           # Helpers
│   └── Core/              # Núcleo (Router, Controller, Database, Model)
├── config/
│   └── database.php       # Configuración de base de datos
├── public/
│   └── index.php          # Punto de entrada
├── routes/
│   └── api.php            # Definición de rutas
├── scripts/
│   └── seed_actividades.php # Carga de actividades UNSPSC
├── uploads/
│   └── documentos/        # Archivos subidos
├── bd/
│   └── schema.sql         # Estructura de base de datos
├── .env                   # Variables de entorno
└── composer.json          # Dependencias
```

## 📡 API Endpoints

### Actividades

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/actividades` | Listar actividades (búsqueda opcional) |

### Ofertas

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/ofertas` | Listar ofertas (paginación + búsqueda) |
| GET | `/api/ofertas/{id}` | Obtener detalle de una oferta |
| POST | `/api/ofertas` | Crear nueva oferta |
| PUT | `/api/ofertas/{id}` | Actualizar oferta |
| POST | `/api/ofertas/{id}/documentos` | Subir documento a oferta |
| GET | `/api/ofertas/reporte/excel` | Exportar reporte en Excel |

### Documentos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/documentos/{id}/descargar` | Descargar documento |

## 📦 Colección Postman

Importa el archivo de colección incluido en el proyecto para probar todos los endpoints.

## 🧪 Pruebas rápidas

### Listar actividades

```bash
curl http://localhost:8000/api/actividades
```

### Crear una oferta

```bash
curl -X POST http://localhost:8000/api/ofertas \
  -H "Content-Type: application/json" \
  -d '{
    "objeto": "Compra de equipos de computación",
    "descripcion": "Adquisición de 50 computadores portátiles",
    "moneda": "COP",
    "presupuesto": 150000000.50,
    "actividad_id": 1,
    "fecha_inicio": "2025-06-01",
    "hora_inicio": "09:00",
    "fecha_cierre": "2025-06-30",
    "hora_cierre": "17:00"
  }'
```

### Subir documento

```bash
curl -X POST http://localhost:8000/api/ofertas/1/documentos \
  -F "titulo=Especificaciones Técnicas" \
  -F "descripcion=Documento técnico" \
  -F "archivo=@/ruta/al/archivo.pdf"
```

## 🛠️ Comandos útiles

```bash
# Iniciar servidor de desarrollo
php -S localhost:8000 -t public

# Cargar datos UNSPSC desde Excel
php scripts/seed_actividades.php

# Regenerar autoload
composer dump-autoload

```

## 📝 Funcionalidades implementadas

- ✅ CRUD completo de ofertas
- ✅ Generación automática de consecutivo (O-XXXX-YY)
- ✅ Subida de documentos (PDF/ZIP)
- ✅ Descarga de documentos
- ✅ Filtros y paginación en listado
- ✅ Exportación a Excel
- ✅ Validaciones en backend
- ✅ Arquitectura MVC con Repositories y Services
- ✅ Eloquent ORM standalone
- ✅ Carga de actividades UNSPSC desde Excel

## 🔧 Solución de problemas

### Error: Access denied for user

Verifica las credenciales en el archivo `.env`

### Error: Class not found

Ejecuta `composer dump-autoload`

### Error: Memory exhausted

```bash
php -d memory_limit=512M scripts/seed_actividades.php
```

### El puerto 8000 está ocupado

```bash
php -S localhost:8001 -t public
```

## 📄 Licencia

Prueba técnica - Suplos

---

Desarrollado por Sebastian Tovar Chavez para la prueba técnica de FullStack Developer.
