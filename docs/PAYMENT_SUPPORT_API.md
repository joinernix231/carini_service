# API de Soporte de Pago - Endpoint UpdatePaymentSupport

## 📋 Descripción
Endpoint para actualizar el soporte de pago de un mantenimiento específico. Permite a los clientes subir documentos de soporte de pago (PDF, imágenes) para sus mantenimientos.

## 🔗 Endpoint
```
PUT /api/maintenances/{maintenance}/payment-support
```

## 📝 Parámetros

### Path Parameters
- `maintenance` (integer, required): ID del mantenimiento

### Request Body (multipart/form-data)
- `payment_support` (file, required): Archivo de soporte de pago
  - Tipos permitidos: PDF, JPG, JPEG, PNG
  - Tamaño máximo: 10MB

## 🔐 Autorización
- Solo el cliente propietario del mantenimiento puede actualizar el soporte de pago
- Se verifica que el mantenimiento pertenezca al cliente autenticado

## 📤 Respuesta

### ✅ Éxito (200 OK)
```json
{
  "success": true,
  "message": "Soporte de pago actualizado exitosamente",
  "data": {
    "id": 1,
    "type": "preventive",
    "date_maintenance": "2025-09-25",
    "shift": "AM",
    "status": "assigned",
    "value": "150000",
    "spare_parts": null,
    "payment_support": "https://bucket.s3.amazonaws.com/docs/pdfs/payment_support_1_1695650400_abc123.pdf",
    "device": {
      "id": 1,
      "model": "ESP32-CAM",
      "brand": "Espressif",
      "type": "lavadora"
    },
    "client": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com"
    },
    "technician": {
      "id": 1,
      "user": {
        "name": "Técnico López",
        "email": "tecnico@example.com"
      }
    },
    "description": "Mantenimiento preventivo programado"
  }
}
```

### ❌ Errores

#### 401 Unauthorized
```json
{
  "success": false,
  "message": "No autorizado para actualizar este mantenimiento"
}
```

#### 422 Validation Error
```json
{
  "success": false,
  "message": "Los datos proporcionados no son válidos",
  "errors": {
    "payment_support": [
      "El soporte de pago es obligatorio.",
      "El soporte de pago debe ser un archivo PDF, JPG, JPEG o PNG.",
      "El soporte de pago no puede ser mayor a 10MB."
    ]
  }
}
```

## 🚀 Ejemplo de Uso

### JavaScript/Fetch
```javascript
const formData = new FormData();
formData.append('payment_support', fileInput.files[0]);

const response = await fetch('/api/maintenances/1/payment-support', {
  method: 'PUT',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});

const result = await response.json();
console.log(result);
```

### cURL
```bash
curl -X PUT \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "payment_support=@/path/to/payment_support.pdf" \
  http://localhost:8000/api/maintenances/1/payment-support
```

### React Native
```javascript
import { launchImageLibrary } from 'react-native-image-picker';

const updatePaymentSupport = async (maintenanceId, file) => {
  const formData = new FormData();
  formData.append('payment_support', {
    uri: file.uri,
    type: file.type,
    name: file.fileName,
  });

  try {
    const response = await fetch(`/api/maintenances/${maintenanceId}/payment-support`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data',
      },
      body: formData,
    });

    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Error updating payment support:', error);
    throw error;
  }
};

// Uso con selector de archivos
const selectAndUploadPaymentSupport = () => {
  launchImageLibrary({
    mediaType: 'photo',
    includeBase64: false,
    maxHeight: 2000,
    maxWidth: 2000,
  }, (response) => {
    if (response.assets && response.assets[0]) {
      updatePaymentSupport(maintenanceId, response.assets[0]);
    }
  });
};
```

## 📁 Almacenamiento
- Los archivos se almacenan en Amazon S3
- Ruta: `docs/pdfs/payment_support_{maintenance_id}_{timestamp}_{random}.{extension}`
- Se genera una URL pública para acceso al archivo

## 🔄 Flujo de Trabajo
1. Cliente selecciona archivo de soporte de pago
2. Se valida el archivo (tipo, tamaño)
3. Se verifica autorización del cliente
4. Se sube archivo a S3
5. Se actualiza el mantenimiento con el nombre del archivo
6. Se retorna el mantenimiento actualizado con la URL del archivo

## ⚠️ Consideraciones
- Solo se permite un archivo por mantenimiento
- El archivo anterior se sobrescribe si se sube uno nuevo
- Se recomienda limpiar archivos antiguos periódicamente
- El archivo debe ser legible y de buena calidad para revisión

