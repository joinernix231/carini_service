# API de Mantenimientos de Técnicos

## Descripción General

Esta API permite a los técnicos obtener sus mantenimientos asignados con diferentes filtros opcionales. La API utiliza el criterio `TechnicianMaintenancesCriteria` para filtrar automáticamente por el técnico autenticado y permite filtros adicionales por estado y rango de fechas.

## Endpoint

**URL:** `GET /api/technicianMaintenances`

**Método:** GET

**Autenticación:** Requerida (JWT Token)

## Headers Requeridos

- `Authorization: Bearer {token}`
- `Content-Type: application/json`
- `Accept: application/json`

## Parámetros de Query (Opcionales)

| Parámetro | Tipo | Descripción | Valores Válidos | Ejemplo |
|-----------|------|-------------|-----------------|---------|
| `status` | string | Filtrar por estado específico | `assigned`, `in_progress`, `completed` | `?status=assigned` |
| `date_from` | string | Fecha de inicio del rango | Formato: `YYYY-MM-DD` | `?date_from=2024-01-01` |
| `date_to` | string | Fecha de fin del rango | Formato: `YYYY-MM-DD` | `?date_to=2024-01-31` |
| `unpaginated` | boolean | Obtener todos los resultados sin paginación | `true`, `false` | `?unpaginated=true` |

## Ejemplos de Uso

### 1. Obtener todos los mantenimientos del técnico (paginados)
```
GET /api/technicianMaintenances
```

### 2. Obtener todos los mantenimientos sin paginación
```
GET /api/technicianMaintenances?unpaginated=true
```

### 3. Filtrar por estado específico
```
GET /api/technicianMaintenances?status=assigned
GET /api/technicianMaintenances?status=in_progress
GET /api/technicianMaintenances?status=completed
```

### 4. Filtrar por rango de fechas
```
GET /api/technicianMaintenances?date_from=2024-01-01&date_to=2024-01-31
```

### 5. Filtrar solo por fecha de inicio
```
GET /api/technicianMaintenances?date_from=2024-01-15
```

### 6. Filtrar solo por fecha de fin
```
GET /api/technicianMaintenances?date_to=2024-01-30
```

### 7. Combinar múltiples filtros
```
GET /api/technicianMaintenances?status=assigned&date_from=2024-01-15&date_to=2024-01-30
```

## Respuesta Exitosa

### Estructura de la Respuesta

```json
{
  "success": true,
  "message": "Maintenances retrieved Successfully",
  "data": [
    {
      "id": 1,
      "status": "assigned",
      "date_maintenance": "2024-01-15",
      "created_at": "2024-01-10T10:00:00.000000Z",
      "updated_at": "2024-01-10T10:00:00.000000Z",
      "clientDevices": {
        "client": {
          "id": 1,
          "name": "Cliente Ejemplo",
          "email": "cliente@ejemplo.com"
        },
        "device": {
          "id": 1,
          "name": "Dispositivo Ejemplo",
          "model": "Modelo XYZ"
        }
      },
      "technician": {
        "user": {
          "id": 1,
          "name": "Técnico Ejemplo",
          "email": "tecnico@ejemplo.com"
        }
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 1,
    "last_page": 1
  }
}
```

### Campos de la Respuesta

#### Objeto Principal (Mantenimiento)
- `id`: ID único del mantenimiento
- `status`: Estado del mantenimiento (assigned, in_progress, completed)
- `date_maintenance`: Fecha programada para el mantenimiento
- `created_at`: Fecha de creación del registro
- `updated_at`: Fecha de última actualización

#### Objeto clientDevices
- `client`: Información del cliente
  - `id`: ID del cliente
  - `name`: Nombre del cliente
  - `email`: Email del cliente
- `device`: Información del dispositivo
  - `id`: ID del dispositivo
  - `name`: Nombre del dispositivo
  - `model`: Modelo del dispositivo

#### Objeto technician
- `user`: Información del técnico
  - `id`: ID del usuario técnico
  - `name`: Nombre del técnico
  - `email`: Email del técnico

#### Objeto meta (Solo cuando está paginado)
- `current_page`: Página actual
- `per_page`: Elementos por página
- `total`: Total de elementos
- `last_page`: Última página

## Respuesta de Error

### Error de Autenticación (401)
```json
{
  "success": false,
  "message": "Unauthorized",
  "error": "Token inválido o expirado"
}
```

### Error de Validación (422)
```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "status": ["El estado debe ser uno de: assigned, in_progress, completed"],
    "date_from": ["La fecha debe tener el formato Y-m-d"],
    "date_to": ["La fecha debe ser posterior o igual a date_from"]
  }
}
```

### Error del Servidor (500)
```json
{
  "success": false,
  "message": "Internal Server Error",
  "error": "Error interno del servidor"
}
```

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | Éxito - Mantenimientos obtenidos correctamente |
| 401 | No autorizado - Token inválido o faltante |
| 422 | Error de validación - Parámetros inválidos |
| 500 | Error interno del servidor |

## Características Especiales

### Filtrado Automático
- La API filtra automáticamente por el `technician_id` de la sesión del usuario autenticado
- No es necesario enviar el ID del técnico como parámetro

### Ordenamiento
- Los resultados se ordenan por `date_maintenance` (ascendente)
- Como criterio secundario se ordena por `created_at` (ascendente)

### Paginación
- Por defecto, los resultados están paginados (20 elementos por página)
- Usar `unpaginated=true` para obtener todos los resultados

### Relaciones Incluidas
- `clientDevices.client`: Información del cliente
- `clientDevices.device`: Información del dispositivo
- `technician.user`: Información del técnico

## Casos de Uso Comunes

### 1. Dashboard del Técnico
Obtener todos los mantenimientos asignados para mostrar en el dashboard principal.

### 2. Filtro por Estado
Mostrar solo mantenimientos en un estado específico (ej: solo los asignados).

### 3. Vista de Calendario
Obtener mantenimientos de un rango de fechas específico para mostrar en un calendario.

### 4. Reporte Mensual
Obtener todos los mantenimientos de un mes específico sin paginación.

### 5. Búsqueda Combinada
Combinar filtros para encontrar mantenimientos específicos (ej: asignados en la última semana).

## Notas de Implementación

- Todos los parámetros de fecha deben estar en formato ISO (YYYY-MM-DD)
- El filtro por estado es case-sensitive
- Los filtros de fecha son inclusivos (incluyen las fechas límite)
- La paginación se aplica solo cuando no se especifica `unpaginated=true`
- Los resultados siempre incluyen las relaciones necesarias para mostrar información completa

## Limitaciones

- Solo técnicos autenticados pueden acceder a este endpoint
- Los filtros de fecha no pueden ser anteriores a la fecha de creación del mantenimiento
- El parámetro `date_to` debe ser posterior o igual a `date_from`
- Máximo 20 elementos por página en modo paginado
