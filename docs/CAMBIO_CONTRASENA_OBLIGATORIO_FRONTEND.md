# Cambio de Contraseña Obligatorio - Guía Frontend

## 📋 Resumen

El sistema ahora requiere que los usuarios cambien su contraseña la primera vez que ingresan a la aplicación. Esto es similar al sistema de aceptación de políticas que ya existe.

---

## 🔄 Flujo Completo

### **Paso 1: Login del Usuario**

**Endpoint:** `POST /api/login`

**Body:**
```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña_temporal"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Login exitoso.",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "role": "tecnico",
      "policy_accepted": true,
      "must_change_password": true  // ⭐ Este flag indica si debe cambiar contraseña
    }
  }
}
```

**Importante:** 
- Si `must_change_password: true` → El usuario DEBE cambiar su contraseña antes de usar la app
- Si `must_change_password: false` → El usuario puede usar la app normalmente

---

### **Paso 2: Verificar Estado del Usuario**

Después del login, siempre verifica el estado del usuario consultando:

**Endpoint:** `GET /api/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Usuario obtenido correctamente.",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@ejemplo.com",
    "role": "tecnico",
    "policy_accepted": true,
    "must_change_password": true,  // ⭐ Verificar este flag
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "technician_data": { ... }
  }
}
```

---

### **Paso 3: Bloqueo de Acceso**

Si `must_change_password: true`, el backend **bloqueará automáticamente** el acceso a todas las rutas excepto:
- `GET /api/me` - Para obtener información del usuario
- `POST /api/change-password` - Para cambiar la contraseña

**Cualquier otra ruta retornará:**
```json
{
  "success": false,
  "message": "Debes cambiar tu contraseña antes de continuar",
  "code": 403,
  "data": {
    "must_change_password": true
  }
}
```

**Recomendación Frontend:**
- Después del login, verifica `must_change_password`
- Si es `true`, muestra inmediatamente la pantalla de cambio de contraseña
- Bloquea la navegación a otras pantallas hasta que cambie la contraseña
- Intercepta las respuestas 403 con `must_change_password: true` y redirige a la pantalla de cambio

---

### **Paso 4: Cambiar Contraseña**

**Endpoint:** `POST /api/change-password`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "current_password": "contraseña_actual",
  "new_password": "nueva_contraseña_segura",
  "new_password_confirmation": "nueva_contraseña_segura"
}
```

**Validaciones:**
- `current_password`: Requerido, debe ser la contraseña actual del usuario
- `new_password`: Requerido, mínimo 8 caracteres
- `new_password_confirmation`: Requerido, debe coincidir con `new_password`

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Contraseña cambiada exitosamente",
  "data": []
}
```

**Errores Posibles:**

**422 - Contraseña actual incorrecta:**
```json
{
  "success": false,
  "message": "La contraseña actual es incorrecta",
  "code": 422
}
```

**422 - Nueva contraseña igual a la actual:**
```json
{
  "success": false,
  "message": "La nueva contraseña debe ser diferente a la actual",
  "code": 422
}
```

**422 - Validación fallida:**
```json
{
  "success": false,
  "message": "Ha ocurrido un error de validación",
  "data": {
    "new_password": [
      "La nueva contraseña debe tener al menos 8 caracteres"
    ],
    "new_password_confirmation": [
      "La confirmación de la nueva contraseña no coincide"
    ]
  }
}
```

---

### **Paso 5: Después de Cambiar Contraseña**

Una vez que el usuario cambia su contraseña exitosamente:

1. **Actualizar estado local:**
   - El backend automáticamente pone `must_change_password = false`
   - Puedes consultar `GET /api/me` para verificar el nuevo estado

2. **Permitir acceso normal:**
   - Ahora el usuario puede acceder a todas las rutas normalmente
   - Puedes navegar a la pantalla principal de la app

3. **Opcional - Refrescar token:**
   - Si quieres, puedes hacer un nuevo login con la nueva contraseña para obtener un token fresco

---

## 📱 Implementación Recomendada en Frontend

### **1. Interceptor de Respuestas**

Crea un interceptor que detecte cuando el backend bloquea el acceso:

```javascript
// Ejemplo en React Native / JavaScript
axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 403 && 
        error.response?.data?.data?.must_change_password) {
      // Redirigir a pantalla de cambio de contraseña
      navigation.navigate('ChangePassword');
    }
    return Promise.reject(error);
  }
);
```

### **2. Verificación después del Login**

```javascript
const handleLogin = async (email, password) => {
  try {
    const response = await login(email, password);
    const { token, user } = response.data;
    
    // Guardar token
    await AsyncStorage.setItem('token', token);
    
    // Verificar si debe cambiar contraseña
    if (user.must_change_password) {
      // Navegar directamente a pantalla de cambio de contraseña
      navigation.navigate('ChangePassword', { 
        isRequired: true 
      });
    } else {
      // Navegar a pantalla principal
      navigation.navigate('Home');
    }
  } catch (error) {
    // Manejar error de login
  }
};
```

### **3. Pantalla de Cambio de Contraseña**

```javascript
const ChangePasswordScreen = ({ route }) => {
  const { isRequired = false } = route.params || {};
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);
  
  const handleChangePassword = async () => {
    // Validaciones frontend
    if (newPassword.length < 8) {
      Alert.alert('Error', 'La contraseña debe tener al menos 8 caracteres');
      return;
    }
    
    if (newPassword !== confirmPassword) {
      Alert.alert('Error', 'Las contraseñas no coinciden');
      return;
    }
    
    setLoading(true);
    try {
      await changePassword(currentPassword, newPassword, confirmPassword);
      
      Alert.alert('Éxito', 'Contraseña cambiada exitosamente', [
        {
          text: 'OK',
          onPress: () => {
            if (isRequired) {
              // Si era obligatorio, ahora puede acceder
              navigation.navigate('Home');
            } else {
              // Si era opcional, volver atrás
              navigation.goBack();
            }
          }
        }
      ]);
    } catch (error) {
      Alert.alert('Error', error.response?.data?.message || 'Error al cambiar contraseña');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <View>
      {isRequired && (
        <Text style={styles.warning}>
          ⚠️ Debes cambiar tu contraseña antes de continuar
        </Text>
      )}
      
      <TextInput
        placeholder="Contraseña actual"
        secureTextEntry
        value={currentPassword}
        onChangeText={setCurrentPassword}
      />
      
      <TextInput
        placeholder="Nueva contraseña (mínimo 8 caracteres)"
        secureTextEntry
        value={newPassword}
        onChangeText={setNewPassword}
      />
      
      <TextInput
        placeholder="Confirmar nueva contraseña"
        secureTextEntry
        value={confirmPassword}
        onChangeText={setConfirmPassword}
      />
      
      <Button
        title="Cambiar Contraseña"
        onPress={handleChangePassword}
        disabled={loading}
      />
      
      {!isRequired && (
        <Button
          title="Cancelar"
          onPress={() => navigation.goBack()}
        />
      )}
    </View>
  );
};
```

### **4. Función para Cambiar Contraseña**

```javascript
const changePassword = async (currentPassword, newPassword, newPasswordConfirmation) => {
  const token = await AsyncStorage.getItem('token');
  
  const response = await fetch(`${API_BASE_URL}/change-password`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      current_password: currentPassword,
      new_password: newPassword,
      new_password_confirmation: newPasswordConfirmation
    })
  });
  
  const result = await response.json();
  
  if (!result.success) {
    throw new Error(result.message);
  }
  
  return result;
};
```

---

## 🔍 Estados del Usuario

| Estado | `must_change_password` | Acción Requerida |
|--------|----------------------|------------------|
| Usuario nuevo | `true` | Debe cambiar contraseña |
| Usuario que ya cambió | `false` | Acceso normal |
| Admin fuerza cambio | `true` | Debe cambiar contraseña |

---

## ⚠️ Puntos Importantes

1. **Bloqueo automático:** El backend bloquea automáticamente todas las rutas excepto `/me` y `/change-password` cuando `must_change_password = true`

2. **Verificación constante:** Siempre verifica `must_change_password` después del login y en el endpoint `/me`

3. **Navegación bloqueada:** Si `must_change_password = true`, bloquea la navegación en el frontend hasta que cambie la contraseña

4. **Mensajes claros:** Muestra mensajes claros al usuario explicando por qué debe cambiar su contraseña

5. **Validación frontend:** Valida la contraseña en el frontend antes de enviar (mínimo 8 caracteres, coincidencia de confirmación)

6. **Manejo de errores:** Maneja todos los posibles errores (contraseña actual incorrecta, nueva igual a actual, validaciones)

---

## 📝 Checklist de Implementación

- [ ] Interceptor de respuestas para detectar bloqueo 403
- [ ] Verificación de `must_change_password` después del login
- [ ] Pantalla de cambio de contraseña con validaciones
- [ ] Bloqueo de navegación cuando `must_change_password = true`
- [ ] Manejo de errores (contraseña incorrecta, validaciones, etc.)
- [ ] Mensajes claros al usuario
- [ ] Actualización del estado después de cambiar contraseña
- [ ] Pruebas con usuario nuevo y usuario existente

---

## 🔗 Endpoints Resumen

| Acción | Método | Endpoint | Descripción |
|--------|--------|----------|-------------|
| Login | POST | `/api/login` | Retorna `must_change_password` en la respuesta |
| Obtener usuario | GET | `/api/me` | Retorna `must_change_password` en la respuesta |
| Cambiar contraseña | POST | `/api/change-password` | Cambia contraseña y actualiza `must_change_password = false` |

---

## ❓ Preguntas Frecuentes

**P: ¿Qué pasa si el usuario intenta acceder a otra ruta sin cambiar la contraseña?**  
R: El backend retorna error 403 con el mensaje "Debes cambiar tu contraseña antes de continuar"

**P: ¿Puedo permitir que el usuario cancele el cambio de contraseña?**  
R: No, si `must_change_password = true`, el backend bloquea el acceso hasta que cambie la contraseña

**P: ¿Qué pasa si el usuario olvida su contraseña actual?**  
R: Necesitarías implementar un sistema de recuperación de contraseña separado (no está incluido en esta funcionalidad)

**P: ¿Puedo forzar a un usuario a cambiar su contraseña desde el admin?**  
R: Sí, el admin puede actualizar `must_change_password = true` en la base de datos para cualquier usuario

**P: ¿La nueva contraseña puede ser igual a la actual?**  
R: No, el backend valida que la nueva contraseña sea diferente a la actual

---

**Última actualización:** Noviembre 2024  
**Versión Backend:** Implementación completa

