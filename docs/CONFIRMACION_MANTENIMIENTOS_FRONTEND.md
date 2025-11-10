# 📱 Guía de Confirmación de Mantenimientos - Frontend

## 🎯 ¿Qué es esto?

Implementamos un sistema donde **el cliente debe confirmar el mantenimiento** después de que el coordinador asigne un técnico, fecha y turno. Si el cliente no confirma en **1 hora**, el coordinador recibe una notificación para llamar al cliente.

---

## 🔄 Flujo Completo

### **1. Coordinador Asigna Técnico** 
Cuando el coordinador asigna técnico + fecha + turno:
- ✅ El mantenimiento entra en estado `assigned`
- ✅ Se activa `confirmation_required = true`
- ✅ Se establece un plazo de 1 hora (`confirmation_deadline`)
- 📱 **El cliente recibe una notificación** pidiendo confirmación

### **2. Cliente Confirma (Opcional)**
El cliente puede confirmar el mantenimiento:
- ✅ El mantenimiento se marca como confirmado
- 📱 **El técnico recibe notificación** de que puede iniciar
- 📱 **El coordinador recibe notificación** de que el cliente confirmó

### **3. Cliente NO Confirma (Después de 1 hora)**
Si el cliente no confirma en 1 hora:
- 📱 **El coordinador recibe notificación** automática
- El coordinador puede llamar al cliente y marcar como "llamado"

### **4. Técnico Inicia Mantenimiento**
Antes de iniciar, el sistema verifica:
- ⚠️ Si requiere confirmación Y no está confirmado → **NO puede iniciar**
- ✅ Si está confirmado o no requiere confirmación → **Puede iniciar**

---

## 📡 Endpoints Disponibles

### **1. Confirmar Mantenimiento (Cliente)**
```
POST /api/maintenances/{maintenanceId}/confirm
```

**¿Quién lo usa?** Cliente

**¿Cuándo?** Cuando el cliente acepta el mantenimiento asignado

**Body:** No requiere body (todo viene del token de sesión)

**Respuesta:**
```json
{
  "success": true,
  "message": "Maintenance confirmed successfully",
  "data": {
    "id": 123,
    "status": "assigned",
    "confirmation_required": false,
    "confirmed_at": "2025-01-15T10:30:00Z",
    // ... resto de datos del mantenimiento
  }
}
```

**Errores posibles:**
- `403`: El usuario no es cliente o no es dueño del mantenimiento
- `422`: El mantenimiento no puede ser confirmado (ya confirmado, no requiere confirmación, etc.)

---

### **2. Marcar como Llamado (Coordinador)**
```
POST /api/maintenances/{maintenanceId}/mark-as-called
```

**¿Quién lo usa?** Coordinador o Administrador

**¿Cuándo?** Cuando el coordinador llama al cliente después de recibir la notificación

**Body:** No requiere body

**Respuesta:**
```json
{
  "success": true,
  "message": "Marked as called successfully",
  "data": {
    "id": 123,
    "coordinator_called": true,
    "coordinator_called_at": "2025-01-15T11:30:00Z"
  }
}
```

---

### **3. Listar Mantenimientos Sin Confirmar (Coordinador)**
```
GET /api/coordinator/maintenances/unconfirmed
```

**¿Quién lo usa?** Coordinador o Administrador

**¿Cuándo?** Para ver todos los mantenimientos que el cliente no ha confirmado

**Query Params (opcionales):**
- `unpaginated`: boolean - Si quieres todos sin paginar
- `filters`: string - Filtros adicionales

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "client": {
        "id": 1,
        "name": "Juan Pérez",
        "phone": "3001234567"
      },
      "technician": {
        "id": 5,
        "user": {
          "name": "Carlos Técnico"
        }
      },
      "date_maintenance": "2025-01-20",
      "shift": "AM",
      "confirmation_deadline": "2025-01-15T10:00:00Z",
      "coordinator_notified": true,
      "coordinator_notified_at": "2025-01-15T10:05:00Z"
    }
  ]
}
```

---

## 🔔 Notificaciones que Recibirás

### **1. Para Cliente: Técnico Asignado - Requiere Confirmación**

**Cuándo:** Cuando el coordinador asigna técnico

**Tipo:** `maintenance_assigned_requires_confirmation`

**Contenido:**
```
Título: "🔧 Técnico Asignado - Confirma tu Mantenimiento"
Mensaje: "Se ha asignado el técnico {nombre_tecnico} para el {fecha} ({turno}). Por favor confirma tu mantenimiento."
```

**Datos adicionales:**
```json
{
  "type": "maintenance_assigned_requires_confirmation",
  "maintenance_id": 123,
  "date_maintenance": "2025-01-20",
  "shift": "AM",
  "confirmation_deadline": "2025-01-15T11:00:00Z",
  "screen": "MantenimientoDetail"
}
```

**Acción del Frontend:**
- Mostrar botón de confirmación en la pantalla de detalle
- Mostrar contador de tiempo restante para confirmar

---

### **2. Para Coordinador: Cliente No Confirmó**

**Cuándo:** Después de 1 hora sin confirmar

**Tipo:** `maintenance_unconfirmed`

**Contenido:**
```
Título: "🚨 Cliente No Ha Confirmado Mantenimiento"
Mensaje: "{nombre_cliente} no ha confirmado el mantenimiento #{id} programado para {fecha}. Por favor, llámalo."
```

**Datos adicionales:**
```json
{
  "type": "maintenance_unconfirmed",
  "maintenance_id": 123,
  "client_id": 1,
  "client_name": "Juan Pérez",
  "client_phone": "3001234567",
  "date_maintenance": "2025-01-20",
  "screen": "MantenimientoDetail"
}
```

**Acción del Frontend:**
- Mostrar lista de mantenimientos sin confirmar
- Permitir llamar al cliente (usando teléfono)
- Mostrar botón para marcar como "llamado"

---

### **3. Para Coordinador: Cliente Confirmó**

**Cuándo:** Cuando el cliente confirma

**Tipo:** `maintenance_confirmed`

**Contenido:**
```
Título: "✅ Cliente Confirmó Mantenimiento"
Mensaje: "{nombre_cliente} ha confirmado el mantenimiento #{id}. El técnico puede iniciar."
```

**Datos adicionales:**
```json
{
  "type": "maintenance_confirmed",
  "maintenance_id": 123,
  "confirmed_at": "2025-01-15T10:30:00Z",
  "screen": "MantenimientoDetail"
}
```

---

### **4. Para Técnico: Mantenimiento Confirmado**

**Cuándo:** Cuando el cliente confirma

**Tipo:** `maintenance_confirmed_by_client`

**Contenido:**
```
Título: "✅ Mantenimiento Confirmado"
Mensaje: "El cliente ha confirmado el mantenimiento programado para {fecha} ({turno}). Ya puedes iniciar."
```

**Datos adicionales:**
```json
{
  "type": "maintenance_confirmed_by_client",
  "maintenance_id": 123,
  "date_maintenance": "2025-01-20",
  "shift": "AM",
  "confirmed_at": "2025-01-15T10:30:00Z",
  "screen": "MantenimientoDetail"
}
```

**Acción del Frontend:**
- Habilitar botón de "Iniciar Mantenimiento" si estaba deshabilitado

---

## 📊 Campos Nuevos en Maintenance

Todos los mantenimientos ahora incluyen estos campos adicionales:

```json
{
  "confirmation_required": true/false,
  "confirmed_at": "2025-01-15T10:30:00Z" o null,
  "confirmation_deadline": "2025-01-15T11:00:00Z" o null,
  "coordinator_notified": true/false,
  "coordinator_notified_at": "2025-01-15T11:05:00Z" o null,
  "coordinator_called": true/false,
  "coordinator_called_at": "2025-01-15T11:30:00Z" o null
}
```

---

## 🎨 Implementación Frontend

### **Para Cliente:**

1. **Pantalla de Detalle de Mantenimiento:**
   - Mostrar estado: "Pendiente de confirmación" si `confirmation_required = true` y `confirmed_at = null`
   - Mostrar botón "Confirmar Mantenimiento" si está pendiente
   - Mostrar contador regresivo hasta `confirmation_deadline`
   - Mostrar "Confirmado" con fecha si `confirmed_at` tiene valor

2. **Lista de Mantenimientos:**
   - Filtrar por `status = 'assigned'` + `confirmation_required = true` + `confirmed_at = null` para ver pendientes de confirmar
   - Mostrar badge o indicador visual de "Pendiente de confirmación"

3. **Al recibir notificación:**
   - Redirigir a pantalla de detalle del mantenimiento
   - Resaltar botón de confirmación

---

### **Para Coordinador:**

1. **Pantalla de Mantenimientos Sin Confirmar:**
   - Usar endpoint `/api/coordinator/maintenances/unconfirmed`
   - Mostrar lista con:
     - Nombre del cliente
     - Teléfono del cliente (clickeable para llamar)
     - Fecha programada
     - Tiempo desde que pasó el deadline
   - Botón para marcar como "llamado"

2. **Pantalla de Detalle:**
   - Mostrar información de confirmación
   - Botón para llamar al cliente (usando teléfono)
   - Botón para marcar como "llamado"

3. **Al recibir notificación:**
   - Mostrar alerta o badge con mantenimientos sin confirmar
   - Redirigir a lista de mantenimientos sin confirmar

---

### **Para Técnico:**

1. **Pantalla de Detalle de Mantenimiento:**
   - Mostrar estado: "Esperando confirmación del cliente" si `confirmation_required = true` y `confirmed_at = null`
   - **Deshabilitar botón "Iniciar Mantenimiento"** si requiere confirmación y no está confirmado
   - Mostrar mensaje: "El cliente aún no ha confirmado este mantenimiento. No puedes iniciarlo."
   - Habilitar botón cuando `confirmed_at` tenga valor

2. **Al recibir notificación:**
   - Habilitar botón de "Iniciar Mantenimiento" si estaba deshabilitado
   - Mostrar mensaje de éxito indicando que ya puede iniciar

---

## ⚠️ Validaciones Importantes

### **1. Cliente no puede iniciar mantenimiento**
Solo el técnico puede iniciar. El cliente solo confirma.

### **2. Técnico no puede iniciar sin confirmación**
Si `confirmation_required = true` y `confirmed_at = null`, el backend rechazará la petición de inicio con:
```json
{
  "success": false,
  "message": "El cliente aún no ha confirmado este mantenimiento. No puedes iniciarlo."
}
```

### **3. Solo un cliente puede confirmar**
Solo el cliente dueño del mantenimiento puede confirmarlo. El backend valida esto automáticamente.

---

## 🕐 Manejo de Tiempo

### **Confirmation Deadline:**
- Se establece automáticamente: **1 hora después de la asignación**
- Formato: ISO 8601 (ej: `2025-01-15T11:00:00Z`)
- El frontend debe mostrar cuánto tiempo queda para confirmar

### **Ejemplo de contador:**
```
Si confirmation_deadline = "2025-01-15T11:00:00Z" y ahora = "2025-01-15T10:30:00Z"
Mostrar: "Tienes 30 minutos para confirmar"
```

---

## 🎯 Checklist de Implementación Frontend

### **Cliente:**
- [ ] Mostrar estado de confirmación en pantalla de detalle
- [ ] Botón "Confirmar Mantenimiento" visible cuando aplica
- [ ] Contador regresivo hasta deadline
- [ ] Manejar notificación `maintenance_assigned_requires_confirmation`
- [ ] Filtrar mantenimientos pendientes de confirmar

### **Coordinador:**
- [ ] Pantalla de mantenimientos sin confirmar
- [ ] Botón para llamar al cliente (usar teléfono)
- [ ] Botón para marcar como "llamado"
- [ ] Manejar notificaciones `maintenance_unconfirmed` y `maintenance_confirmed`
- [ ] Mostrar información del cliente (nombre, teléfono)

### **Técnico:**
- [ ] Deshabilitar botón "Iniciar" si requiere confirmación
- [ ] Mostrar mensaje cuando no puede iniciar
- [ ] Habilitar botón cuando se confirma
- [ ] Manejar notificación `maintenance_confirmed_by_client`

---

## 📝 Ejemplos de Uso

### **Ejemplo 1: Cliente confirma mantenimiento**

```javascript
// En la pantalla de detalle del mantenimiento
const handleConfirm = async () => {
  try {
    const response = await api.post(`/maintenances/${maintenanceId}/confirm`);
    // El mantenimiento ahora tiene confirmed_at con valor
    // Redirigir o actualizar la UI
    showSuccessMessage('Mantenimiento confirmado exitosamente');
    refreshMaintenanceData();
  } catch (error) {
    showErrorMessage(error.response.data.message);
  }
};
```

### **Ejemplo 2: Coordinador marca como llamado**

```javascript
// En la pantalla de detalle o lista
const handleMarkAsCalled = async () => {
  try {
    await api.post(`/maintenances/${maintenanceId}/mark-as-called`);
    showSuccessMessage('Marcado como llamado');
    refreshMaintenanceData();
  } catch (error) {
    showErrorMessage('Error al marcar como llamado');
  }
};
```

### **Ejemplo 3: Técnico intenta iniciar sin confirmación**

```javascript
// En la pantalla de detalle del mantenimiento
const handleStart = async () => {
  // Validar en frontend primero
  if (maintenance.confirmation_required && !maintenance.confirmed_at) {
    showWarningMessage('El cliente aún no ha confirmado este mantenimiento');
    return;
  }

  try {
    await api.post(`/technicianMaintenances/${maintenanceId}/start`, {
      latitude: currentLocation.lat,
      longitude: currentLocation.lng
    });
    // Éxito
  } catch (error) {
    // El backend también rechazará si no está confirmado
    showErrorMessage(error.response.data.message);
  }
};
```

---

## ❓ Preguntas Frecuentes

**P: ¿Qué pasa si el cliente confirma después del deadline?**
R: El cliente puede confirmar en cualquier momento. El deadline solo afecta la notificación al coordinador.

**P: ¿Puede un cliente confirmar dos veces?**
R: No, el backend rechazará la segunda confirmación con un error.

**P: ¿Qué pasa si el coordinador no llama?**
R: El sistema solo notifica. El coordinador puede marcar como "llamado" cuando quiera, o no hacerlo.

**P: ¿Puede el técnico iniciar si el coordinador no fue notificado?**
R: Sí, solo importa si el cliente confirmó o no. La notificación al coordinador es independiente.

**P: ¿Se puede desactivar la confirmación?**
R: No desde el frontend. Solo el backend puede cambiar `confirmation_required`.

---

## 📞 Soporte

Si tienes dudas sobre la implementación o encuentras algún problema, contacta al equipo de backend.

---

**Última actualización:** 2025-01-15


