# Resumen Técnico - Sistema de Asignación de Mantenimientos

## Arquitectura Implementada

### Backend (Laravel)
- **Request:** `AssignMaintenanceAPIRequest` - Validación completa con reglas personalizadas
- **Controller:** `MaintenanceNotAssignedAPIController` - Manejo de la lógica de asignación
- **Repository:** `MaintenanceRepository` - Persistencia de datos
- **Rules:** Validaciones personalizadas para estado y disponibilidad
- **Model:** `Maintenance` - Modelo actualizado con nuevos campos

### Frontend (React Native)
- **Service:** Clase para manejo de API con autenticación JWT
- **Hooks:** Hook personalizado para estado de asignación
- **Components:** Formulario de asignación y lista de mantenimientos
- **Error Handling:** Manejo específico de errores de validación

## Validaciones Implementadas

### 1. Autorización
```php
// Solo coordinadores y administradores
$allowedRoles = ['coordinador','administrador'];
$user = session('user');
return in_array($user->role, $allowedRoles);
```

### 2. Estado del Mantenimiento
```php
// Solo mantenimientos en estado 'pending'
if ($this->maintenance->status !== 'pending') {
    $fail('Solo se pueden asignar mantenimientos con estado pendiente.');
}
```

### 3. Disponibilidad del Técnico
```php
// Verificar conflictos de horario
$exists = Maintenance::where('technician_id', $value)
    ->where('date_maintenance', $this->date)
    ->where('shift', $this->shift)
    ->whereIn('status', ['assigned', 'in_progress'])
    ->exists();
```

## Campos de Base de Datos

### Tabla: maintenances
```sql
-- Campos existentes
id, client_device_id, type, date_maintenance, shift, photo, description, status, technician_id

-- Campos agregados
value DECIMAL(10,2)           -- Valor del mantenimiento
spare_parts JSON             -- Repuestos necesarios
is_paid BOOLEAN              -- Estado de pago
payment_support VARCHAR      -- Soporte de pago
```

## Endpoints API

### Asignar Mantenimiento
```
PUT /api/assignMaintenance/{maintenance}
Authorization: Bearer {jwt_token}
Content-Type: application/json

Body:
{
    "date_maintenance": "2024-01-15",
    "shift": "AM",
    "value": 50000,
    "spare_parts": ["filtro", "aceite"],
    "technician_id": 1
}
```

### Respuesta Exitosa
```json
{
    "success": true,
    "message": "Maintenance assigned Successfully",
    "data": { /* Maintenance object */ }
}
```

### Respuesta de Error
```json
{
    "success": false,
    "message": "Ha ocurrido un error de validación",
    "data": {
        "technician_id": ["El técnico ya tiene un mantenimiento asignado para esta fecha y turno."]
    }
}
```

## Flujo de Datos

1. **Frontend** → Envía petición PUT con datos de asignación
2. **Request** → Valida autorización, estado y disponibilidad
3. **Controller** → Procesa la asignación
4. **Repository** → Actualiza el mantenimiento
5. **Response** → Retorna resultado o errores

## Archivos Modificados/Creados

### Nuevos Archivos
- `app/Http/Requests/Maintenance/AssignMaintenanceAPIRequest.php`
- `app/Http/Controllers/Maintenance/MaintenanceNotAssignedAPIController.php`
- `app/Rules/Maintenance/MaintenancePendingStatusRule.php`
- `app/Rules/Maintenance/TechnicianAvailabilityRule.php`
- `database/migrations/2025_09_24_002138_add_fields_maintenances_assigned_to_maintenances_table.php`

### Archivos Modificados
- `app/Models/Maintenance/Maintenance.php` - Agregados campos y corrección de `square_parts` → `spare_parts`
- `app/Repositories/Maintenance/MaintenanceRepository.php` - Método `assignMaintenance`
- `routes/api.php` - Ruta para asignación

## Consideraciones de Seguridad

1. **Autenticación JWT:** Todas las peticiones requieren token válido
2. **Autorización por Roles:** Solo coordinadores y administradores
3. **Validación de Estado:** Solo mantenimientos pendientes
4. **Validación de Disponibilidad:** Previene conflictos de horario
5. **Validación de Datos:** Campos requeridos y formatos correctos

## Estados de Mantenimiento

| Estado | Descripción | Asignable | Siguiente Estado |
|--------|-------------|-----------|------------------|
| `pending` | Pendiente de asignación | ✅ Sí | `assigned` |
| `assigned` | Asignado a técnico | ❌ No | `in_progress` |
| `in_progress` | En progreso | ❌ No | `completed` |
| `completed` | Completado | ❌ No | - |

## Optimizaciones Implementadas

1. **Uso de Objetos Existentes:** Las reglas usan objetos ya cargados por Laravel
2. **Validación Eficiente:** No consultas adicionales innecesarias
3. **Manejo de Errores JSON:** Respuestas consistentes para API
4. **Validación en Frontend:** Validación básica antes de enviar

## Testing Scenarios

### Casos Exitosos
- Mantenimiento `pending` + técnico disponible
- Datos válidos + autorización correcta

### Casos de Error
- Mantenimiento ya asignado (`assigned`, `in_progress`, `completed`)
- Técnico con conflicto de horario
- Usuario sin permisos
- Datos inválidos (fecha, turno, técnico)

## Dependencias

### Backend
- Laravel Framework
- JWT Authentication
- Eloquent ORM

### Frontend
- React Native
- AsyncStorage (para tokens)
- Picker (para selecciones)

## Notas de Implementación

1. **Corrección de Inconsistencias:** Se corrigió `square_parts` → `spare_parts` en todo el sistema
2. **Optimización de Consultas:** Las reglas usan objetos ya cargados
3. **Manejo de Errores:** Respuestas JSON consistentes
4. **Validación Completa:** Estado + disponibilidad + autorización
5. **Documentación Completa:** Para facilitar mantenimiento futuro

## Próximos Pasos Sugeridos

1. **Testing:** Implementar tests unitarios y de integración
2. **Logging:** Agregar logs para auditoría de asignaciones
3. **Notificaciones:** Notificar a técnicos sobre nuevas asignaciones
4. **Dashboard:** Panel para coordinar asignaciones
5. **Reportes:** Estadísticas de asignaciones y rendimiento
