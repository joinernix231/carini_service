# API de Reporte de Servicio - Documentación Frontend

## Descripción General

El sistema de Reporte de Servicio permite a los técnicos completar un formulario detallado con toda la información necesaria para generar la Orden de Servicio (PDF) **por cada dispositivo** antes de finalizar un mantenimiento. Cada dispositivo en el mantenimiento tiene su propio reporte de servicio y su propio PDF generado.

## Flujo de Trabajo

1. **Técnico completa el mantenimiento** → Llena el formulario de reporte de servicio **para cada dispositivo**
2. **Guarda el reporte por dispositivo** → `POST /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`
3. **Completa el mantenimiento** → `POST /api/technicianMaintenances/{id}/complete`
4. **Se generan PDFs automáticamente** → Un PDF por cada dispositivo con su respectivo reporte
5. En el detalle de mantenimiento de todos los roles ahora va a venir PDF url. Porfa muesstralo

## Endpoints

### 1. Guardar/Actualizar Reporte de Servicio

**Endpoint:** `POST /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`

**Descripción:** Guarda o actualiza el reporte de servicio de un dispositivo específico en un mantenimiento. Si el reporte ya existe, se actualiza; si no existe, se crea.

**Autenticación:** Requerida (rol: `tecnico`)

**Parámetros de Ruta:**
- `maintenance` (integer, requerido): ID del mantenimiento
- `clientDevice` (integer, requerido): ID del dispositivo del cliente

**Body (JSON):**

```json
{
    "version": "1.0",
    "activities_description": "Mantenimiento preventivo realizado. Se limpiaron filtros, se verificó el funcionamiento del equipo y se realizaron ajustes menores.",
    "used_spare_parts": [
        {
            "description": "Filtro de agua",
            "quantity": 2
        },
        {
            "description": "Correa de transmisión",
            "quantity": 1
        },
        {
            "description": "Aceite lubricante",
            "quantity": 1
        }
    ],
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
| `used_spare_parts` | array | No | Lista de repuestos utilizados | Array de objetos con `description` y `quantity` |
| `used_spare_parts[].description` | string | Sí (si se envía el array) | Descripción del repuesto usado | Texto (máx. 500 caracteres) |
| `used_spare_parts[].quantity` | integer | Sí (si se envía el array) | Cantidad del repuesto usado | Número entero ≥ 1 |
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
        "maintenance_client_device_id": 25,
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
          "equipment_status": ["El estado del equipo debe ser: FUNCIONANDO, EN_REPARACION, FUERA_DE_SERVICIO u OTRO"],
          "used_spare_parts.0.quantity": ["La cantidad de repuestos debe ser al menos 1"]
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

- **404 Not Found:** Mantenimiento o dispositivo no encontrado, o el dispositivo no está asociado al mantenimiento
  ```json
  {
      "success": false,
      "message": "El dispositivo no está asociado a este mantenimiento",
      "data": [],
      "code": 404
  }
  ```

---

### 2. Obtener Reporte de Servicio

**Endpoint:** `GET /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`

**Descripción:** Obtiene el reporte de servicio de un dispositivo específico en un mantenimiento si existe.

**Autenticación:** Requerida (rol: `tecnico`)

**Parámetros de Ruta:**
- `maintenance` (integer, requerido): ID del mantenimiento
- `clientDevice` (integer, requerido): ID del dispositivo del cliente

**Respuesta Exitosa (200):**

```json
{
    "success": true,
    "message": "Service report retrieved successfully",
    "data": {
        "id": 1,
        "maintenance_client_device_id": 25,
        "version": "1.0",
        "activities_description": "Mantenimiento preventivo realizado...",
        "used_spare_parts": [
            {
                "description": "Filtro de agua",
                "quantity": 2
            },
            {
                "description": "Correa de transmisión",
                "quantity": 1
            }
        ],
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
// Guardar reporte de servicio para un dispositivo
const saveServiceReport = async (maintenanceId, clientDeviceId, reportData) => {
    try {
        const response = await fetch(
            `${API_BASE_URL}/technicianMaintenances/${maintenanceId}/devices/${clientDeviceId}/service-report`,
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

// Obtener reporte de servicio de un dispositivo
const getServiceReport = async (maintenanceId, clientDeviceId) => {
    try {
        const response = await fetch(
            `${API_BASE_URL}/technicianMaintenances/${maintenanceId}/devices/${clientDeviceId}/service-report`,
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

// Ejemplo de uso - Guardar reporte para cada dispositivo
const maintenance = {
    id: 15,
    clientDevices: [
        { id: 57, device: { brand: 'Carini', model: 'CAR-25' } },
        { id: 58, device: { brand: 'Carini', model: 'CAR-30' } }
    ]
};

// Reporte para el primer dispositivo
const reportData1 = {
    version: "1.0",
    activities_description: "Mantenimiento preventivo realizado...",
    used_spare_parts: [
        {
            description: "Filtro de agua",
            quantity: 2
        },
        {
            description: "Correa de transmisión",
            quantity: 1
        }
    ],
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

// Reporte para el segundo dispositivo
const reportData2 = {
    version: "1.0",
    activities_description: "Revisión y ajuste de componentes...",
    used_spare_parts: [
        {
            description: "Aceite lubricante",
            quantity: 1
        }
    ],
    equipment_status: "FUNCIONANDO",
    bill_service: true,
    service_amount: 120000.00,
    expenses_received: false,
    expenses_amount_received: 0,
    bill_expenses: false,
    expenses_amount: 0,
    food: false,
    lodging: false,
    transport: false,
    client_observations: ""
};

// Guardar reportes antes de completar el mantenimiento
await saveServiceReport(15, 57, reportData1);
await saveServiceReport(15, 58, reportData2);

// Luego completar el mantenimiento (esto generará PDFs para cada dispositivo)
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
   - Obtener la lista de dispositivos del mantenimiento
   - Mostrar formulario de reporte de servicio **para cada dispositivo**
   - Permitir al técnico llenar todos los campos por dispositivo
   - Guardar el reporte con `POST /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`
   - Opcional: Permitir editar y actualizar el reporte antes de completar

2. **Al completar el mantenimiento:**
   - Llamar a `POST /api/technicianMaintenances/{id}/complete`
   - Se generarán PDFs automáticamente (uno por cada dispositivo con su respectivo reporte)

3. **Obtener los PDFs generados:**
   - Los PDFs estarán disponibles después de completar
   - Cada dispositivo tendrá su propio PDF generado
   - O usar las rutas de preview: `/preview-pdf/maintenance/{id}` (esto puede necesitar actualización para mostrar PDFs por dispositivo)

---

## Estructura de `used_spare_parts`

El campo `used_spare_parts` es un array de objetos donde cada objeto representa un repuesto usado:

```json
{
    "used_spare_parts": [
        {
            "description": "Filtro de agua",
            "quantity": 2
        },
        {
            "description": "Correa de transmisión",
            "quantity": 1
        }
    ]
}
```

**Notas importantes:**
- Cada repuesto debe tener `description` (string, requerido) y `quantity` (integer ≥ 1, requerido)
- Si no se usaron repuestos, puedes enviar un array vacío `[]` o simplemente no incluir el campo
- No se incluyen fotos de repuestos en este campo (solo descripción y cantidad)

---

## Notas Importantes

1. **Un reporte por dispositivo:** Cada dispositivo en el mantenimiento debe tener su propio reporte de servicio. Debes guardar un reporte para cada dispositivo antes de completar el mantenimiento.

2. **El reporte es opcional:** Si no se guarda un reporte para un dispositivo antes de completar el mantenimiento, ese dispositivo no tendrá PDF generado.

3. **Actualización:** Puedes llamar múltiples veces a `POST /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report` para actualizar el reporte antes de completar el mantenimiento.

4. **Validación:** Todos los campos son opcionales excepto las validaciones de tipo (si `bill_service` es `true`, `service_amount` debe ser un número válido).

5. **Estados del Equipo:** Los valores exactos son:
   - `FUNCIONANDO`
   - `EN_REPARACION`
   - `FUERA_DE_SERVICIO`
   - `OTRO`

6. **Montos:** Todos los montos deben ser números decimales con máximo 2 decimales.

7. **Repuestos usados vs sugerencias:** 
   - `used_spare_parts` son los repuestos que **realmente usó** el técnico (van en el reporte)
   - `spare_part_suggestions` son **sugerencias** de repuestos que el técnico recomienda cambiar (son diferentes)

---

## Integración con el Formulario de Completar Mantenimiento

El reporte de servicio debe completarse **antes** de llamar al endpoint de completar mantenimiento. El flujo recomendado es:

```
1. Técnico termina el trabajo
   ↓
2. Para cada dispositivo:
   - Llena formulario de reporte de servicio
   - Guarda reporte (POST /devices/{clientDevice}/service-report)
   ↓
3. Obtiene firma del cliente
   ↓
4. Completa mantenimiento (POST /complete)
   ↓
5. PDFs se generan automáticamente (uno por cada dispositivo con reporte)
```

---

## Preguntas Frecuentes

**P: ¿Debo guardar un reporte para cada dispositivo?**  
R: Sí, cada dispositivo debe tener su propio reporte de servicio. Si un mantenimiento tiene 3 dispositivos, debes guardar 3 reportes.

**P: ¿Puedo actualizar el reporte después de completar el mantenimiento?**  
R: Sí, técnicamente puedes actualizar el reporte, pero el PDF ya habrá sido generado. Si necesitas regenerar el PDF, deberías contactar al backend para implementar esa funcionalidad.

**P: ¿Qué pasa si no guardo el reporte para un dispositivo antes de completar?**  
R: Ese dispositivo no tendrá PDF generado. Es recomendable siempre guardar el reporte para todos los dispositivos antes de completar.

**P: ¿Puedo obtener el reporte de un mantenimiento completado?**  
R: Sí, siempre que tengas acceso al mantenimiento como técnico asignado. Usa `GET /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`.

**P: ¿Los montos deben incluir separador de miles?**  
R: No, envía los montos como números decimales simples (ej: `150000.00`, no `150.000,00`).

**P: ¿Puedo enviar un array vacío en `used_spare_parts`?**  
R: Sí, si no se usaron repuestos, puedes enviar `[]` o simplemente no incluir el campo.
