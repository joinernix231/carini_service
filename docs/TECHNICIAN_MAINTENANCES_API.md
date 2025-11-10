# API de Mantenimientos de Técnicos

## Descripción General

Esta API permite a los técnicos obtener sus mantenimientos asignados con diferentes filtros opcionales. La API utiliza el criterio `TechnicianMaintenancesCriteria` para filtrar automáticamente por el técnico autenticado y permite filtros adicionales por estado y rango de fechas.

## Endpoints

### 1. Lista de Mantenimientos

**URL:** `GET /api/technicianMaintenances`

**Método:** GET

**Autenticación:** Requerida (JWT Token)

### 2. Detalle de Mantenimiento

**URL:** `GET /api/technicianMaintenances/{id}`

**Método:** GET

**Autenticación:** Requerida (JWT Token)

**Parámetros de Ruta:**
- `id`: ID del mantenimiento (requerido)

## Headers Requeridos

- `Authorization: Bearer {token}`
- `Content-Type: application/json`
- `Accept: application/json`

## Parámetros de Query (Solo para Lista de Mantenimientos)

| Parámetro | Tipo | Descripción | Valores Válidos | Ejemplo |
|-----------|------|-------------|-----------------|---------|
| `status` | string | Filtrar por estado específico | `assigned`, `in_progress`, `completed` | `?status=assigned` |
| `date_from` | string | Fecha de inicio del rango | Formato: `YYYY-MM-DD` | `?date_from=2024-01-01` |
| `date_to` | string | Fecha de fin del rango | Formato: `YYYY-MM-DD` | `?date_to=2024-01-31` |
| `unpaginated` | boolean | Obtener todos los resultados sin paginación | `true`, `false` | `?unpaginated=true` |

**Nota:** Los parámetros de query solo aplican para el endpoint de lista (`GET /api/technicianMaintenances`). El endpoint de detalle (`GET /api/technicianMaintenances/{id}`) no acepta parámetros de query.

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

### 8. Obtener detalle de un mantenimiento específico
```
GET /api/technicianMaintenances/123
```

## Respuestas

### Respuesta Exitosa - Lista de Mantenimientos

### Estructura de la Respuesta (Lista)

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

### Respuesta Exitosa - Detalle de Mantenimiento

```json
{
  "success": true,
  "message": "Maintenance detail retrieved successfully",
  "data": {
    "id": 1,
    "status": "assigned",
    "date_maintenance": "2024-01-15",
    "description": "Mantenimiento preventivo del dispositivo",
    "created_at": "2024-01-10T10:00:00.000000Z",
    "updated_at": "2024-01-10T10:00:00.000000Z",
    "clientDevices": {
      "client": {
        "id": 1,
        "name": "Cliente Ejemplo",
        "email": "cliente@ejemplo.com",
        "phone": "+1234567890",
        "address": "Dirección del cliente"
      },
      "device": {
        "id": 1,
        "name": "Dispositivo Ejemplo",
        "model": "Modelo XYZ",
        "serial_number": "SN123456789"
      }
    },
    "technician": {
      "user": {
        "id": 1,
        "name": "Técnico Ejemplo",
        "email": "tecnico@ejemplo.com"
      }
    },
    "maintenanceItems": [
      {
        "id": 1,
        "description": "Cambio de filtro",
        "price": 25.50,
        "quantity": 1
      }
    ],
    "maintenanceImages": [
      {
        "id": 1,
        "image_url": "https://example.com/image1.jpg",
        "description": "Imagen antes del mantenimiento"
      }
    ],
    "payments": [
      {
        "id": 1,
        "amount": 100.00,
        "status": "pending",
        "payment_method": "cash"
      }
    ]
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

#### Objeto meta (Solo cuando está paginado - Lista)
- `current_page`: Página actual
- `per_page`: Elementos por página
- `total`: Total de elementos
- `last_page`: Última página

#### Campos Adicionales (Solo en Detalle)
- `description`: Descripción detallada del mantenimiento
- `maintenanceItems`: Array de items/servicios realizados
- `maintenanceImages`: Array de imágenes del mantenimiento
- `payments`: Array de pagos relacionados

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

### Error de Mantenimiento No Encontrado (404)
```json
{
  "success": false,
  "message": "Maintenance not found or not assigned to this technician"
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
| 404 | No encontrado - Mantenimiento no existe o no asignado al técnico |
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

#### Lista de Mantenimientos
- `clientDevices.client`: Información del cliente
- `clientDevices.device`: Información del dispositivo
- `technician.user`: Información del técnico

#### Detalle de Mantenimiento
- `clientDevices.client`: Información completa del cliente
- `clientDevices.device`: Información completa del dispositivo
- `technician.user`: Información del técnico
- `maintenanceItems`: Items/servicios del mantenimiento
- `maintenanceImages`: Imágenes del mantenimiento
- `payments`: Pagos relacionados

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

### 6. Vista de Detalle
Mostrar información completa de un mantenimiento específico incluyendo items, imágenes y pagos.

## Notas de Implementación

- Todos los parámetros de fecha deben estar en formato ISO (YYYY-MM-DD)
- El filtro por estado es case-sensitive
- Los filtros de fecha son inclusivos (incluyen las fechas límite)
- La paginación se aplica solo cuando no se especifica `unpaginated=true`
- Los resultados siempre incluyen las relaciones necesarias para mostrar información completa
- El endpoint de detalle verifica que el mantenimiento esté asignado al técnico autenticado
- El endpoint de detalle incluye información adicional como items, imágenes y pagos

## Limitaciones

- Solo técnicos autenticados pueden acceder a estos endpoints
- Los filtros de fecha no pueden ser anteriores a la fecha de creación del mantenimiento
- El parámetro `date_to` debe ser posterior o igual a `date_from`
- Máximo 20 elementos por página en modo paginado
- El endpoint de detalle solo muestra mantenimientos asignados al técnico autenticado
- Los IDs de mantenimiento deben ser válidos y existir en la base de datos
