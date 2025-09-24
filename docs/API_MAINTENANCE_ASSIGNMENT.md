# API de Asignación de Mantenimientos

## Descripción General

Esta documentación describe la implementación completa de la funcionalidad para asignar mantenimientos a técnicos, incluyendo validaciones de disponibilidad y estado.

## Endpoints Implementados

### Asignar Mantenimiento
```
PUT /api/assignMaintenance/{maintenance}
```

**Headers requeridos:**
```
Authorization: Bearer {jwt_token}
Content-Type: application/json
```

**Parámetros de URL:**
- `{maintenance}`: ID del mantenimiento a asignar

**Body de la petición:**
```json
{
    "date_maintenance": "2024-01-15",
    "shift": "AM",
    "value": 50000,
    "spare_parts": ["filtro", "aceite", "bujías"],
    "technician_id": 1
}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "message": "Maintenance assigned Successfully",
    "data": {
        "id": 1,
        "client_device_id": 5,
        "type": "preventive",
        "date_maintenance": "2024-01-15",
        "shift": "AM",
        "status": "assigned",
        "technician_id": 1,
        "value": 50000,
        "spare_parts": ["filtro", "aceite", "bujías"],
        "created_at": "2024-01-10T10:00:00.000000Z",
        "updated_at": "2024-01-10T10:30:00.000000Z"
    }
}
```

## Validaciones Implementadas

### 1. Autorización
- Solo usuarios con rol `coordinador` o `administrador` pueden asignar mantenimientos
- Se valida mediante JWT token

### 2. Estado del Mantenimiento
- **Regla:** `MaintenancePendingStatusRule`
- **Validación:** Solo se pueden asignar mantenimientos con estado `pending`
- **Error:** "Solo se pueden asignar mantenimientos con estado pendiente."

### 3. Disponibilidad del Técnico
- **Regla:** `TechnicianAvailabilityRule`
- **Validación:** El técnico no debe tener mantenimientos asignados para la misma fecha y turno
- **Estados verificados:** `assigned`, `in_progress`
- **Error:** "El técnico ya tiene un mantenimiento asignado para esta fecha y turno."

### 4. Validaciones de Campos
```php
$rules = [
    'date_maintenance' => ['required', 'date_format:Y-m-d'],
    'shift' => ['required', 'string', 'in:AM,PM'],
    'value' => ['nullable', 'integer'],
    'spare_parts' => ['required', 'array'],
    'technician_id' => ['required', 'integer', new TechnicianAvailabilityRule(...)]
];
```

## Archivos Implementados

### 1. Request de Validación
**Archivo:** `app/Http/Requests/Maintenance/AssignMaintenanceAPIRequest.php`

**Características:**
- Extiende `APIRequest` para manejo de errores JSON
- Valida autorización por roles
- Incluye validación de estado del mantenimiento
- Incluye validación de disponibilidad del técnico

### 2. Reglas de Validación Personalizadas

#### MaintenancePendingStatusRule
**Archivo:** `app/Rules/Maintenance/MaintenancePendingStatusRule.php`
- Verifica que el mantenimiento tenga estado `pending`
- Recibe el objeto Maintenance directamente (optimizado)

#### TechnicianAvailabilityRule
**Archivo:** `app/Rules/Maintenance/TechnicianAvailabilityRule.php`
- Verifica disponibilidad del técnico para fecha y turno específicos
- Solo considera mantenimientos activos (`assigned`, `in_progress`)

### 3. Controlador
**Archivo:** `app/Http/Controllers/Maintenance/MaintenanceNotAssignedAPIController.php`

**Método:** `assignMaintenance(Maintenance $maintenance, AssignMaintenanceAPIRequest $request)`

### 4. Repositorio
**Archivo:** `app/Repositories/Maintenance/MaintenanceRepository.php`

**Método:** `assignMaintenance(Maintenance $maintenance, array $input)`
- Actualiza el mantenimiento con los datos de asignación
- Cambia el estado a `assigned`

### 5. Modelo
**Archivo:** `app/Models/Maintenance/Maintenance.php`

**Campos agregados:**
- `value`: Valor del mantenimiento
- `spare_parts`: Repuestos necesarios (JSON)
- `shift`: Turno (AM/PM)

## Estados de Mantenimiento

| Estado | Descripción | Asignable |
|--------|-------------|-----------|
| `pending` | Pendiente de asignación | ✅ Sí |
| `assigned` | Asignado a técnico | ❌ No |
| `in_progress` | En progreso | ❌ No |
| `completed` | Completado | ❌ No |

## Códigos de Error

### 403 - No Autorizado
```json
{
    "success": false,
    "message": "Este usuario no tiene permiso para realizar esta acción"
}
```

### 400 - Error de Validación
```json
{
    "success": false,
    "message": "Ha ocurrido un error de validación",
    "data": {
        "technician_id": [
            "El técnico ya tiene un mantenimiento asignado para esta fecha y turno."
        ]
    }
}
```

### 400 - Estado Incorrecto
```json
{
    "success": false,
    "message": "Ha ocurrido un error de validación",
    "data": {
        "maintenance_id": [
            "Solo se pueden asignar mantenimientos con estado pendiente."
        ]
    }
}
```

## Uso en React Native

### Ejemplo de Implementación

```javascript
// Asignar mantenimiento
const assignMaintenance = async (maintenanceId, assignmentData) => {
  try {
    const response = await fetch(`/api/assignMaintenance/${maintenanceId}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        date_maintenance: assignmentData.date,
        shift: assignmentData.shift, // 'AM' o 'PM'
        value: assignmentData.value,
        spare_parts: assignmentData.spareParts,
        technician_id: assignmentData.technicianId
      })
    });

    const result = await response.json();
    
    if (result.success) {
      // Mantenimiento asignado exitosamente
      console.log('Mantenimiento asignado:', result.data);
    } else {
      // Manejar errores de validación
      console.error('Error:', result.message);
      if (result.data) {
        // Mostrar errores específicos de validación
        Object.keys(result.data).forEach(field => {
          console.error(`${field}: ${result.data[field].join(', ')}`);
        });
      }
    }
  } catch (error) {
    console.error('Error de red:', error);
  }
};
```

### Manejo de Errores Específicos

```javascript
const handleAssignmentError = (errorData) => {
  if (errorData.technician_id) {
    // Técnico no disponible
    showAlert('El técnico seleccionado no está disponible para esta fecha y turno');
  } else if (errorData.maintenance_id) {
    // Mantenimiento no en estado pending
    showAlert('Este mantenimiento ya fue asignado o está en progreso');
  } else if (errorData.date_maintenance) {
    // Fecha inválida
    showAlert('Formato de fecha inválido. Use YYYY-MM-DD');
  }
};
```

### Validación en Frontend

```javascript
const validateAssignmentData = (data) => {
  const errors = {};
  
  // Validar fecha
  if (!data.date || !/^\d{4}-\d{2}-\d{2}$/.test(data.date)) {
    errors.date = 'Fecha requerida en formato YYYY-MM-DD';
  }
  
  // Validar turno
  if (!['AM', 'PM'].includes(data.shift)) {
    errors.shift = 'Turno debe ser AM o PM';
  }
  
  // Validar técnico
  if (!data.technicianId || data.technicianId <= 0) {
    errors.technician = 'Debe seleccionar un técnico';
  }
  
  // Validar repuestos
  if (!data.spareParts || !Array.isArray(data.spareParts) || data.spareParts.length === 0) {
    errors.spareParts = 'Debe incluir al menos un repuesto';
  }
  
  return errors;
};
```

## Consideraciones de Seguridad

1. **Autenticación JWT:** Todas las peticiones requieren token válido
2. **Autorización por Roles:** Solo coordinadores y administradores
3. **Validación de Estado:** Solo mantenimientos pendientes
4. **Validación de Disponibilidad:** Previene conflictos de horario

## Notas de Implementación

- Los campos `value` y `spare_parts` se agregaron en la migración `2025_09_24_002138_add_fields_maintenances_assigned_to_maintenances_table.php`
- Se corrigió la inconsistencia de `square_parts` vs `spare_parts` en el modelo
- Las reglas de validación están optimizadas para usar objetos ya cargados
- El sistema maneja automáticamente el cambio de estado a `assigned`

## Testing

Para probar la funcionalidad:

1. **Caso exitoso:** Mantenimiento en estado `pending` + técnico disponible
2. **Caso de error:** Mantenimiento ya asignado
3. **Caso de error:** Técnico con conflicto de horario
4. **Caso de error:** Usuario sin permisos
