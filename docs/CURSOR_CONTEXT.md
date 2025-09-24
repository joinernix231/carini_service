# Contexto para Cursor del Futuro - Sistema de Asignación de Mantenimientos

## 🎯 Resumen del Sistema Implementado

Este proyecto implementa un sistema completo de asignación de mantenimientos a técnicos con validaciones de disponibilidad por turnos (AM/PM). El sistema respeta el principio de responsabilidad única y utiliza una arquitectura de repositorios.

## 🏗️ Arquitectura Implementada

### **Principio de Responsabilidad Única Aplicado:**
- **Controladores:** Solo manejan HTTP (request/response)
- **Repositorios:** Contienen toda la lógica de negocio y consultas
- **Requests:** Solo validación de datos
- **Resources:** Solo transformación de datos para API

### **Endpoints Principales:**
1. `GET /api/availableTechnicians` - Técnicos disponibles (con filtros opcionales)
2. `PUT /api/assignMaintenance/{maintenance}` - Asignar mantenimiento a técnico
3. `GET /api/maintenancesNotAssigned` - Mantenimientos pendientes

## 🔧 Funcionalidades Clave

### **Sistema de Disponibilidad por Turnos:**
- Un técnico puede tener mantenimiento en AM y estar disponible en PM
- Un técnico puede tener mantenimiento en PM y estar disponible en AM
- Solo se excluyen técnicos con mantenimientos en el mismo turno

### **Validaciones Implementadas:**
- Solo coordinadores/administradores pueden asignar
- Solo mantenimientos con estado 'pending' son asignables
- Verificación de disponibilidad del técnico por fecha y turno
- Validación de datos requeridos (fecha, turno, técnico, repuestos)

## 📁 Estructura de Archivos Importantes

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Maintenance/MaintenanceNotAssignedAPIController.php
│   │   └── Technician/AvailableTechnicianAPIController.php
│   ├── Requests/
│   │   ├── Maintenance/AssignMaintenanceAPIRequest.php
│   │   └── Technician/AvailableTechnicianAPIRequest.php
│   └── Resources/
│       └── Maintenance/MaintenanceResource.php
├── Models/
│   └── Maintenance/Maintenance.php (con relación directa 'client')
├── Repositories/
│   ├── Maintenance/MaintenanceRepository.php
│   └── Technician/TechnicianRepository.php
└── Rules/
    ├── Maintenance/MaintenancePendingStatusRule.php
    └── Maintenance/TechnicianAvailabilityRule.php
```

## 🎨 Patrones de Diseño Utilizados

### **Repository Pattern:**
```php
// En TechnicianRepository
public function getAvailableTechnicians(?string $date = null, ?string $shift = null)
{
    // Lógica de negocio centralizada aquí
}
```

### **Custom Validation Rules:**
```php
// Reglas personalizadas para validaciones complejas
new TechnicianAvailabilityRule($date, $shift)
new MaintenancePendingStatusRule($maintenance)
```

### **Resource Pattern:**
```php
// Transformación limpia de datos para API
'client' => new ClientResource($this->whenLoaded('client'))
```

## 🔄 Flujo de Asignación

1. **Obtener técnicos disponibles:** `GET /api/availableTechnicians?date=2024-01-15&shift=AM`
2. **Seleccionar técnico y asignar:** `PUT /api/assignMaintenance/{id}`
3. **Validaciones automáticas:** Estado, disponibilidad, autorización
4. **Actualización:** Mantenimiento cambia a estado 'assigned'

## 📊 Estados de Mantenimiento

| Estado | Asignable | Descripción |
|--------|-----------|-------------|
| `pending` | ✅ Sí | Pendiente de asignación |
| `assigned` | ❌ No | Ya asignado a técnico |
| `in_progress` | ❌ No | En progreso |
| `completed` | ❌ No | Completado |

## 🛡️ Seguridad y Validaciones

- **Autenticación JWT:** Todas las peticiones requieren token
- **Autorización por Roles:** Solo coordinadores/administradores
- **Validación de Estado:** Solo mantenimientos 'pending'
- **Validación de Disponibilidad:** Previene conflictos de horario
- **Validación de Datos:** Campos requeridos y formatos correctos

## 🚀 Para React Native

El sistema está completamente documentado con ejemplos de implementación en React Native en:
- `docs/REACT_NATIVE_EXAMPLES.md` - Componentes y servicios
- `docs/API_MAINTENANCE_ASSIGNMENT.md` - Documentación completa de API

## 💡 Notas Importantes

1. **Relación Cliente:** Se implementó relación directa `client()` en Maintenance para optimizar consultas
2. **Corrección de Campos:** Se corrigió `square_parts` → `spare_parts` en todo el sistema
3. **Filtrado Eficiente:** Los repositorios manejan toda la lógica de filtrado
4. **Respuestas JSON:** Sistema consistente de respuestas con `success`, `message`, `data`

## 🔧 Para Desarrollar

- Usar los repositorios para cualquier lógica de negocio
- Mantener controladores simples (solo HTTP)
- Crear reglas de validación personalizadas para lógica compleja
- Usar Resources para transformación de datos
- Seguir el patrón de responsabilidad única

## 📝 Comandos Útiles

```bash
# Verificar técnicos disponibles
GET /api/availableTechnicians

# Con filtros de fecha y turno
GET /api/availableTechnicians?date=2024-01-15&shift=AM

# Asignar mantenimiento
PUT /api/assignMaintenance/123
{
    "date_maintenance": "2024-01-15",
    "shift": "AM",
    "technician_id": 1,
    "value": 50000,
    "spare_parts": ["filtro", "aceite"]
}
```

---

**¡El sistema está listo para producción y completamente documentado!** 🎉
