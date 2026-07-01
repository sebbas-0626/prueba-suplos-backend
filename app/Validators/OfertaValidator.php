<?php

namespace App\Validators;

use App\Models\Actividad;
use App\Models\OfertaDocumento;

class OfertaValidator implements ValidatorInterface
{
    private $errors = [];
    private $isUpdate = false;
    private $ofertaId = null;

    public function setUpdateContext($ofertaId = null)
    {
        $this->isUpdate = true;
        $this->ofertaId = $ofertaId;
        return $this;
    }

    public function validate(array $data): array
    {
        $this->errors = [];
        
        $this->validateRequired($data);
        $this->validateLengths($data);
        $this->validateMoneda($data);
        $this->validatePresupuesto($data);
        $this->validateActividad($data);
        $this->validateFechas($data);
        $this->validateEstado($data);

        if ($this->isUpdate) {
            $this->validateDocumentos();
        }

        return $this->errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    // Validación de campos requeridos de acuerdo a la operación (crear o actualizar)
    private function validateRequired(array $data)
    {
        $required = [
            'objeto' => 'El objeto es requerido',
            'descripcion' => 'La descripción es requerida',
            'moneda' => 'La moneda es requerida',
            'presupuesto' => 'El presupuesto es requerido',
            'actividad_id' => 'La actividad es requerida',
            'fecha_inicio' => 'La fecha de inicio es requerida',
            'hora_inicio' => 'La hora de inicio es requerida',
            'fecha_cierre' => 'La fecha de cierre es requerida',
            'hora_cierre' => 'La hora de cierre es requerida',
        ];

        foreach ($required as $field => $message) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $this->addError($field, $message);
            }
        }
    }

    private function validateLengths(array $data)
    {
        if (isset($data['objeto']) && strlen($data['objeto']) > 150) {
            $this->addError('objeto', 'El objeto no puede tener más de 150 caracteres');
        }

        if (isset($data['descripcion']) && strlen($data['descripcion']) > 400) {
            $this->addError('descripcion', 'La descripción no puede tener más de 400 caracteres');
        }
    }

    private function validateMoneda(array $data)
    {
        if (isset($data['moneda']) && !in_array($data['moneda'], ['COP', 'USD', 'EUR'])) {
            $this->addError('moneda', 'La moneda debe ser COP, USD o EUR');
        }
    }
// Validar que el estado sea uno de los permitidos que son: borrador, publicada, en_curso, cerrada, adjudicada
    private function validateEstado(array $data)
    {
        $estadosPermitidos = ['borrador', 'publicada', 'en_curso', 'cerrada', 'adjudicada'];
        if (isset($data['estado']) && !in_array($data['estado'], $estadosPermitidos)) {
            $this->addError('estado', 'El estado debe ser: ' . implode(', ', $estadosPermitidos));
        }
    }

    private function validatePresupuesto(array $data)
    {
        if (!isset($data['presupuesto'])) {
            return;
        }

        $presupuesto = $data['presupuesto'];
        
        if (!is_numeric($presupuesto)) {
            $this->addError('presupuesto', 'El presupuesto debe ser un número');
            return;
        }

        if ($presupuesto <= 0) {
            $this->addError('presupuesto', 'El presupuesto debe ser mayor a 0');
        }

        // Validar máximo 2 decimales
        if (strpos((string)$presupuesto, '.') !== false) {
            $decimals = strlen(substr((string)$presupuesto, strpos((string)$presupuesto, '.') + 1));
            if ($decimals > 2) {
                $this->addError('presupuesto', 'El presupuesto debe tener máximo 2 decimales');
            }
        }
    }

    private function validateActividad(array $data)
    {
        if (!isset($data['actividad_id'])) {
            return;
        }

        $actividad = Actividad::find($data['actividad_id']);
        if (!$actividad) {
            $this->addError('actividad_id', 'La actividad seleccionada no existe');
        }
    }

    private function validateFechas(array $data)
    {
        // Validar formato de fechas
        $dateFields = ['fecha_inicio', 'fecha_cierre'];
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && !$this->isValidDate($data[$field])) {
                $this->addError($field, "El formato de {$field} debe ser YYYY-MM-DD");
            }
        }

        // Validar formato de horas
        $timeFields = ['hora_inicio', 'hora_cierre'];
        foreach ($timeFields as $field) {
            if (isset($data[$field]) && !$this->isValidTime($data[$field])) {
                $this->addError($field, "El formato de {$field} debe ser HH:MM (24h)");
            }
        }

        // Validar relación fecha inicio < fecha cierre
        if (isset($data['fecha_inicio']) && isset($data['fecha_cierre']) && 
            $this->isValidDate($data['fecha_inicio']) && $this->isValidDate($data['fecha_cierre'])) {
            
            $fechaInicio = new \DateTime($data['fecha_inicio']);
            $fechaCierre = new \DateTime($data['fecha_cierre']);
            
            if ($fechaCierre < $fechaInicio) {
                $this->addError('fecha_cierre', 'La fecha de cierre debe ser mayor o igual a la fecha de inicio');
            }

            // Si es el mismo día, validar horas
            if ($fechaInicio->format('Y-m-d') === $fechaCierre->format('Y-m-d')) {
                if (isset($data['hora_inicio']) && isset($data['hora_cierre']) &&
                    $this->isValidTime($data['hora_inicio']) && $this->isValidTime($data['hora_cierre'])) {
                    
                    $horaInicio = new \DateTime($data['hora_inicio']);
                    $horaCierre = new \DateTime($data['hora_cierre']);
                    
                    if ($horaCierre <= $horaInicio) {
                        $this->addError('hora_cierre', 'La hora de cierre debe ser mayor a la hora de inicio cuando es el mismo día');
                    }
                }
            }
        }
    }
// Validar que exista al menos un documento cargado si es una actualización
    private function validateDocumentos()
    {
        if ($this->ofertaId) {
            $count = OfertaDocumento::where('oferta_id', $this->ofertaId)->count();
            if ($count === 0) {
                $this->addError('documentos', 'Debe existir al menos 1 documento cargado');
            }
        }
    }

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidTime($time)
    {
        $d = \DateTime::createFromFormat('H:i', $time);
        return $d && $d->format('H:i') === $time;
    }
}