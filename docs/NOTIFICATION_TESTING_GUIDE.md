# 🧪 Guía de Testing - Sistema de Notificaciones Push

## 📋 **RESUMEN DEL SISTEMA**

**Backend Laravel:** ✅ Completado  
**Frontend React Native:** ✅ Completado  
**Estado:** Listo para testing

---

## 🚀 **PASOS PARA PROBAR EL SISTEMA**

### **1. 🔐 Obtener Token de Autenticación**

Primero necesitas autenticarte para obtener un token JWT:

```bash
# POST /api/auth/login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "tecnico@example.com",
    "password": "password"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "Técnico Test",
      "email": "tecnico@example.com",
      "role": "tecnico"
    }
  }
}
```

---

### **2. 📱 Registrar Token de Notificación**

Una vez autenticado, registra un token de notificación:

```bash
# POST /api/notifications/register-token
curl -X POST http://localhost:8000/api/notifications/register-token \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
    "device_id": "device_12345",
    "platform": "android",
    "app_version": "1.0.0",
    "device_model": "Samsung Galaxy S21",
    "os_version": "Android 12"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Token registrado correctamente.",
  "data": {
    "token_id": 1,
    "platform": "android",
    "device_model": "Samsung Galaxy S21"
  }
}
```

---

### **3. 🔔 Probar Envío de Notificaciones**

#### **A. Enviar a usuarios específicos:**

```bash
# POST /api/notifications/send
curl -X POST http://localhost:8000/api/notifications/send \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "userIds": [1, 2, 3],
    "title": "🧪 Notificación de Prueba",
    "body": "Esta es una notificación de testing del sistema",
    "data": {
      "type": "test",
      "screen": "HomeScreen",
      "priority": "normal"
    }
  }'
```

#### **B. Enviar por rol:**

```bash
# POST /api/notifications/send-to-role
curl -X POST http://localhost:8000/api/notifications/send-to-role \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{
    "role": "tecnico",
    "title": "🔧 Nuevo Mantenimiento",
    "body": "Se te ha asignado un nuevo mantenimiento",
    "data": {
      "type": "maintenance_assigned",
      "maintenance_id": 123,
      "screen": "MantenimientoDetail"
    }
  }'
```

---

### **4. 📊 Verificar Tokens Registrados**

```bash
# GET /api/notifications/user-tokens
curl -X GET http://localhost:8000/api/notifications/user-tokens \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Tokens obtenidos correctamente.",
  "data": {
    "tokens": [
      {
        "id": 1,
        "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
        "platform": "android",
        "device_model": "Samsung Galaxy S21",
        "last_used_at": "2025-10-01T10:30:00.000000Z",
        "created_at": "2025-10-01T10:30:00.000000Z"
      }
    ]
  }
}
```

---

### **5. 🧪 Probar Notificaciones Laravel**

#### **A. Crear un comando de testing:**

```bash
php artisan make:command TestNotifications
```

#### **B. Probar notificaciones desde tinker:**

```bash
php artisan tinker
```

```php
// En tinker:
use App\Models\User;
use App\Models\Maintenance;
use App\Notifications\MantenimientoAsignado;
use App\Notifications\MantenimientoCompletado;
use App\Notifications\MaintenanceCreated;

// Obtener un usuario técnico
$tecnico = User::where('role', 'tecnico')->first();

// Obtener un mantenimiento
$mantenimiento = Maintenance::first();

// Probar notificación de asignación (Email + Push)
$tecnico->notify(new MantenimientoAsignado($mantenimiento));

// Probar notificación de completado (Solo Push)
$cliente = User::where('role', 'cliente')->first();
$cliente->notify(new MantenimientoCompletado($mantenimiento));

// Probar alerta urgente (Solo Push)
$coordinador = User::where('role', 'coordinador')->first();
$coordinador->notify(new MaintenanceCreated($mantenimiento, 'Mantenimiento crítico detectado'));
```

---

### **6. 📱 Testing con Frontend React Native**

Si tienes el frontend configurado:

```typescript
// En tu app React Native
import { pushNotificationService } from './src/services/PushNotificationService';

// Inicializar el servicio
await pushNotificationService.initialize();

// Registrar token automáticamente
await pushNotificationService.registerToken(userId, authToken);

// Probar notificación local
await pushNotificationService.sendLocalNotification(
  'Prueba Local',
  'Esta es una notificación de prueba',
  { type: 'test' }
);
```

---

### **7. 🔍 Verificar Logs**

Revisar los logs de Laravel para ver el envío de notificaciones:

```bash
tail -f storage/logs/laravel.log
```

**Logs esperados:**
```
[2025-10-01 10:30:00] local.INFO: Push notifications sent {"sent":1,"failed":0,"total":1}
[2025-10-01 10:30:00] local.INFO: Expo notification sent {"user_id":1,"sent":1,"failed":0}
```

---

### **8. 🧹 Limpieza de Testing**

```bash
# Desregistrar token
curl -X DELETE http://localhost:8000/api/notifications/unregister-token \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -d '{"token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"}'

# O desregistrar todos los tokens del usuario
curl -X DELETE http://localhost:8000/api/notifications/unregister-token \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

## 🎯 **CASOS DE PRUEBA ESPECÍFICOS**

### **Caso 1: Flujo Completo de Mantenimiento**

1. **Asignar mantenimiento a técnico:**
   ```php
   $tecnico->notify(new MantenimientoAsignado($mantenimiento));
   // ✅ Debe enviar Email + Push
   ```

2. **Completar mantenimiento:**
   ```php
   $cliente->notify(new MantenimientoCompletado($mantenimiento));
   // ✅ Debe enviar solo Push
   ```

3. **Alerta urgente:**
   ```php
   $coordinador->notify(new AlertaUrgente($mantenimiento, 'Equipo crítico'));
   // ✅ Debe enviar solo Push
   ```

### **Caso 2: Testing de Tokens**

1. **Registrar múltiples tokens:**
   - iOS: `ExponentPushToken[ios_token_123]`
   - Android: `ExponentPushToken[android_token_456]`
   - Web: `ExponentPushToken[web_token_789]`

2. **Verificar que todos reciben notificaciones**

3. **Desactivar un token y verificar que no recibe**

### **Caso 3: Testing de Roles**

1. **Enviar a todos los técnicos:**
   ```bash
   curl -X POST /api/notifications/send-to-role \
     -d '{"role": "tecnico", "title": "Test", "body": "Mensaje"}'
   ```

2. **Enviar a todos los clientes:**
   ```bash
   curl -X POST /api/notifications/send-to-role \
     -d '{"role": "cliente", "title": "Test", "body": "Mensaje"}'
   ```

---

## 🚨 **SOLUCIÓN DE PROBLEMAS**

### **Error: "No active tokens found"**
- Verificar que el token esté registrado y activo
- Revisar que el usuario tenga tokens en la base de datos

### **Error: "Expo API request failed"**
- Verificar conexión a internet
- Revisar logs de Laravel para detalles del error

### **Error: "Token validation failed"**
- Verificar formato del token Expo
- Asegurar que el token sea válido

### **Notificaciones no llegan al dispositivo:**
- Verificar que el dispositivo tenga conexión
- Revisar configuración de notificaciones en el dispositivo
- Verificar que la app esté en primer plano o segundo plano

---

## 📊 **MÉTRICAS DE TESTING**

### **Verificar estadísticas:**
```php
// En tinker
$expoService = app(\App\Services\ExpoPushService::class);
$stats = $expoService->getTokenStats();
print_r($stats);
```

**Respuesta esperada:**
```php
[
  'total_tokens' => 5,
  'active_tokens' => 4,
  'inactive_tokens' => 1,
  'tokens_by_platform' => [
    'android' => 2,
    'ios' => 2,
    'web' => 0
  ]
]
```

---

## ✅ **CHECKLIST DE TESTING**

- [ ] ✅ Rutas configuradas correctamente
- [ ] ✅ Autenticación funcionando
- [ ] ✅ Registro de tokens funcionando
- [ ] ✅ Envío a usuarios específicos funcionando
- [ ] ✅ Envío por rol funcionando
- [ ] ✅ Notificaciones Laravel funcionando
- [ ] ✅ Email solo para asignaciones
- [ ] ✅ Push para todos los casos
- [ ] ✅ Logs funcionando correctamente
- [ ] ✅ Limpieza de tokens funcionando

---

**🎯 ¡Sistema listo para testing completo!**

Sigue estos pasos en orden y tendrás el sistema de notificaciones push funcionando perfectamente.
