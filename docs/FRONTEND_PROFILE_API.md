# 🎨 Guía Frontend - API de Perfiles

## 📋 Resumen

Documentación para desarrolladores frontend sobre cómo actualizar perfiles de usuario según su rol (Cliente, Técnico, Coordinador).

## 🚀 Endpoints

### 1. Obtener Perfil del Usuario
```http
GET /api/me
Authorization: Bearer {token}
```

### 2. Actualizar Perfil del Usuario
```http
PUT /api/profile
Authorization: Bearer {token}
Content-Type: application/json
```

## 👤 Datos a Enviar por Rol

### 🔹 Cliente (role: 'cliente')

**Endpoint:** `PUT /api/profile`

**Datos disponibles para actualizar:**
```json
{
  "name": "string",           // Nombre del cliente
  "email": "string",          // Email del cliente  
  "phone": "string",          // Teléfono
  "address": "string",        // Dirección
  "city": "string",          // Ciudad
  "document": "string"       // Documento (se guarda como identifier)
}
```

**Ejemplo de request:**
```json
{
  "name": "Juan Pérez",
  "email": "juan@ejemplo.com",
  "phone": "3001234567",
  "address": "Calle 123 #45-67",
  "city": "Bogotá",
  "document": "12345678"
}
```

**Validaciones:**
- `name`: string, máximo 255 caracteres
- `email`: email válido, único en la tabla users
- `phone`: string, máximo 20 caracteres
- `address`: string, máximo 255 caracteres
- `city`: string, máximo 255 caracteres
- `document`: string, máximo 20 caracteres

---

### 🔹 Técnico (role: 'tecnico')

**Endpoint:** `PUT /api/profile`

**Datos disponibles para actualizar:**
```json
{
  "name": "string",           // Nombre del usuario
  "email": "string",          // Email del usuario
  "phone": "string",          // Teléfono del técnico
  "address": "string",        // Dirección del técnico
  "document": "string"        // Documento del técnico
}
```

**Ejemplo de request:**
```json
{
  "name": "Carlos Técnico",
  "email": "carlos@ejemplo.com",
  "phone": "3009876543",
  "address": "Carrera 45 #78-90",
  "document": "87654321"
}
```

**Validaciones:**
- `name`: string, máximo 255 caracteres
- `email`: email válido, único en la tabla users
- `phone`: string, máximo 20 caracteres
- `address`: string, máximo 255 caracteres
- `document`: string, máximo 20 caracteres

**Nota:** Los datos se actualizan en dos tablas:
- `name` y `email` → tabla `users`
- `phone`, `address`, `document` → tabla `technicians`

---

### 🔹 Coordinador (role: 'coordinador')

**Endpoint:** `PUT /api/profile`

**Datos disponibles para actualizar:**
```json
{
  "name": "string",           // Nombre del usuario
  "email": "string",          // Email del usuario
  "phone": "string",          // Teléfono del coordinador
  "address": "string",        // Dirección del coordinador
  "identification": "integer"  // Identificación del coordinador
}
```

**Ejemplo de request:**
```json
{
  "name": "Ana Coordinadora",
  "email": "ana@ejemplo.com",
  "phone": "3005555555",
  "address": "Avenida 80 #12-34",
  "identification": 123456789
}
```

**Validaciones:**
- `name`: string, máximo 255 caracteres
- `email`: email válido, único en la tabla users
- `phone`: string, máximo 20 caracteres
- `address`: string, máximo 255 caracteres
- `identification`: integer, único en la tabla coordinators

**Nota:** Los datos se actualizan en dos tablas:
- `name` y `email` → tabla `users`
- `phone`, `address`, `identification` → tabla `coordinators`

---

## 📱 Ejemplos de Implementación Frontend

### React/JavaScript
```javascript
// Función para actualizar perfil
const updateProfile = async (profileData) => {
  try {
    const response = await fetch('/api/profile', {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(profileData)
    });

    const result = await response.json();
    
    if (response.ok) {
      console.log('Perfil actualizado:', result.message);
      return result;
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error('Error al actualizar perfil:', error);
    throw error;
  }
};

// Ejemplo de uso para cliente
const clientData = {
  name: "Juan Pérez",
  email: "juan@ejemplo.com",
  phone: "3001234567",
  address: "Calle 123 #45-67",
  city: "Bogotá",
  document: "12345678"
};

updateProfile(clientData);
```

### Vue.js
```javascript
// Método en componente Vue
methods: {
  async updateProfile() {
    try {
      const response = await this.$http.put('/api/profile', this.profileData);
      this.$toast.success('Perfil actualizado correctamente');
      return response.data;
    } catch (error) {
      this.$toast.error('Error al actualizar perfil: ' + error.response.data.message);
    }
  }
}
```

### Angular
```typescript
// Servicio Angular
@Injectable()
export class ProfileService {
  updateProfile(profileData: any): Observable<any> {
    return this.http.put('/api/profile', profileData);
  }
}

// Componente Angular
updateProfile() {
  this.profileService.updateProfile(this.profileForm.value)
    .subscribe({
      next: (response) => {
        this.toastr.success('Perfil actualizado correctamente');
      },
      error: (error) => {
        this.toastr.error('Error: ' + error.error.message);
      }
    });
}
```

## 🔄 Respuestas de la API

### ✅ Respuesta Exitosa
```json
{
  "message": "Perfil actualizado correctamente.",
  "success": true
}
```

### ❌ Respuesta de Error
```json
{
  "message": "Error al actualizar el perfil: No se encontró el perfil del cliente",
  "success": false
}
```

## 🚨 Códigos de Estado HTTP

- **200 OK:** Perfil actualizado correctamente
- **400 Bad Request:** Datos de validación inválidos
- **401 Unauthorized:** Token inválido o expirado
- **422 Unprocessable Entity:** Errores de validación
- **500 Internal Server Error:** Error del servidor

## 📋 Formularios por Rol

### Formulario Cliente
```html
<form id="clientForm">
  <input type="text" name="name" placeholder="Nombre del cliente" required>
  <input type="email" name="email" placeholder="Email del cliente" required>
  <input type="tel" name="phone" placeholder="Teléfono">
  <input type="text" name="address" placeholder="Dirección">
  <input type="text" name="city" placeholder="Ciudad">
  <input type="text" name="document" placeholder="Documento">
  <button type="submit">Actualizar Perfil</button>
</form>
```

### Formulario Técnico
```html
<form id="technicianForm">
  <input type="text" name="name" placeholder="Nombre del técnico" required>
  <input type="email" name="email" placeholder="Email del técnico" required>
  <input type="tel" name="phone" placeholder="Teléfono del técnico">
  <input type="text" name="address" placeholder="Dirección del técnico">
  <input type="text" name="document" placeholder="Documento del técnico">
  <button type="submit">Actualizar Perfil</button>
</form>
```

### Formulario Coordinador
```html
<form id="coordinatorForm">
  <input type="text" name="name" placeholder="Nombre del coordinador" required>
  <input type="email" name="email" placeholder="Email del coordinador" required>
  <input type="tel" name="phone" placeholder="Teléfono del coordinador">
  <input type="text" name="address" placeholder="Dirección del coordinador">
  <input type="number" name="identification" placeholder="Identificación del coordinador">
  <button type="submit">Actualizar Perfil</button>
</form>
```

## 🎯 Lógica de Formularios Dinámicos

### JavaScript para mostrar campos según rol
```javascript
// Función para mostrar campos según el rol
function showFieldsByRole(userRole) {
  const forms = {
    'cliente': ['name', 'email', 'phone', 'address', 'city', 'document'],
    'tecnico': ['name', 'email', 'phone', 'address', 'document'],
    'coordinador': ['name', 'email', 'phone', 'address', 'identification']
  };

  const fieldsToShow = forms[userRole] || [];
  
  // Mostrar/ocultar campos según el rol
  document.querySelectorAll('.profile-field').forEach(field => {
    const fieldName = field.name;
    field.style.display = fieldsToShow.includes(fieldName) ? 'block' : 'none';
  });
}

// Usar cuando se carga la página
document.addEventListener('DOMContentLoaded', () => {
  const userRole = getUserRole(); // Función que obtiene el rol del usuario
  showFieldsByRole(userRole);
});
```

## 🔍 Validaciones Frontend

### JavaScript
```javascript
// Validaciones específicas por rol
function validateProfileData(data, userRole) {
  const errors = [];

  // Validaciones comunes
  if (data.name && data.name.length > 255) {
    errors.push('El nombre no puede tener más de 255 caracteres');
  }

  if (data.email && !isValidEmail(data.email)) {
    errors.push('El email debe tener un formato válido');
  }

  if (data.phone && data.phone.length > 20) {
    errors.push('El teléfono no puede tener más de 20 caracteres');
  }

  // Validaciones específicas por rol
  if (userRole === 'cliente' || userRole === 'tecnico') {
    if (data.document && data.document.length > 20) {
      errors.push('El documento no puede tener más de 20 caracteres');
    }
  }

  if (userRole === 'coordinador') {
    if (data.identification && !Number.isInteger(Number(data.identification))) {
      errors.push('La identificación debe ser un número entero');
    }
  }

  return errors;
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}
```

## 📱 Ejemplo de Componente React

```jsx
import React, { useState, useEffect } from 'react';

const ProfileForm = ({ userRole, initialData }) => {
  const [formData, setFormData] = useState(initialData);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await fetch('/api/profile', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();
      
      if (response.ok) {
        alert('Perfil actualizado correctamente');
      } else {
        alert('Error: ' + result.message);
      }
    } catch (error) {
      alert('Error al actualizar perfil');
    } finally {
      setLoading(false);
    }
  };

  const renderFields = () => {
    const fields = {
      'cliente': ['name', 'email', 'phone', 'address', 'city', 'document'],
      'tecnico': ['name', 'email', 'phone', 'address', 'document'],
      'coordinador': ['name', 'email', 'phone', 'address', 'identification']
    };

    return fields[userRole]?.map(field => (
      <div key={field}>
        <label>{field}:</label>
        <input
          type={field === 'email' ? 'email' : field === 'identification' ? 'number' : 'text'}
          name={field}
          value={formData[field] || ''}
          onChange={(e) => setFormData({...formData, [field]: e.target.value})}
        />
      </div>
    ));
  };

  return (
    <form onSubmit={handleSubmit}>
      {renderFields()}
      <button type="submit" disabled={loading}>
        {loading ? 'Actualizando...' : 'Actualizar Perfil'}
      </button>
    </form>
  );
};

export default ProfileForm;
```

## 🎯 Resumen de Campos por Rol

| Campo | Cliente | Técnico | Coordinador |
|-------|---------|---------|-------------|
| `name` | ✅ | ✅ | ✅ |
| `email` | ✅ | ✅ | ✅ |
| `phone` | ✅ | ✅ | ✅ |
| `address` | ✅ | ✅ | ✅ |
| `city` | ✅ | ❌ | ❌ |
| `document` | ✅ | ✅ | ❌ |
| `identification` | ❌ | ❌ | ✅ |

---

**¡Listo para implementar en el frontend!** 🚀
