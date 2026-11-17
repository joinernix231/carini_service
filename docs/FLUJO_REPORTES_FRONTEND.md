# Flujo de Generación de Reportes - Guía Frontend

## 📋 Resumen

Cada mantenimiento puede tener múltiples equipos/dispositivos. **Cada equipo debe tener su propio reporte de servicio** antes de finalizar el mantenimiento. Al finalizar, se generará automáticamente un PDF por cada equipo que tenga reporte guardado.

---

## 🔄 Flujo Completo

### Paso 1: Obtener los Equipos del Mantenimiento

**Endpoint:** `GET /api/maintenances/{id}/information`

**Qué hace:** Obtiene toda la información del mantenimiento, incluyendo la lista de todos los equipos/dispositivos asociados.

**Respuesta:** Incluye un array `devices` con todos los equipos del mantenimiento.

---

### Paso 2: Mostrar Formulario de Reporte para Cada Equipo

Para cada equipo en la lista, debes mostrar un formulario con los siguientes campos:

#### Campos del Formulario:

- **Versión** (opcional, texto)
- **Descripción de Actividades** (texto largo)
- **Repuestos Usados** (lista donde cada repuesto tiene):
  - Descripción (texto)
  - Cantidad (número)
- **Estado del Equipo** (seleccionar uno):
  - FUNCIONANDO
  - EN_REPARACION
  - FUERA_DE_SERVICIO
  - OTRO
- **Facturar el Servicio** (Sí/No)
- **Monto del Servicio** (número, requerido si se marca "Facturar el Servicio")
- **Viáticos Recibidos** (Sí/No)
- **Monto de Viáticos Recibidos** (número, requerido si se marca "Viáticos Recibidos")
- **Facturar Viáticos** (Sí/No)
- **Monto de Viáticos a Facturar** (número, requerido si se marca "Facturar Viáticos")
- **Alimentación** (Sí/No)
- **Hospedaje** (Sí/No)
- **Transporte** (Sí/No)
- **Observaciones del Cliente** (texto largo)

---

### Paso 3: Guardar Reporte para Cada Equipo

**Endpoint:** `POST /api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report`

**Parámetros de Ruta:**
- `maintenance`: ID del mantenimiento
- `clientDevice`: ID del equipo/dispositivo

**Body:** Todos los campos del formulario completados

**Qué hace:** Guarda o actualiza el reporte de servicio para ese equipo específico.

**Importante:** 
- Debes guardar un reporte para **cada equipo** antes de finalizar el mantenimiento
- Si un equipo no tiene reporte guardado, **no se generará PDF para ese equipo**
- Puedes guardar y actualizar el reporte múltiples veces antes de finalizar

---

### Paso 4: Obtener Firma del Cliente

Obtén la firma del cliente usando tu implementación actual. La firma debe enviarse como el nombre del archivo (sin extensión) en el campo `client_signature`.

---

### Paso 5: Finalizar el Mantenimiento

**Endpoint:** `POST /api/technicianMaintenances/{id}/complete`

**Body:**
- `latitude`: Latitud actual (número)
- `longitude`: Longitud actual (número)
- `final_observations`: Observaciones finales (texto opcional)
- `client_signature`: Nombre del archivo de la firma (texto)

**Qué hace:** Finaliza el mantenimiento y dispara la generación automática de PDFs.

**Resultado:** Se generarán automáticamente los PDFs (uno por cada equipo que tenga reporte guardado).

---

### Paso 6: Obtener los PDFs Generados

**Endpoint:** `GET /api/maintenances/{id}/information`

**Qué hace:** Obtiene la información actualizada del mantenimiento con los PDFs generados.

**Respuesta:** Cada equipo en el array `devices` incluirá:
- `pdf_url`: URL completa del PDF generado para ese equipo (o `null` si no se generó)

---

## ⚠️ Puntos Importantes

1. **Un reporte por equipo:** Debes guardar un reporte para cada equipo antes de finalizar. Si un mantenimiento tiene 3 equipos, debes guardar 3 reportes.

2. **Orden obligatorio:**
   - Primero: Guardar reportes para todos los equipos
   - Segundo: Obtener firma del cliente
   - Tercero: Finalizar mantenimiento
   - Cuarto: Los PDFs se generan automáticamente

3. **Validación:** Verifica que todos los equipos tengan reporte guardado antes de permitir finalizar el mantenimiento.

4. **Repuestos usados:** Se envían como un array de objetos, cada uno con `description` (texto) y `quantity` (número). Si no se usaron repuestos, puedes enviar un array vacío `[]`.

5. **PDFs:** Los PDFs se generan automáticamente después de finalizar. No hay un endpoint separado para generarlos.

---

## 📝 Checklist

Antes de finalizar un mantenimiento, asegúrate de:

- [ ] Obtener la lista de equipos del mantenimiento
- [ ] Mostrar formulario de reporte para cada equipo
- [ ] Llenar todos los campos del formulario para cada equipo
- [ ] Guardar el reporte para cada equipo
- [ ] Verificar que todos los reportes se guardaron correctamente
- [ ] Obtener la firma del cliente
- [ ] Obtener la ubicación actual (latitud y longitud)
- [ ] Finalizar el mantenimiento
- [ ] Verificar que los PDFs estén disponibles consultando el mantenimiento nuevamente

---

## 🔗 Endpoints Resumen

| Acción | Método | Endpoint |
|--------|--------|----------|
| Obtener equipos del mantenimiento | GET | `/api/maintenances/{id}/information` |
| Guardar reporte de un equipo | POST | `/api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report` |
| Obtener reporte guardado (opcional) | GET | `/api/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report` |
| Finalizar mantenimiento | POST | `/api/technicianMaintenances/{id}/complete` |
| Obtener PDFs generados | GET | `/api/maintenances/{id}/information` |

---

## ❓ Preguntas Frecuentes

**P: ¿Debo guardar un reporte para cada equipo?**  
R: Sí, cada equipo debe tener su propio reporte. Si un mantenimiento tiene 3 equipos, debes guardar 3 reportes.

**P: ¿Puedo actualizar el reporte después de guardarlo?**  
R: Sí, puedes llamar múltiples veces al endpoint de guardar reporte para actualizar la información antes de finalizar.

**P: ¿Qué pasa si no guardo el reporte para un equipo?**  
R: Ese equipo no tendrá PDF generado. Es recomendable guardar reportes para todos los equipos.

**P: ¿Cuándo se generan los PDFs?**  
R: Los PDFs se generan automáticamente cuando finalizas el mantenimiento (después de llamar al endpoint `complete`).

**P: ¿Cómo obtengo los PDFs generados?**  
R: Consulta el endpoint `GET /api/maintenances/{id}/information`. Cada equipo en la respuesta tendrá su campo `pdf_url` con la URL del PDF.

**P: ¿Puedo enviar un array vacío en repuestos usados?**  
R: Sí, si no se usaron repuestos, puedes enviar `[]` o simplemente no incluir el campo.

