# Guía Frontend - Reporte de Servicio y Finalización de Mantenimiento

## 📋 Resumen de Cambios

### Cambio Principal: Orden de Servicio por Dispositivo

**ANTES:** Un mantenimiento tenía un solo reporte de servicio y generaba un solo PDF.

**AHORA:** Cada dispositivo en el mantenimiento tiene su propio reporte de servicio y genera su propio PDF. Cada dispositivo tendrá su propio `pdf_url` disponible en la respuesta de `maintenance/information`.

### Estructura de Repuestos

**ANTES:** Los repuestos se enviaban como texto plano con una cantidad total.

**AHORA:** Los repuestos se envían como un array de objetos, donde cada objeto contiene:
- `description`: Descripción del repuesto usado (texto)
- `quantity`: Cantidad usada de ese repuesto (número entero, mínimo 1)

---

## 🎯 Flujo Completo: Pantalla de Finalizar Mantenimiento

### Paso 1: Obtener Dispositivos del Mantenimiento

Antes de mostrar el formulario, debes obtener los dispositivos asociados al mantenimiento usando el endpoint `GET /api/maintenances/{id}/information`. Este endpoint retorna todos los dispositivos (`devices`) asociados al mantenimiento.

### Paso 2: Mostrar Formulario por Cada Dispositivo

Para cada dispositivo en la lista, debes mostrar un formulario de reporte de servicio con los siguientes campos:

**Campos del Formulario:**

- **Versión**: Texto opcional (por defecto "1.0")
- **Descripción de Actividades**: Campo de texto largo para describir las actividades realizadas
- **Repuestos Usados**: Lista de repuestos donde cada repuesto tiene:
  - Descripción (texto)
  - Cantidad (número entero, mínimo 1)
- **Estado del Equipo**: Selector con opciones:
  - FUNCIONANDO
  - EN_REPARACION
  - FUERA_DE_SERVICIO
  - OTRO
- **Facturar el Servicio**: Checkbox (Sí/No)
- **Monto del Servicio**: Campo numérico (requerido si se marca "Facturar el Servicio")
- **Viáticos Recibidos**: Checkbox (Sí/No)
- **Monto de Viáticos Recibidos**: Campo numérico (requerido si se marca "Viáticos Recibidos")
- **Facturar Viáticos**: Checkbox (Sí/No)
- **Monto de Viáticos a Facturar**: Campo numérico (requerido si se marca "Facturar Viáticos")
- **Alimentación**: Checkbox (Sí/No)
- **Hospedaje**: Checkbox (Sí/No)
- **Transporte**: Checkbox (Sí/No)
- **Observaciones del Cliente**: Campo de texto largo

### Paso 3: Manejar Repuestos Usados

Para cada dispositivo, el usuario debe poder:
- Agregar múltiples repuestos usados
- Para cada repuesto, ingresar:
  - Una descripción (texto)
  - Una cantidad (número entero, mínimo 1)
- Eliminar repuestos de la lista
- Si no se usaron repuestos, puede dejar la lista vacía o no incluir el campo

### Paso 4: Guardar Reporte por Cada Dispositivo

Antes de finalizar el mantenimiento, debes guardar el reporte para cada dispositivo usando el endpoint:

**Endpoint:** `POST /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`

**Parámetros de Ruta:**
- `maintenance`: ID del mantenimiento
- `clientDevice`: ID del dispositivo del cliente

**Body (JSON):** Todos los campos del formulario mencionados anteriormente.

**Importante:** Debes guardar un reporte para cada dispositivo antes de finalizar el mantenimiento. Si un dispositivo no tiene reporte guardado, no se generará PDF para ese dispositivo.

### Paso 5: Obtener Firma del Cliente

Después de guardar todos los reportes, obtén la firma del cliente usando tu implementación actual de captura de firma. La firma debe enviarse como el nombre del archivo (sin extensión) en el campo `client_signature`.

### Paso 6: Finalizar Mantenimiento

Una vez que todos los reportes estén guardados y tengas la firma del cliente, finaliza el mantenimiento usando el endpoint:

**Endpoint:** `POST /api/technicianMaintenances/{id}/complete`

**Body (JSON):**
- `latitude`: Latitud actual (número)
- `longitude`: Longitud actual (número)
- `final_observations`: Observaciones finales (texto opcional)
- `client_signature`: Nombre del archivo de la firma (texto)

**Resultado:** Después de finalizar, se generarán automáticamente los PDFs (uno por cada dispositivo que tenga un reporte guardado). Los PDFs estarán disponibles en el campo `pdf_url` de cada dispositivo cuando consultes `maintenance/information`.

---

## 📦 Estructura de Datos: Repuestos Usados

### Formato del Campo `used_spare_parts`

El campo `used_spare_parts` es un array de objetos. Cada objeto representa un repuesto usado y contiene:

- `description` (string, requerido): Descripción del repuesto usado (máximo 500 caracteres)
- `quantity` (integer, requerido): Cantidad usada del repuesto (mínimo 1)

### Ejemplo de Datos

**Con repuestos:**
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

**Sin repuestos:**
```json
{
  "used_spare_parts": []
}
```

O simplemente no incluir el campo (será `null` por defecto).

### Validaciones

- Si envías el array `used_spare_parts`, cada elemento debe tener `description` y `quantity`
- `description` es requerido y debe ser un texto (máximo 500 caracteres)
- `quantity` es requerido y debe ser un número entero mayor o igual a 1

---

## 📄 PDFs Generados

### Disponibilidad de PDFs

Después de completar el mantenimiento, cada dispositivo que tenga un reporte de servicio guardado tendrá su propio PDF generado. El PDF estará disponible en el campo `pdf_url` de cada dispositivo.

### Consultar PDFs

Para obtener los PDFs generados, consulta el endpoint:

**Endpoint:** `GET /api/maintenances/{id}/information`

En la respuesta, cada dispositivo en el array `devices` incluirá:
- `pdf_url`: URL completa del PDF generado para ese dispositivo (o `null` si no se generó)

**Ejemplo de respuesta:**
```json
{
  "devices": [
    {
      "id": 57,
      "device": { ... },
      "serial": "ABC123",
      "pdf_url": "https://s3.amazonaws.com/bucket/docs/maintenance_15_device_57_1234567890.pdf",
      ...
    },
    {
      "id": 58,
      "device": { ... },
      "serial": "DEF456",
      "pdf_url": "https://s3.amazonaws.com/bucket/docs/maintenance_15_device_58_1234567891.pdf",
      ...
    }
  ]
}
```

---

## 🔄 Endpoints Actualizados

### 1. Guardar Reporte de Servicio

**Endpoint:** `POST /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`

**Descripción:** Guarda o actualiza el reporte de servicio para un dispositivo específico.

**Parámetros de Ruta:**
- `maintenance`: ID del mantenimiento
- `clientDevice`: ID del dispositivo del cliente

**Body:** Todos los campos del formulario de reporte de servicio.

**Respuesta:** Confirma que el reporte fue guardado exitosamente.

### 2. Obtener Reporte de Servicio

**Endpoint:** `GET /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`

**Descripción:** Obtiene el reporte de servicio guardado para un dispositivo específico.

**Parámetros de Ruta:**
- `maintenance`: ID del mantenimiento
- `clientDevice`: ID del dispositivo del cliente

**Respuesta:** Retorna el reporte de servicio si existe, o error 404 si no existe.

### 3. Obtener Información del Mantenimiento

**Endpoint:** `GET /api/maintenances/{id}/information`

**Descripción:** Obtiene toda la información del mantenimiento, incluyendo los dispositivos con sus respectivos `pdf_url`.

**Respuesta:** Incluye el array `devices` donde cada dispositivo tiene su `pdf_url` si el PDF fue generado.

---

## ⚠️ Puntos Importantes

1. **Un reporte por dispositivo:** Debes guardar un reporte para cada dispositivo antes de finalizar el mantenimiento. Si un dispositivo no tiene reporte, no se generará PDF para ese dispositivo.

2. **Orden de operaciones:**
   - Primero: Guardar reportes para todos los dispositivos
   - Segundo: Obtener firma del cliente
   - Tercero: Finalizar mantenimiento
   - Cuarto: Los PDFs se generan automáticamente

3. **Repuestos usados vs sugerencias:**
   - `used_spare_parts`: Repuestos que **realmente usó** el técnico durante el mantenimiento (van en el reporte de servicio)
   - `spare_part_suggestions`: **Sugerencias** de repuestos que el técnico recomienda cambiar (son diferentes y se manejan en otro endpoint)

4. **Validación de dispositivos:** El backend valida que el dispositivo pertenezca al mantenimiento. Si intentas guardar un reporte para un dispositivo que no está asociado, recibirás un error 404.

5. **PDFs generados:** Después de completar el mantenimiento, se generará un PDF por cada dispositivo que tenga un reporte guardado. Los PDFs estarán disponibles en el campo `pdf_url` de cada dispositivo cuando consultes `maintenance/information`.

6. **Actualización de reportes:** Puedes guardar y actualizar el reporte de un dispositivo múltiples veces antes de finalizar el mantenimiento. Solo se generarán los PDFs cuando se complete el mantenimiento.

---

## 📋 Checklist para Finalizar Mantenimiento

Antes de finalizar un mantenimiento, asegúrate de:

- [ ] Obtener la lista de dispositivos del mantenimiento
- [ ] Mostrar formulario de reporte para cada dispositivo
- [ ] Llenar todos los campos requeridos en cada formulario
- [ ] Agregar repuestos usados (si aplica) con descripción y cantidad
- [ ] Guardar el reporte para cada dispositivo
- [ ] Verificar que todos los reportes se guardaron correctamente
- [ ] Obtener la firma del cliente
- [ ] Obtener la ubicación actual (latitud y longitud)
- [ ] Finalizar el mantenimiento
- [ ] Verificar que los PDFs estén disponibles en `maintenance/information`

---

## ❓ Preguntas Frecuentes

**P: ¿Debo guardar un reporte para cada dispositivo?**  
R: Sí, cada dispositivo debe tener su propio reporte de servicio. Si un mantenimiento tiene 3 dispositivos, debes guardar 3 reportes.

**P: ¿Puedo actualizar el reporte después de guardarlo?**  
R: Sí, puedes llamar múltiples veces al endpoint de guardar reporte para actualizar la información antes de finalizar el mantenimiento.

**P: ¿Qué pasa si no guardo el reporte para un dispositivo?**  
R: Ese dispositivo no tendrá PDF generado. Es recomendable guardar reportes para todos los dispositivos.

**P: ¿Cuándo se generan los PDFs?**  
R: Los PDFs se generan automáticamente cuando finalizas el mantenimiento (después de llamar al endpoint `complete`).

**P: ¿Cómo obtengo los PDFs generados?**  
R: Consulta el endpoint `GET /api/maintenances/{id}/information`. Cada dispositivo en la respuesta tendrá su campo `pdf_url` con la URL del PDF.

**P: ¿Puedo enviar un array vacío en `used_spare_parts`?**  
R: Sí, si no se usaron repuestos, puedes enviar `[]` o simplemente no incluir el campo.

**P: ¿Los repuestos usados pueden tener fotos?**  
R: No, los repuestos usados solo tienen `description` y `quantity`. Las fotos son solo para las sugerencias de repuestos (endpoint diferente).

**P: ¿Qué formato tienen los montos?**  
R: Los montos deben enviarse como números decimales simples (ej: `150000.00`, no `150.000,00`).

---

## 📚 Recursos Adicionales

Para más detalles técnicos sobre los endpoints y sus respuestas, consulta:
- `docs/SERVICE_REPORT_API.md` - Documentación completa de la API
