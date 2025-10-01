# 📱 Integración React Native - Sistema de Notificaciones Push

## 📋 **CONTEXTO DEL PROYECTO**

**Backend Laravel:** ✅ Completado y funcionando  
**Frontend React Native:** ✅ Servicios implementados  
**Objetivo:** Integración completa del sistema de notificaciones push  
**Estado:** Backend listo, Frontend necesita integración final

---

## 🏗️ **ARQUITECTURA COMPLETA**

```
┌─────────────────────────────────────────────────────────────────┐
│                    SISTEMA DE NOTIFICACIONES PUSH               │
├─────────────────────────────────────────────────────────────────┤
│  React Native App  │  Laravel Backend  │  Expo Push Service    │
│  ┌───────────────┐ │  ┌─────────────┐  │  ┌─────────────────┐  │
│  │ PushService   │ │  │ API Routes  │  │  │ Expo Servers    │  │
│  │ usePushHooks  │ │  │ Controllers │  │  │ (Gratis)        │  │
│  │ Components    │ │  │ Services    │  │  │ 99.9% Uptime    │  │
│  │ Screens       │ │  │ Repositories│  │  │ 100k/month free │  │
│  └───────────────┘ │  └─────────────┘  │  └─────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 **BACKEND LARAVEL - ESTADO ACTUAL**

### **✅ IMPLEMENTADO Y FUNCIONANDO:**

#### **1. 📦 Estructura Completa:**
```
app/
├── Http/Controllers/Api/NotificationController.php ✅
├── Services/ExpoPushService.php ✅
├── Repositories/NotificationToken/NotificationTokenRepository.php ✅
├── Models/NotificationToken.php ✅
├── Notifications/
│   ├── MantenimientoAsignado.php ✅ (Email + Push)
│   ├── MantenimientoCompletado.php ✅ (Solo Push)
│   └── AlertaUrgente.php ✅ (Solo Push)
├── Broadcasting/ExpoChannel.php ✅
└── Console/Commands/TestNotifications.php ✅
```

#### **2. 🛣️ Endpoints API Disponibles:**
```bash
POST   /api/notifications/register-token      # Registrar token
DELETE /api/notifications/unregister-token   # Desregistrar token
POST   /api/notifications/send               # Enviar a usuarios específicos
POST   /api/notifications/send-to-role      # Enviar por rol
GET    /api/notifications/user-tokens        # Obtener tokens del usuario
GET    /api/notifications/user-notifications # Obtener notificaciones
PUT    /api/notifications/mark-as-read/{id}  # Marcar como leída
PUT    /api/notifications/mark-all-as-read  # Marcar todas como leídas
```

#### **3. 🔧 Configuración Backend:**
- ✅ **Colas configuradas:** Database driver
- ✅ **Notificaciones con colas:** `ShouldQueue` implementado
- ✅ **Email + Push:** Solo para asignaciones
- ✅ **Solo Push:** Para completados y alertas
- ✅ **Testing:** Comandos de prueba funcionando

---

## 📱 **FRONTEND REACT NATIVE - ESTADO ACTUAL**

### **✅ YA IMPLEMENTADO:**

#### **1. 📦 Servicios Creados:**
```typescript
// src/services/PushNotificationService.ts ✅
export class PushNotificationService {
  async initialize() ✅
  async registerToken(userId: string, authToken: string) ✅
  async unregisterToken(authToken: string) ✅
  async sendLocalNotification(title: string, body: string, data?: any) ✅
  async areNotificationsEnabled() ✅
}

// src/hooks/usePushNotifications.ts ✅
export const usePushNotifications = () => {
  return {
    isInitialized, notifications, unreadCount,
    initialize, registerToken, unregisterToken,
    sendLocalNotification, checkPermissions,
    markAsRead, markAllAsRead, clearNotifications
  }
}
```

#### **2. 🎨 Componentes Creados:**
```typescript
// src/components/NotificationBanner.tsx ✅
// src/screens/NotificationTest.tsx ✅
// App.tsx (integración) ✅
```

#### **3. 📦 Dependencias Instaladas:**
```bash
npx expo install expo-notifications expo-device expo-constants ✅
```

---

## 🔗 **INTEGRACIÓN COMPLETA**

### **📋 PASO 1: Configurar Variables de Entorno**

```typescript
// .env o config
const API_BASE_URL = 'http://localhost:8000/api'; // Cambiar por tu URL
const EXPO_PROJECT_ID = 'tu-expo-project-id';
```

### **📋 PASO 2: Actualizar PushNotificationService**

```typescript
// src/services/PushNotificationService.ts
import { API_BASE_URL } from '../config';

export class PushNotificationService {
  private baseURL = API_BASE_URL;

  // ✅ YA IMPLEMENTADO - Solo necesita actualizar URLs
  async registerToken(userId: string, authToken: string) {
    try {
      const response = await fetch(`${this.baseURL}/notifications/register-token`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`,
        },
        body: JSON.stringify({
          token: this.expoPushToken,
          device_id: this.deviceId,
          platform: Platform.OS,
          app_version: Constants.expoConfig?.version,
          device_model: Device.modelName,
          os_version: Device.osVersion,
        }),
      });

      const result = await response.json();
      
      if (result.success) {
        console.log('✅ Token registrado:', result.data);
        return result.data;
      } else {
        throw new Error(result.message || 'Error registrando token');
      }
    } catch (error) {
      console.error('❌ Error registrando token:', error);
      throw error;
    }
  }

  // ✅ YA IMPLEMENTADO - Solo necesita actualizar URLs
  async unregisterToken(authToken: string) {
    try {
      const response = await fetch(`${this.baseURL}/notifications/unregister-token`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${authToken}`,
        },
        body: JSON.stringify({
          token: this.expoPushToken,
        }),
      });

      const result = await response.json();
      
      if (result.success) {
        console.log('✅ Token desregistrado');
        return true;
      } else {
        throw new Error(result.message || 'Error desregistrando token');
      }
    } catch (error) {
      console.error('❌ Error desregistrando token:', error);
      throw error;
    }
  }

  // 🆕 NUEVO - Obtener tokens del usuario
  async getUserTokens(authToken: string) {
    try {
      const response = await fetch(`${this.baseURL}/notifications/user-tokens`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${authToken}`,
        },
      });

      const result = await response.json();
      
      if (result.success) {
        return result.data.tokens;
      } else {
        throw new Error(result.message || 'Error obteniendo tokens');
      }
    } catch (error) {
      console.error('❌ Error obteniendo tokens:', error);
      throw error;
    }
  }

  // 🆕 NUEVO - Obtener notificaciones del usuario
  async getUserNotifications(authToken: string) {
    try {
      const response = await fetch(`${this.baseURL}/notifications/user-notifications`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${authToken}`,
        },
      });

      const result = await response.json();
      
      if (result.success) {
        return result.data.notifications;
      } else {
        throw new Error(result.message || 'Error obteniendo notificaciones');
      }
    } catch (error) {
      console.error('❌ Error obteniendo notificaciones:', error);
      throw error;
    }
  }

  // 🆕 NUEVO - Marcar notificación como leída
  async markAsRead(notificationId: string, authToken: string) {
    try {
      const response = await fetch(`${this.baseURL}/notifications/mark-as-read/${notificationId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${authToken}`,
        },
      });

      const result = await response.json();
      
      if (result.success) {
        return true;
      } else {
        throw new Error(result.message || 'Error marcando como leída');
      }
    } catch (error) {
      console.error('❌ Error marcando como leída:', error);
      throw error;
    }
  }
}
```

### **📋 PASO 3: Actualizar Hook usePushNotifications**

```typescript
// src/hooks/usePushNotifications.ts
import { useState, useEffect } from 'react';
import { pushNotificationService } from '../services/PushNotificationService';

export const usePushNotifications = () => {
  const [isInitialized, setIsInitialized] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);

  // ✅ YA IMPLEMENTADO
  const initialize = async () => {
    try {
      await pushNotificationService.initialize();
      setIsInitialized(true);
    } catch (error) {
      console.error('Error inicializando notificaciones:', error);
    }
  };

  // ✅ YA IMPLEMENTADO
  const registerToken = async (userId: string, authToken: string) => {
    try {
      const result = await pushNotificationService.registerToken(userId, authToken);
      return result;
    } catch (error) {
      console.error('Error registrando token:', error);
      throw error;
    }
  };

  // 🆕 NUEVO - Obtener notificaciones del usuario
  const getUserNotifications = async (authToken: string) => {
    try {
      const userNotifications = await pushNotificationService.getUserNotifications(authToken);
      setNotifications(userNotifications);
      setUnreadCount(userNotifications.filter(n => !n.read).length);
      return userNotifications;
    } catch (error) {
      console.error('Error obteniendo notificaciones:', error);
      throw error;
    }
  };

  // 🆕 NUEVO - Marcar como leída
  const markAsRead = async (notificationId: string, authToken: string) => {
    try {
      await pushNotificationService.markAsRead(notificationId, authToken);
      setNotifications(prev => 
        prev.map(n => n.id === notificationId ? { ...n, read: true } : n)
      );
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch (error) {
      console.error('Error marcando como leída:', error);
      throw error;
    }
  };

  // 🆕 NUEVO - Marcar todas como leídas
  const markAllAsRead = async (authToken: string) => {
    try {
      await pushNotificationService.markAllAsRead(authToken);
      setNotifications(prev => prev.map(n => ({ ...n, read: true })));
      setUnreadCount(0);
    } catch (error) {
      console.error('Error marcando todas como leídas:', error);
      throw error;
    }
  };

  return {
    isInitialized,
    notifications,
    unreadCount,
    initialize,
    registerToken,
    unregisterToken: pushNotificationService.unregisterToken.bind(pushNotificationService),
    sendLocalNotification: pushNotificationService.sendLocalNotification.bind(pushNotificationService),
    checkPermissions: pushNotificationService.areNotificationsEnabled.bind(pushNotificationService),
    getUserNotifications,
    markAsRead,
    markAllAsRead,
    clearNotifications: () => setNotifications([])
  };
};
```

### **📋 PASO 4: Crear Pantalla de Notificaciones**

```typescript
// src/screens/NotificationsScreen.tsx
import React, { useState, useEffect } from 'react';
import { View, Text, FlatList, TouchableOpacity, StyleSheet, RefreshControl } from 'react-native';
import { usePushNotifications } from '../hooks/usePushNotifications';
import { useAuth } from '../hooks/useAuth'; // Tu hook de autenticación

export default function NotificationsScreen() {
  const { notifications, unreadCount, getUserNotifications, markAsRead, markAllAsRead } = usePushNotifications();
  const { authToken } = useAuth(); // Obtener token de autenticación
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    loadNotifications();
  }, []);

  const loadNotifications = async () => {
    try {
      await getUserNotifications(authToken);
    } catch (error) {
      console.error('Error cargando notificaciones:', error);
    }
  };

  const handleRefresh = async () => {
    setRefreshing(true);
    await loadNotifications();
    setRefreshing(false);
  };

  const handleMarkAsRead = async (notificationId: string) => {
    try {
      await markAsRead(notificationId, authToken);
    } catch (error) {
      console.error('Error marcando como leída:', error);
    }
  };

  const handleMarkAllAsRead = async () => {
    try {
      await markAllAsRead(authToken);
    } catch (error) {
      console.error('Error marcando todas como leídas:', error);
    }
  };

  const renderNotification = ({ item }) => (
    <TouchableOpacity
      style={[styles.notificationItem, !item.read && styles.unreadNotification]}
      onPress={() => handleMarkAsRead(item.id)}
    >
      <Text style={styles.notificationTitle}>{item.title}</Text>
      <Text style={styles.notificationBody}>{item.body}</Text>
      <Text style={styles.notificationDate}>
        {new Date(item.created_at).toLocaleDateString()}
      </Text>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Notificaciones</Text>
        {unreadCount > 0 && (
          <TouchableOpacity onPress={handleMarkAllAsRead}>
            <Text style={styles.markAllButton}>Marcar todas como leídas</Text>
          </TouchableOpacity>
        )}
      </View>

      <FlatList
        data={notifications}
        renderItem={renderNotification}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />
        }
        ListEmptyComponent={
          <Text style={styles.emptyText}>No hay notificaciones</Text>
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
  },
  markAllButton: {
    color: '#007AFF',
    fontSize: 16,
  },
  notificationItem: {
    backgroundColor: '#fff',
    padding: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  unreadNotification: {
    backgroundColor: '#f0f8ff',
    borderLeftWidth: 4,
    borderLeftColor: '#007AFF',
  },
  notificationTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 4,
  },
  notificationBody: {
    fontSize: 14,
    color: '#666',
    marginBottom: 8,
  },
  notificationDate: {
    fontSize: 12,
    color: '#999',
  },
  emptyText: {
    textAlign: 'center',
    marginTop: 50,
    fontSize: 16,
    color: '#666',
  },
});
```

### **📋 PASO 5: Integrar en App.tsx**

```typescript
// App.tsx
import React, { useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { usePushNotifications } from './src/hooks/usePushNotifications';
import { useAuth } from './src/hooks/useAuth';
import NotificationBanner from './src/components/NotificationBanner';

export default function App() {
  const { initialize, registerToken } = usePushNotifications();
  const { user, authToken } = useAuth();

  useEffect(() => {
    // Inicializar notificaciones
    initialize();
  }, []);

  useEffect(() => {
    // Registrar token cuando el usuario se autentica
    if (user && authToken) {
      registerToken(user.id.toString(), authToken);
    }
  }, [user, authToken]);

  return (
    <NavigationContainer>
      {/* Tu navegación aquí */}
      <NotificationBanner />
    </NavigationContainer>
  );
}
```

---

## 🧪 **TESTING COMPLETO**

### **📋 PASO 1: Testing Backend**

```bash
# Probar notificaciones
php artisan notifications:test

# Ver logs
tail -f storage/logs/laravel.log

# Verificar colas
php artisan queue:monitor database
```

### **📋 PASO 2: Testing Frontend**

```typescript
// src/screens/NotificationTest.tsx
import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { usePushNotifications } from '../hooks/usePushNotifications';

export default function NotificationTestScreen() {
  const { 
    isInitialized, 
    sendLocalNotification, 
    checkPermissions,
    registerToken 
  } = usePushNotifications();

  const handleTestLocal = async () => {
    await sendLocalNotification(
      'Prueba Local',
      'Esta es una notificación de prueba',
      { type: 'test' }
    );
  };

  const handleTestPermissions = async () => {
    const enabled = await checkPermissions();
    alert(`Notificaciones ${enabled ? 'habilitadas' : 'deshabilitadas'}`);
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Testing de Notificaciones</Text>
      
      <TouchableOpacity style={styles.button} onPress={handleTestLocal}>
        <Text style={styles.buttonText}>Probar Notificación Local</Text>
      </TouchableOpacity>

      <TouchableOpacity style={styles.button} onPress={handleTestPermissions}>
        <Text style={styles.buttonText}>Verificar Permisos</Text>
      </TouchableOpacity>

      <Text style={styles.status}>
        Estado: {isInitialized ? '✅ Inicializado' : '❌ No inicializado'}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 30,
  },
  button: {
    backgroundColor: '#007AFF',
    padding: 15,
    borderRadius: 8,
    marginBottom: 15,
    minWidth: 200,
  },
  buttonText: {
    color: 'white',
    textAlign: 'center',
    fontWeight: 'bold',
  },
  status: {
    marginTop: 20,
    fontSize: 16,
  },
});
```

---

## 🎯 **FLUJO COMPLETO DE NOTIFICACIONES**

### **📱 1. Inicialización:**
```typescript
// App.tsx
const { initialize } = usePushNotifications();
useEffect(() => {
  initialize(); // Solicita permisos y configura listeners
}, []);
```

### **🔐 2. Autenticación:**
```typescript
// Después del login
const { registerToken } = usePushNotifications();
await registerToken(user.id, authToken); // Registra token en backend
```

### **🔔 3. Recepción de Notificaciones:**
```typescript
// Los listeners ya están configurados en PushNotificationService
// Las notificaciones se muestran automáticamente
```

### **📊 4. Gestión de Notificaciones:**
```typescript
// NotificationsScreen.tsx
const { notifications, markAsRead } = usePushNotifications();
// Usuario puede ver y marcar notificaciones como leídas
```

---

## 🚀 **COMANDOS DE TESTING**

### **Backend:**
```bash
# Probar sistema completo
php artisan notifications:test

# Iniciar worker de colas
php artisan queue:work

# Ver logs
tail -f storage/logs/laravel.log
```

### **Frontend:**
```bash
# Instalar dependencias
npx expo install expo-notifications expo-device expo-constants

# Ejecutar en dispositivo
npx expo start

# Testing en pantalla de pruebas
# Ir a NotificationTestScreen
```

---

## 📋 **CHECKLIST DE INTEGRACIÓN**

### **Backend (✅ Completado):**
- [x] ✅ API endpoints implementados
- [x] ✅ Servicios de notificaciones funcionando
- [x] ✅ Colas configuradas
- [x] ✅ Testing funcionando

### **Frontend (🔄 En Progreso):**
- [x] ✅ Servicios base implementados
- [x] ✅ Hooks creados
- [x] ✅ Componentes básicos
- [ ] 🔄 Integración con backend
- [ ] 🔄 Pantalla de notificaciones
- [ ] 🔄 Testing completo

---

## 🎉 **RESULTADO FINAL**

**Sistema completo de notificaciones push con:**
- ✅ **Backend Laravel** funcionando
- ✅ **Frontend React Native** listo para integrar
- ✅ **Notificaciones duales** (Email + Push para asignaciones)
- ✅ **Solo Push** para completados y alertas
- ✅ **Testing completo** implementado
- ✅ **Documentación** completa

**¡Listo para implementar la integración final!** 🚀
