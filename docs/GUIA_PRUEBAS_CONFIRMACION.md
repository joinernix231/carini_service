# 🧪 Guía de Pruebas - Flujo de Confirmación de Mantenimientos

## 📋 Resumen del Flujo

El flujo de confirmación funciona de la siguiente manera:

1. **Coordinador asigna técnico** → Se activa `confirmation_required = true` y se establece `confirmation_deadline` (1 hora)
2. **Cliente recibe notificación** → Puede confirmar el mantenimiento
3. **Cliente confirma** → Se marca como confirmado y se notifica al técnico y coordinador
4. **Cliente NO confirma** → Después de 1 hora, el coordinador recibe notificación
5. **Técnico intenta iniciar** → Solo puede iniciar si está confirmado o no requiere confirmación

---

## 🎯 Pasos para Probar el Flujo Completo

### **Prerequisitos**

Antes de comenzar, necesitas:

1. **Usuarios de prueba:**
   - 1 Coordinador (o Administrador)
   - 1 Cliente con dispositivo vinculado
   - 1 Técnico
   - Todos deben tener tokens de notificación registrados

2. **Mantenimiento en estado `pending` o `quoted`** que pueda ser asignado

3. **Herramientas:**
   - Postman, Insomnia, o similar para hacer peticiones HTTP
   - Acceso a la base de datos para verificar estados (opcional)
   - Logs del sistema para verificar notificaciones

---

## 📝 Escenario 1: Cliente Confirma el Mantenimiento (Flujo Exitoso)

### **Paso 1: Coordinador Asigna Técnico**

**Endpoint:**
```
PUT /api/assignMaintenance/{maintenanceId}
```

**Headers:**
```
Authorization: Bearer {token_coordinador}
Content-Type: application/json
```

**Body:**
```json
{
  "technician_id": 1,
  "date_maintenance": "2025-01-20",
  "shift": "AM",
  "value": 50000
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Maintenance assigned Successfully",
  "data": {
    "id": 123,
    "status": "assigned",
    "confirmation_required": true,
    "confirmation_deadline": "2025-01-15T11:00:00Z",
    "confirmed_at": null,
    "technician_id": 1,
    "date_maintenance": "2025-01-20",
    "shift": "AM"
  }
}
```

**✅ Verificaciones:**
- `status` debe ser `"assigned"`
- `confirmation_required` debe ser `true`
- `confirmation_deadline` debe estar establecido (1 hora desde ahora)
- `confirmed_at` debe ser `null`

**📱 Notificaciones esperadas:**
- Cliente recibe: `maintenance_assigned_requires_confirmation`
- Técnico recibe: `maintenance_assigned` (notificación normal de asignación)

---

### **Paso 2: Verificar que el Cliente Puede Ver el Mantenimiento Pendiente de Confirmación**

**Endpoint:**
```
GET /api/maintenances/{maintenanceId}
```

**Headers:**
```
Authorization: Bearer {token_cliente}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "status": "assigned",
    "confirmation_required": true,
    "confirmation_deadline": "2025-01-15T11:00:00Z",
    "confirmed_at": null,
    "technician": {
      "id": 1,
      "user": {
        "name": "Carlos Técnico"
      }
    },
    "date_maintenance": "2025-01-20",
    "shift": "AM"
  }
}
```

**✅ Verificaciones:**
- El cliente puede ver el mantenimiento
- Los campos de confirmación están presentes
- El técnico asignado es visible

---

### **Paso 3: Cliente Confirma el Mantenimiento**

**Endpoint:**
```
POST /api/maintenances/{maintenanceId}/confirm
```

**Headers:**
```
Authorization: Bearer {token_cliente}
Content-Type: application/json
```

**Body:** (No requiere body)

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Maintenance confirmed successfully",
  "data": {
    "id": 123,
    "status": "assigned",
    "confirmation_required": false,
    "confirmed_at": "2025-01-15T10:30:00Z",
    "confirmation_deadline": "2025-01-15T11:00:00Z"
  }
}
```

**✅ Verificaciones:**
- `confirmation_required` cambió a `false`
- `confirmed_at` tiene un valor (timestamp de confirmación)
- `status` sigue siendo `"assigned"`

**📱 Notificaciones esperadas:**
- Técnico recibe: `maintenance_confirmed_by_client`
- Coordinador recibe: `maintenance_confirmed`

---

### **Paso 4: Técnico Puede Iniciar el Mantenimiento**

**Endpoint:**
```
POST /api/technicianMaintenances/{maintenanceId}/start
```

**Headers:**
```
Authorization: Bearer {token_tecnico}
Content-Type: application/json
```

**Body:**
```json
{
  "latitude": 4.6097,
  "longitude": -74.0817,
  "notes": "Iniciando mantenimiento"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Maintenance started successfully",
  "data": {
    "id": 123,
    "status": "in_progress"
  }
}
```

**✅ Verificaciones:**
- El técnico puede iniciar sin problemas
- El estado cambia a `in_progress`

---

## 📝 Escenario 2: Cliente NO Confirma (Después de 1 Hora)

### **Paso 1: Coordinador Asigna Técnico**

Igual que el Escenario 1, Paso 1.

---

### **Paso 2: Esperar 1 Hora (o Simular el Tiempo)**

**Opción A: Esperar realmente 1 hora** (no recomendado para pruebas)

**Opción B: Modificar directamente en la base de datos** (para pruebas rápidas):
```sql
UPDATE maintenances 
SET confirmation_deadline = NOW() - INTERVAL 1 MINUTE
WHERE id = {maintenanceId};
```

**Opción C: Usar un comando o script que simule el paso del tiempo**

---

### **Paso 3: Verificar Notificación al Coordinador**

Después de que pase el deadline, el coordinador debería recibir una notificación automática.

**Verificar notificaciones del coordinador:**
```
GET /api/notifications/user-notifications
```

**Headers:**
```
Authorization: Bearer {token_coordinador}
```

**Buscar notificación de tipo:**
```json
{
  "type": "maintenance_unconfirmed",
  "maintenance_id": 123,
  "client_name": "Juan Pérez",
  "client_phone": "3001234567"
}
```

**✅ Verificaciones:**
- El coordinador recibió la notificación `maintenance_unconfirmed`
- La notificación contiene información del cliente (nombre, teléfono)

---

### **Paso 4: Coordinador Ve Lista de Mantenimientos Sin Confirmar**

**Endpoint:**
```
GET /api/coordinator/maintenances/unconfirmed
```

**Headers:**
```
Authorization: Bearer {token_coordinador}
```

**Respuesta esperada:**
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
        "id": 1,
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

**✅ Verificaciones:**
- La lista muestra mantenimientos sin confirmar
- Incluye información del cliente (nombre, teléfono)
- Muestra cuándo fue notificado el coordinador

---

### **Paso 5: Coordinador Marca como Llamado (Opcional)**

**Endpoint:**
```
POST /api/maintenances/{maintenanceId}/mark-as-called
```

**Headers:**
```
Authorization: Bearer {token_coordinador}
Content-Type: application/json
```

**Body:** (No requiere body)

**Respuesta esperada:**
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

**✅ Verificaciones:**
- `coordinator_called` cambió a `true`
- `coordinator_called_at` tiene un valor

---

### **Paso 6: Técnico NO Puede Iniciar (Sin Confirmación)**

**Endpoint:**
```
POST /api/technicianMaintenances/{maintenanceId}/start
```

**Headers:**
```
Authorization: Bearer {token_tecnico}
Content-Type: application/json
```

**Body:**
```json
{
  "latitude": 4.6097,
  "longitude": -74.0817
}
```

**Respuesta esperada (Error):**
```json
{
  "success": false,
  "message": "El cliente aún no ha confirmado este mantenimiento. No puedes iniciarlo."
}
```

**✅ Verificaciones:**
- El técnico NO puede iniciar
- Recibe un mensaje de error claro
- El estado del mantenimiento sigue siendo `assigned`

---

## 📝 Escenario 3: Cliente Confirma Después del Deadline

### **Paso 1-2: Igual que Escenario 2**

Asignar técnico y esperar que pase el deadline.

---

### **Paso 3: Cliente Confirma (Aunque Pasó el Deadline)**

**Endpoint:**
```
POST /api/maintenances/{maintenanceId}/confirm
```

**Headers:**
```
Authorization: Bearer {token_cliente}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Maintenance confirmed successfully",
  "data": {
    "id": 123,
    "confirmation_required": false,
    "confirmed_at": "2025-01-15T11:30:00Z"
  }
}
```

**✅ Verificaciones:**
- El cliente PUEDE confirmar aunque pasó el deadline
- El mantenimiento se marca como confirmado
- Se envían las notificaciones correspondientes

**📱 Notificaciones esperadas:**
- Técnico recibe: `maintenance_confirmed_by_client`
- Coordinador recibe: `maintenance_confirmed`

---

### **Paso 4: Técnico Ahora Puede Iniciar**

Después de la confirmación, el técnico puede iniciar normalmente.

---

## 📝 Escenario 4: Validaciones y Casos de Error

### **Caso 1: Cliente Intenta Confirmar Dos Veces**

**Paso 1:** Cliente confirma el mantenimiento (éxito)

**Paso 2:** Cliente intenta confirmar nuevamente

**Endpoint:**
```
POST /api/maintenances/{maintenanceId}/confirm
```

**Respuesta esperada (Error):**
```json
{
  "success": false,
  "message": "Este mantenimiento ya fue confirmado."
}
```

**✅ Verificaciones:**
- El backend rechaza la segunda confirmación
- Mensaje de error claro

---

### **Caso 2: Usuario Incorrecto Intenta Confirmar**

**Endpoint:**
```
POST /api/maintenances/{maintenanceId}/confirm
```

**Headers:**
```
Authorization: Bearer {token_otro_cliente}  // Cliente que NO es dueño
```

**Respuesta esperada (Error):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**✅ Verificaciones:**
- Solo el cliente dueño puede confirmar
- El backend valida la autorización

---

### **Caso 3: Confirmar Mantenimiento en Estado Incorrecto**

**Preparación:** Tener un mantenimiento en estado `pending` (sin asignar)

**Endpoint:**
```
POST /api/maintenances/{maintenanceId}/confirm
```

**Respuesta esperada (Error):**
```json
{
  "success": false,
  "message": "Solo se pueden confirmar mantenimientos en estado asignado."
}
```

**✅ Verificaciones:**
- Solo se pueden confirmar mantenimientos en estado `assigned`
- Mensaje de error descriptivo

---

### **Caso 4: Coordinador Intenta Confirmar**

**Endpoint:**
```
POST /api/maintenances/{maintenanceId}/confirm
```

**Headers:**
```
Authorization: Bearer {token_coordinador}
```

**Respuesta esperada (Error):**
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**✅ Verificaciones:**
- Solo los clientes pueden confirmar
- El coordinador no tiene permiso

---

## 🔍 Verificaciones Adicionales en Base de Datos

Si tienes acceso a la base de datos, puedes verificar directamente:

```sql
-- Ver estado de confirmación de un mantenimiento
SELECT 
    id,
    status,
    confirmation_required,
    confirmed_at,
    confirmation_deadline,
    coordinator_notified,
    coordinator_notified_at,
    coordinator_called,
    coordinator_called_at
FROM maintenances
WHERE id = {maintenanceId};
```

**Campos a verificar:**
- `confirmation_required`: `1` cuando requiere confirmación, `0` cuando no
- `confirmed_at`: `NULL` si no está confirmado, timestamp si está confirmado
- `confirmation_deadline`: Timestamp de cuando vence el plazo
- `coordinator_notified`: `1` si el coordinador fue notificado, `0` si no
- `coordinator_called`: `1` si el coordinador marcó como llamado, `0` si no

---

## 📊 Checklist de Pruebas

### **Flujo Principal:**
- [ ] Coordinador asigna técnico → Se activa confirmación requerida
- [ ] Cliente recibe notificación de confirmación requerida
- [ ] Cliente confirma → Se marca como confirmado
- [ ] Técnico recibe notificación de confirmación
- [ ] Coordinador recibe notificación de confirmación
- [ ] Técnico puede iniciar después de confirmación

### **Flujo Sin Confirmación:**
- [ ] Después de 1 hora sin confirmar → Coordinador recibe notificación
- [ ] Coordinador puede ver lista de mantenimientos sin confirmar
- [ ] Coordinador puede marcar como llamado
- [ ] Técnico NO puede iniciar sin confirmación

### **Validaciones:**
- [ ] Cliente no puede confirmar dos veces
- [ ] Solo el cliente dueño puede confirmar
- [ ] Solo se puede confirmar en estado `assigned`
- [ ] Solo clientes pueden confirmar (no coordinadores ni técnicos)
- [ ] Cliente puede confirmar después del deadline

### **Notificaciones:**
- [ ] Cliente recibe notificación al asignar técnico
- [ ] Técnico recibe notificación cuando cliente confirma
- [ ] Coordinador recibe notificación cuando cliente confirma
- [ ] Coordinador recibe notificación cuando pasa el deadline sin confirmar

---

## 🛠️ Herramientas Útiles para Pruebas

### **1. Postman Collection**

Puedes crear una colección de Postman con todos los endpoints:

- `PUT /api/assignMaintenance/{id}` - Asignar técnico
- `POST /api/maintenances/{id}/confirm` - Confirmar mantenimiento
- `GET /api/maintenances/{id}` - Ver mantenimiento
- `GET /api/coordinator/maintenances/unconfirmed` - Ver sin confirmar
- `POST /api/maintenances/{id}/mark-as-called` - Marcar como llamado
- `POST /api/technicianMaintenances/{id}/start` - Iniciar mantenimiento

### **2. Scripts SQL para Simular Tiempo**

```sql
-- Simular que pasó el deadline (restar 1 hora)
UPDATE maintenances 
SET confirmation_deadline = NOW() - INTERVAL 1 HOUR
WHERE id = {maintenanceId};

-- Verificar notificaciones en la base de datos
SELECT * FROM notifications 
WHERE notifiable_id = {user_id}
ORDER BY created_at DESC;
```

### **3. Verificar Logs de Notificaciones**

Revisa los logs de Laravel para ver si las notificaciones se están enviando:

```bash
tail -f storage/logs/laravel.log | grep -i "notification\|expo"
```

---

## ❓ Preguntas Frecuentes de Pruebas

**P: ¿Cómo puedo probar el deadline de 1 hora sin esperar realmente?**
R: Modifica directamente en la base de datos el campo `confirmation_deadline` a una fecha pasada.

**P: ¿Cómo verifico que las notificaciones se enviaron?**
R: Revisa la tabla `notifications` en la base de datos o los logs del sistema.

**P: ¿Puedo probar con un mantenimiento que ya está confirmado?**
R: Sí, pero primero necesitas resetear los campos de confirmación en la base de datos.

**P: ¿Qué pasa si el técnico intenta iniciar antes de que el cliente confirme?**
R: El backend rechazará la petición con un mensaje de error claro.

---

## 📞 Soporte

Si encuentras algún problema durante las pruebas o tienes dudas sobre el flujo, contacta al equipo de backend.

---

**Última actualización:** 2025-01-15

