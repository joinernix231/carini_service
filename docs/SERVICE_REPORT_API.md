# API de Reporte de Servicio - Documentación Frontend

## Descripción General

El sistema de Reporte de Servicio permite a los técnicos completar un formulario detallado con toda la información necesaria para generar la Orden de Servicio (PDF) antes de finalizar un mantenimiento. Esta información se almacena en una tabla separada (`maintenance_service_reports`) asociada al mantenimiento.

## Flujo de Trabajo

1. **Técnico completa el mantenimiento** → Llena el formulario de reporte de servicio
2. **Guarda el reporte** → `POST /api/technicianMaintenances/{id}/service-report`
3. **Completa el mantenimiento** → `POST /api/technicianMaintenances/{id}/complete`
4. **Se genera el PDF automáticamente** → Con toda la información del reporte

## Endpoints

### 1. Guardar/Actualizar Reporte de Servicio

**Endpoint:** `POST /api/technicianMaintenances/{maintenance}/service-report`

**Descripción:** Guarda o actualiza el reporte de servicio de un mantenimiento. Si el reporte ya existe, se actualiza; si no existe, se crea.

**Autenticación:** Requerida (rol: `tecnico`)

**Parámetros de Ruta:**
- `maintenance` (integer, requerido): ID del mantenimiento

**Body (JSON):**

```json
{
    "version": "1.0",
    "activities_description": "Mantenimiento preventivo realizado. Se limpiaron filtros, se verificó el funcionamiento del equipo y se realizaron ajustes menores.",
    "spare_parts_used": "Filtro de agua, correa de transmisión, aceite lubricante",
    "spare_parts_quantity": 3,
    "equipment_status": "FUNCIONANDO",
    "bill_service": true,
    "service_amount": 150000.00,
    "expenses_received": false,
    "expenses_amount_received": 0,
    "bill_expenses": false,
    "expenses_amount": 0,
    "food": false,
    "lodging": false,
    "transport": false,
    "client_observations": "El cliente quedó satisfecho con el servicio. El equipo funciona correctamente."
}
```

**Campos del Body:**

| Campo | Tipo | Requerido | Descripción | Valores Permitidos |
|-------|------|-----------|-------------|-------------------|
| `version` | string | No | Versión del formulario | Cualquier string (máx. 10 caracteres) |
| `activities_description` | string | No | Descripción detallada de las actividades realizadas | Texto (máx. 2000 caracteres) |
| `spare_parts_used` | string | No | Lista de repuestos utilizados | Texto (máx. 1000 caracteres) |
| `spare_parts_quantity` | integer | No | Cantidad total de repuestos utilizados | Número entero ≥ 0 |
| `equipment_status` | string | No | Estado del equipo después del mantenimiento | `FUNCIONANDO`, `EN_REPARACION`, `FUERA_DE_SERVICIO`, `OTRO` |
| `bill_service` | boolean | No | Indica si se debe facturar el servicio | `true` o `false` |
| `service_amount` | decimal | No | Monto a facturar por el servicio | Número ≥ 0 (requerido si `bill_service` es `true`) |
| `expenses_received` | boolean | No | Indica si se recibieron viáticos | `true` o `false` |
| `expenses_amount_received` | decimal | No | Monto de viáticos recibidos | Número ≥ 0 (requerido si `expenses_received` es `true`) |
| `bill_expenses` | boolean | No | Indica si se deben facturar viáticos | `true` o `false` |
| `expenses_amount` | decimal | No | Monto de viáticos a facturar | Número ≥ 0 (requerido si `bill_expenses` es `true`) |
| `food` | boolean | No | Indica si hubo gastos de alimentación | `true` o `false` |
| `lodging` | boolean | No | Indica si hubo gastos de hospedaje | `true` o `false` |
| `transport` | boolean | No | Indica si hubo gastos de transporte | `true` o `false` |
| `client_observations` | string | No | Observaciones del cliente | Texto (máx. 2000 caracteres) |

**Respuesta Exitosa (200):**

```json
{
    "success": true,
    "message": "Service report saved successfully",
    "data": {
        "id": 1,
        "maintenance_id": 15,
        "version": "1.0"
    }
}
```

**Errores Posibles:**

- **400 Bad Request:** Error de validación
  ```json
  {
      "success": false,
      "message": "Ha ocurrido un error de validación",
      "data": {
          "equipment_status": ["El estado del equipo debe ser: FUNCIONANDO, EN_REPARACION, FUERA_DE_SERVICIO u OTRO"]
      }
  }
  ```

- **403 Forbidden:** Usuario no autorizado o no es el técnico asignado
  ```json
  {
      "success": false,
      "message": "Este usuario no tiene permiso para realizar esta acción",
      "code": 403
  }
  ```

- **404 Not Found:** Mantenimiento no encontrado

---

### 2. Obtener Reporte de Servicio

**Endpoint:** `GET /api/technicianMaintenances/{maintenance}/service-report`

**Descripción:** Obtiene el reporte de servicio de un mantenimiento si existe.

**Autenticación:** Requerida (rol: `tecnico`)

**Parámetros de Ruta:**
- `maintenance` (integer, requerido): ID del mantenimiento

**Respuesta Exitosa (200):**

```json
{
    "success": true,
    "message": "Service report retrieved successfully",
    "data": {
        "id": 1,
        "maintenance_id": 15,
        "version": "1.0",
        "activities_description": "Mantenimiento preventivo realizado...",
        "spare_parts_used": "Filtro de agua, correa de transmisión",
        "spare_parts_quantity": 2,
        "equipment_status": "FUNCIONANDO",
        "bill_service": true,
        "service_amount": "150000.00",
        "expenses_received": false,
        "expenses_amount_received": null,
        "bill_expenses": false,
        "expenses_amount": null,
        "food": false,
        "lodging": false,
        "transport": false,
        "client_observations": "El cliente quedó satisfecho",
        "created_at": "2025-11-12T10:00:00.000000Z",
        "updated_at": "2025-11-12T10:00:00.000000Z"
    }
}
```

**Respuesta cuando no existe (404):**

```json
{
    "success": false,
    "message": "Service report not found",
    "data": [],
    "code": 404
}
```

---

## Ejemplo de Implementación Frontend

### React Native / JavaScript

```javascript
// Guardar reporte de servicio
const saveServiceReport = async (maintenanceId, reportData) => {
    try {
        const response = await fetch(
            `${API_BASE_URL}/technicianMaintenances/${maintenanceId}/service-report`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(reportData)
            }
        );

        const result = await response.json();
        
        if (result.success) {
            console.log('Reporte guardado exitosamente');
            return result.data;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error al guardar reporte:', error);
        throw error;
    }
};

// Obtener reporte de servicio
const getServiceReport = async (maintenanceId) => {
    try {
        const response = await fetch(
            `${API_BASE_URL}/technicianMaintenances/${maintenanceId}/service-report`,
            {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            }
        );

        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else if (result.code === 404) {
            return null; // No existe reporte aún
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error al obtener reporte:', error);
        throw error;
    }
};

// Ejemplo de uso
const reportData = {
    version: "1.0",
    activities_description: "Mantenimiento preventivo realizado...",
    spare_parts_used: "Filtro de agua, correa de transmisión",
    spare_parts_quantity: 2,
    equipment_status: "FUNCIONANDO",
    bill_service: true,
    service_amount: 150000.00,
    expenses_received: false,
    expenses_amount_received: 0,
    bill_expenses: false,
    expenses_amount: 0,
    food: false,
    lodging: false,
    transport: false,
    client_observations: "El cliente quedó satisfecho con el servicio"
};

// Guardar reporte antes de completar el mantenimiento
await saveServiceReport(15, reportData);

// Luego completar el mantenimiento
await completeMaintenance(15, {
    latitude: 4.6130026,
    longitude: -74.1933955,
    final_observations: null,
    client_signature: "signature_1762931851617_50rtes5eige_1762931851618_uj13w0z9fdq"
});
```

---

## Flujo Recomendado en el Frontend

1. **Antes de completar el mantenimiento:**
   - Mostrar formulario de reporte de servicio
   - Permitir al técnico llenar todos los campos
   - Guardar el reporte con `POST /api/technicianMaintenances/{id}/service-report`
   - Opcional: Permitir editar y actualizar el reporte antes de completar

2. **Al completar el mantenimiento:**
   - Llamar a `POST /api/technicianMaintenances/{id}/complete`
   - El PDF se generará automáticamente con la información del reporte

3. **Obtener el PDF generado:**
   - El PDF estará disponible en `maintenance.pdf_url` después de completar
   - O usar las rutas de preview: `/preview-pdf/maintenance/{id}`

---

## Notas Importantes

1. **El reporte es opcional:** Si no se guarda un reporte antes de completar el mantenimiento, el PDF se generará con valores por defecto o vacíos.

2. **Actualización:** Puedes llamar múltiples veces a `POST /api/technicianMaintenances/{id}/service-report` para actualizar el reporte antes de completar el mantenimiento.

3. **Validación:** Todos los campos son opcionales excepto las validaciones de tipo (si `bill_service` es `true`, `service_amount` debe ser un número válido).

4. **Estados del Equipo:** Los valores exactos son:
   - `FUNCIONANDO`
   - `EN_REPARACION`
   - `FUERA_DE_SERVICIO`
   - `OTRO`

5. **Montos:** Todos los montos deben ser números decimales con máximo 2 decimales.

---

## Integración con el Formulario de Completar Mantenimiento

El reporte de servicio debe completarse **antes** de llamar al endpoint de completar mantenimiento. El flujo recomendado es:

```
1. Técnico termina el trabajo
   ↓
2. Llena formulario de reporte de servicio
   ↓
3. Guarda reporte (POST /service-report)
   ↓
4. Obtiene firma del cliente
   ↓
5. Completa mantenimiento (POST /complete)
   ↓
6. PDF se genera automáticamente con toda la info del reporte
```

---

## Preguntas Frecuentes

**P: ¿Puedo actualizar el reporte después de completar el mantenimiento?**  
R: Sí, técnicamente puedes actualizar el reporte, pero el PDF ya habrá sido generado. Si necesitas regenerar el PDF, deberías contactar al backend para implementar esa funcionalidad.

**P: ¿Qué pasa si no guardo el reporte antes de completar?**  
R: El PDF se generará con valores por defecto o vacíos. Es recomendable siempre guardar el reporte antes de completar.

**P: ¿Puedo obtener el reporte de un mantenimiento completado?**  
R: Sí, siempre que tengas acceso al mantenimiento como técnico asignado.

**P: ¿Los montos deben incluir separador de miles?**  
R: No, envía los montos como números decimales simples (ej: `150000.00`, no `150.000,00`).

