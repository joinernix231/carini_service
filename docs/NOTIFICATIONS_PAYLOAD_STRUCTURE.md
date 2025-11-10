# 📱 Estructura de Notificaciones Push - Frontend

## 📋 **RESUMEN EJECUTIVO**

Este documento describe la estructura exacta de los payloads de notificaciones que el backend envía al frontend React Native vía Expo Push Notifications.

---

## 🏗️ **ESTRUCTURA BASE DE TODAS LAS NOTIFICACIONES**

Todas las notificaciones siguen esta estructura base:

```typescript
interface NotificationPayload {
  title: string;           // Título de la notificación
  body: string;             // Cuerpo/mensaje de la notificación
  data: NotificationData;   // Datos adicionales para navegación y lógica
  sound: 'default';         // Sonido de la notificación
  badge: number;            // Número de badge (generalmente 1)
  priority: 'high' | 'normal'; // Prioridad de la notificación
}
```

### **Campo `data` - Estructura Base:**

```typescript
interface NotificationData {
  type: string;              // Tipo de notificación (ver tipos abajo)
  maintenance_id: number;    // ID del mantenimiento (si aplica)
  client_id?: number;        // ID del cliente (si aplica)
  screen: string;            // Pantalla a la que navegar cuando se toque
  priority?: 'high' | 'normal'; // Prioridad adicional en data
  // ... campos específicos según el tipo
}
```

---

## 🎯 **TIPOS DE NOTIFICACIONES**

### **1. 🔧 Mantenimiento Asignado** (`maintenance_assigned`)

**Cuándo se envía:** Cuando un mantenimiento es asignado a un técnico

**Payload:**
```json
{
  "title": "🔧 Mantenimiento Asignado",
  "body": "Se te ha asignado un mantenimiento para {Cliente} - {Equipo/Equipos}",
  "data": {
    "type": "maintenance_assigned",
    "maintenance_id": 123,
    "client_id": 45,
    "device_count": 1,
    "device_ids": [67],
    "date_maintenance": "2024-01-15",
    "shift": "morning",
    "screen": "MantenimientoDetail",
    "priority": "high"
  },
  "sound": "default",
  "badge": 1,
  "priority": "high"
}
```

**Estructura TypeScript:**
```typescript
interface MaintenanceAssignedData extends NotificationData {
  type: 'maintenance_assigned';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  device_ids: number[];
  date_maintenance?: string; // Formato: 'YYYY-MM-DD'
  shift?: string;             // 'morning' | 'afternoon' | 'night'
  screen: 'MantenimientoDetail';
  priority: 'high';
}
```

**Frontend - Manejo:**
```typescript
if (data.type === 'maintenance_assigned') {
  // Navegar a la pantalla de detalle del mantenimiento
  navigation.navigate('MantenimientoDetail', {
    maintenanceId: data.maintenance_id
  });
}
```

---

### **2. ✅ Mantenimiento Completado** (`maintenance_completed`)

**Cuándo se envía:** Cuando un mantenimiento es marcado como completado

**Payload:**
```json
{
  "title": "✅ Mantenimiento Completado",
  "body": "El mantenimiento creado el {fecha} ha sido completado exitosamente",
  "data": {
    "type": "maintenance_completed",
    "maintenance_id": 123,
    "device_id": 67,
    "client_id": 45,
    "screen": "MantenimientoDetail",
    "priority": "normal"
  },
  "sound": "default",
  "badge": 1,
  "priority": "normal"
}
```

**Estructura TypeScript:**
```typescript
interface MaintenanceCompletedData extends NotificationData {
  type: 'maintenance_completed';
  maintenance_id: number;
  device_id: number;
  client_id: number;
  screen: 'MantenimientoDetail';
  priority: 'normal';
}
```

---

### **3. 📋 Mantenimiento Creado** (`maintenance_created`)

**Cuándo se envía:** Cuando se crea un nuevo mantenimiento

**Payload:**
```json
{
  "title": "Nuevo Mantenimiento Creado",
  "body": "Mantenimiento para {Cliente} - {Equipo/Equipos}",
  "data": {
    "type": "maintenance_created",
    "maintenance_id": 123,
    "client_id": 45,
    "device_count": 1,
    "device_ids": [67],
    "screen": "MantenimientoDetail",
    "priority": "normal"
  },
  "sound": "default",
  "badge": 1,
  "priority": "normal"
}
```

**Estructura TypeScript:**
```typescript
interface MaintenanceCreatedData extends NotificationData {
  type: 'maintenance_created';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  device_ids: number[];
  screen: 'MantenimientoDetail';
  priority: 'normal';
}
```

---

### **4. 💰 Mantenimiento Cotizado** (`maintenance_quoted`)

**Cuándo se envía:** Cuando un mantenimiento es cotizado

**Payload:**
```json
{
  "title": "Hola, {Cliente} Mantenimiento Cotizado",
  "body": "Mantenimiento cotizado para {Equipo/Equipos}",
  "data": {
    "type": "maintenance_quoted",
    "maintenance_id": 123,
    "client_id": 45,
    "device_count": 1,
    "screen": "MantenimientoDetail"
  },
  "sound": "default",
  "badge": 1,
  "priority": "normal"
}
```

**Estructura TypeScript:**
```typescript
interface MaintenanceQuotedData extends NotificationData {
  type: 'maintenance_quoted';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  screen: 'MantenimientoDetail';
}
```

---

### **5. 💳 Pago Mantenimiento Subido** (`maintenance_verify`)

**Cuándo se envía:** Cuando se sube un pago de mantenimiento

**Payload:**
```json
{
  "title": "Hola, {Cliente} tu pago del mantenimiento ha sido verificado",
  "body": "Mantenimiento verificado",
  "data": {
    "type": "maintenance_verify",
    "maintenance_id": 123,
    "client_id": 45,
    "device_count": 1,
    "screen": "MantenimientoDetail"
  },
  "sound": "default",
  "badge": 1,
  "priority": "normal"
}
```

**Nota:** ⚠️ Este tipo también se usa para `PagoMantenimientoVerificado`, ambos usan `type: 'maintenance_verify'`

**Estructura TypeScript:**
```typescript
interface MaintenanceVerifyData extends NotificationData {
  type: 'maintenance_verify';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  screen: 'MantenimientoDetail';
}
```

---

### **6. 🔧 Sugerencia Repuesto Creada** (`spare_part_suggestion_created`)

**Cuándo se envía:** Cuando un técnico crea una sugerencia de repuesto

**Payload:**
```json
{
  "title": "🔧 Sugerencia de Repuesto",
  "body": "{Técnico} sugiere cambiar un repuesto en {Equipo}",
  "data": {
    "type": "spare_part_suggestion_created",
    "suggestion_id": 89,
    "maintenance_id": 123,
    "client_device_id": 67,
    "device_id": 12,
    "screen": "MaintenanceDetail",
    "priority": "normal"
  },
  "sound": "default",
  "badge": 1,
  "priority": "normal"
}
```

**Estructura TypeScript:**
```typescript
interface SparePartSuggestionCreatedData extends NotificationData {
  type: 'spare_part_suggestion_created';
  suggestion_id: number;
  maintenance_id: number;
  client_device_id: number;
  device_id: number | null;
  screen: 'MaintenanceDetail';
  priority: 'normal';
}
```

---

## 📱 **IMPLEMENTACIÓN EN REACT NATIVE**

### **1. Receptor de Notificaciones**

```typescript
import * as Notifications from 'expo-notifications';
import { NavigationContainerRef } from '@react-navigation/native';

// Configurar cómo se manejan las notificaciones
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

// Listener para notificaciones recibidas
Notifications.addNotificationReceivedListener(notification => {
  const data = notification.request.content.data;
  handleNotificationNavigation(data);
});

// Listener para cuando el usuario toca la notificación
Notifications.addNotificationResponseReceivedListener(response => {
  const data = response.notification.request.content.data;
  handleNotificationNavigation(data);
});
```

### **2. Manejo de Navegación**

```typescript
interface NotificationData {
  type: string;
  maintenance_id?: number;
  client_id?: number;
  screen: string;
  [key: string]: any;
}

function handleNotificationNavigation(data: NotificationData) {
  const navigation = useNavigation(); // O pasar navigation como parámetro
  
  switch (data.type) {
    case 'maintenance_assigned':
    case 'maintenance_completed':
    case 'maintenance_created':
    case 'maintenance_quoted':
    case 'maintenance_verify':
      if (data.maintenance_id) {
        navigation.navigate(data.screen, {
          maintenanceId: data.maintenance_id
        });
      }
      break;
      
    case 'spare_part_suggestion_created':
      if (data.maintenance_id) {
        navigation.navigate('MaintenanceDetail', {
          maintenanceId: data.maintenance_id,
          suggestionId: data.suggestion_id
        });
      }
      break;
      
    default:
      console.warn('Unknown notification type:', data.type);
  }
}
```

### **3. Tipos TypeScript Completos**

```typescript
// types/notifications.ts

export type NotificationType =
  | 'maintenance_assigned'
  | 'maintenance_completed'
  | 'maintenance_created'
  | 'maintenance_quoted'
  | 'maintenance_verify'
  | 'spare_part_suggestion_created';

export interface BaseNotificationData {
  type: NotificationType;
  screen: string;
  priority?: 'high' | 'normal';
}

export interface MaintenanceAssignedData extends BaseNotificationData {
  type: 'maintenance_assigned';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  device_ids: number[];
  date_maintenance?: string;
  shift?: string;
  screen: 'MantenimientoDetail';
  priority: 'high';
}

export interface MaintenanceCompletedData extends BaseNotificationData {
  type: 'maintenance_completed';
  maintenance_id: number;
  device_id: number;
  client_id: number;
  screen: 'MantenimientoDetail';
  priority: 'normal';
}

export interface MaintenanceCreatedData extends BaseNotificationData {
  type: 'maintenance_created';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  device_ids: number[];
  screen: 'MantenimientoDetail';
  priority: 'normal';
}

export interface MaintenanceQuotedData extends BaseNotificationData {
  type: 'maintenance_quoted';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  screen: 'MantenimientoDetail';
}

export interface MaintenanceVerifyData extends BaseNotificationData {
  type: 'maintenance_verify';
  maintenance_id: number;
  client_id: number | null;
  device_count: number;
  screen: 'MantenimientoDetail';
}

export interface SparePartSuggestionCreatedData extends BaseNotificationData {
  type: 'spare_part_suggestion_created';
  suggestion_id: number;
  maintenance_id: number;
  client_device_id: number;
  device_id: number | null;
  screen: 'MaintenanceDetail';
  priority: 'normal';
}

export type NotificationData =
  | MaintenanceAssignedData
  | MaintenanceCompletedData
  | MaintenanceCreatedData
  | MaintenanceQuotedData
  | MaintenanceVerifyData
  | SparePartSuggestionCreatedData;

export interface NotificationPayload {
  title: string;
  body: string;
  data: NotificationData;
  sound: 'default';
  badge: number;
  priority: 'high' | 'normal';
}
```

### **4. Hook Personalizado para Notificaciones**

```typescript
// hooks/useNotificationHandler.ts
import { useEffect, useRef } from 'react';
import * as Notifications from 'expo-notifications';
import { useNavigation } from '@react-navigation/native';
import { NotificationData } from '../types/notifications';

export function useNotificationHandler() {
  const navigation = useNavigation();
  const notificationListener = useRef<Notifications.Subscription>();
  const responseListener = useRef<Notifications.Subscription>();

  useEffect(() => {
    // Listener para notificaciones recibidas (app en foreground)
    notificationListener.current = Notifications.addNotificationReceivedListener(
      notification => {
        const data = notification.request.content.data as NotificationData;
        console.log('Notification received:', data);
        // Puedes mostrar un banner o actualizar estado
      }
    );

    // Listener para cuando el usuario toca la notificación
    responseListener.current = Notifications.addNotificationResponseReceivedListener(
      response => {
        const data = response.notification.request.content.data as NotificationData;
        handleNavigation(data);
      }
    );

    return () => {
      if (notificationListener.current) {
        Notifications.removeNotificationSubscription(notificationListener.current);
      }
      if (responseListener.current) {
        Notifications.removeNotificationSubscription(responseListener.current);
      }
    };
  }, []);

  const handleNavigation = (data: NotificationData) => {
    switch (data.type) {
      case 'maintenance_assigned':
      case 'maintenance_completed':
      case 'maintenance_created':
      case 'maintenance_quoted':
      case 'maintenance_verify':
        if (data.maintenance_id) {
          navigation.navigate(data.screen as never, {
            maintenanceId: data.maintenance_id
          } as never);
        }
        break;

      case 'spare_part_suggestion_created':
        if (data.maintenance_id) {
          navigation.navigate('MaintenanceDetail' as never, {
            maintenanceId: data.maintenance_id,
            suggestionId: data.suggestion_id
          } as never);
        }
        break;

      default:
        console.warn('Unknown notification type:', data.type);
    }
  };
}
```

---

## 🔍 **CAMPOS COMUNES Y SU PROPÓSITO**

| Campo | Tipo | Descripción | Presente en |
|-------|------|-------------|-------------|
| `type` | string | Identifica el tipo de notificación | Todas |
| `maintenance_id` | number | ID del mantenimiento relacionado | Todas |
| `client_id` | number\|null | ID del cliente (si aplica) | Mayoría |
| `screen` | string | Pantalla a la que navegar | Todas |
| `priority` | 'high'\|'normal' | Prioridad de la notificación | Mayoría |
| `device_count` | number | Cantidad de equipos | Varias |
| `device_ids` | number[] | IDs de los equipos | Varias |
| `date_maintenance` | string | Fecha del mantenimiento (YYYY-MM-DD) | maintenance_assigned |
| `shift` | string | Turno del mantenimiento | maintenance_assigned |
| `suggestion_id` | number | ID de la sugerencia | spare_part_suggestion_created |
| `client_device_id` | number | ID del dispositivo del cliente | spare_part_suggestion_created |

---

## ⚠️ **NOTAS IMPORTANTES**

1. **Formato de Fecha:** Las fechas vienen en formato `YYYY-MM-DD` (ej: `"2024-01-15"`)

2. **Pantallas de Navegación:**
   - `'MantenimientoDetail'` - Para notificaciones de mantenimiento
   - `'MaintenanceDetail'` - Para sugerencias de repuestos (nota la diferencia)

3. **Prioridad:**
   - `'high'` - Solo para `maintenance_assigned`
   - `'normal'` - Para el resto

4. **Campos Opcionales:**
   - `client_id` puede ser `null` en algunos casos
   - `date_maintenance` y `shift` solo están en `maintenance_assigned`
   - `device_id` puede ser `null` en sugerencias de repuestos

5. **Tipo de Notificación Duplicado:**
   - Tanto `PagoMantenimientoSubido` como `PagoMantenimientoVerificado` usan `type: 'maintenance_verify'`
   - Si necesitas diferenciarlos, puedes usar el título o consultar el estado del mantenimiento

---

## 🧪 **TESTING**

Para probar las notificaciones en desarrollo:

```typescript
// En tu pantalla de testing
import * as Notifications from 'expo-notifications';

const testNotification = async () => {
  await Notifications.scheduleNotificationAsync({
    content: {
      title: '🔧 Mantenimiento Asignado',
      body: 'Se te ha asignado un mantenimiento para Cliente Test - Equipo Test',
      data: {
        type: 'maintenance_assigned',
        maintenance_id: 123,
        client_id: 45,
        device_count: 1,
        device_ids: [67],
        date_maintenance: '2024-01-15',
        shift: 'morning',
        screen: 'MantenimientoDetail',
        priority: 'high'
      },
      sound: 'default',
      badge: 1,
    },
    trigger: null, // Mostrar inmediatamente
  });
};
```

---

## 📚 **REFERENCIAS**

- [Documentación Expo Notifications](https://docs.expo.dev/versions/latest/sdk/notifications/)
- [Backend: ExpoPushService.php](../app/Services/ExpoPushService.php)
- [Backend: ExpoChannel.php](../app/Broadcasting/ExpoChannel.php)
- [Notificaciones Backend](../app/Notifications/)

---

**Última actualización:** 2024-01-15

