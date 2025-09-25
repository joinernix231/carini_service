# Documentación de Seeders - Sistema de Mantenimientos

## 🎯 Usuarios Específicos Creados

### **Credenciales de Acceso:**

| Email | Contraseña | Rol | Nombre | Documento/Identificación |
|-------|------------|-----|--------|-------------------------|
| `joinernix2@gmail.com` | `saiz123` | Administrador | Joiner Administrador | 12345678 |
| `mauricio@gmail.com` | `saiz123` | Coordinador | Mauricio Coordinador | 12345678 |
| `eyder@gmail.com` | `saiz123` | Técnico | Eyder Técnico | 12345678 |
| `jo@gmail.com` | `saiz123` | Cliente | Joiner Davila Cliente | 12345678 |

## 📊 Seeders Implementados

### **1. ClientSeeder**
- ✅ **Administrador:** `joinernix2@gmail.com` (saiz123)
- ✅ **Cliente específico:** `jo@gmail.com` (saiz123)
- ✅ **9 clientes adicionales** generados con factory
- ✅ **Total:** 1 administrador + 1 cliente específico + 9 clientes de prueba

### **2. CoordinatorSeeder**
- ✅ **Coordinador específico:** `mauricio@gmail.com` (saiz123)
- ✅ **9 coordinadores adicionales** generados con factory
- ✅ **Total:** 1 coordinador específico + 9 coordinadores de prueba

### **3. TechnicalSeeder**
- ✅ **Técnico específico:** `eyder@gmail.com` (saiz123)
- ✅ **9 técnicos adicionales** generados con factory
- ✅ **Total:** 1 técnico específico + 9 técnicos de prueba

### **4. MaintenanceSeeder**
- ✅ **10 mantenimientos de prueba** en estado `pending`
- ✅ **Asociados a dispositivos de clientes** existentes
- ✅ **Listos para asignación**

### **5. DeviceSeeder** (Existente)
- ✅ **Dispositivos de prueba** para los clientes

### **6. ContactSeeder** (Existente)
- ✅ **Contactos de prueba** para los clientes

## 🚀 Comandos de Ejecución

### **Ejecutar Todos los Seeders:**
```bash
php artisan db:seed
```

### **Ejecutar Seeders Específicos:**
```bash
# Solo usuarios específicos
php artisan db:seed --class=ClientSeeder
php artisan db:seed --class=CoordinatorSeeder
php artisan db:seed --class=TechnicalSeeder

# Solo mantenimientos
php artisan db:seed --class=MaintenanceSeeder
```

### **Refrescar Base de Datos:**
```bash
php artisan migrate:fresh --seed
```

## 🔐 Roles y Permisos

### **Administrador (`joinernix2@gmail.com`)**
- ✅ Acceso completo al sistema
- ✅ Puede asignar mantenimientos
- ✅ Puede gestionar todos los usuarios
- ✅ Puede ver todos los reportes

### **Coordinador (`mauricio@gmail.com`)**
- ✅ Puede asignar mantenimientos
- ✅ Puede ver técnicos disponibles
- ✅ Puede gestionar mantenimientos
- ❌ No puede gestionar usuarios

### **Técnico (`eyder@gmail.com`)**
- ✅ Puede ver sus mantenimientos asignados
- ✅ Puede actualizar estado de mantenimientos
- ❌ No puede asignar mantenimientos

### **Cliente (`jo@gmail.com`)**
- ✅ Puede ver sus mantenimientos
- ✅ Puede eliminar mantenimientos pendientes
- ❌ No puede asignar mantenimientos

## 📋 Datos Generados

### **Usuarios:**
- **1 Administrador** (específico)
- **1 Coordinador** (específico)
- **1 Técnico** (específico)
- **1 Cliente** (específico)
- **9 Coordinadores** (prueba)
- **9 Técnicos** (prueba)
- **9 Clientes** (prueba)
- **Total:** 32 usuarios

### **Mantenimientos:**
- **10 mantenimientos** en estado `pending`
- **Listos para asignación**
- **Asociados a dispositivos de clientes**

## 🧪 Testing del Sistema

### **Flujo de Asignación:**
1. **Login como Coordinador:** `mauricio@gmail.com` / `12345678`
2. **Ver mantenimientos pendientes:** `GET /api/maintenancesNotAssigned`
3. **Ver técnicos disponibles:** `GET /api/availableTechnicians`
4. **Asignar mantenimiento:** `PUT /api/assignMaintenance/{id}`

### **Flujo de Técnico:**
1. **Login como Técnico:** `eyder@gmail.com` / `87654321`
2. **Ver mantenimientos asignados**
3. **Actualizar estado de mantenimientos**

### **Flujo de Cliente:**
1. **Login como Cliente:** `jo@gmail.com` / `12345678-9`
2. **Ver sus mantenimientos**
3. **Eliminar mantenimientos pendientes**

## 🔧 Configuración de Desarrollo

### **Variables de Entorno:**
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### **Ejecutar en Orden:**
```bash
# 1. Limpiar base de datos
php artisan migrate:fresh

# 2. Ejecutar seeders
php artisan db:seed

# 3. Verificar datos
php artisan tinker
>>> User::count()
>>> Client::count()
>>> Technician::count()
>>> Coordinator::count()
>>> Maintenance::count()
```

## ✅ Verificación de Seeders

### **Comandos de Verificación:**
```bash
# Verificar usuarios creados
php artisan tinker
>>> User::whereIn('email', ['joinernix2@gmail.com', 'mauricio@gmail.com', 'eyder@gmail.com', 'jo@gmail.com'])->get(['name', 'email', 'role'])

# Verificar mantenimientos
>>> Maintenance::where('status', 'pending')->count()

# Verificar relaciones
>>> Client::with('user')->first()
>>> Technician::with('user')->first()
>>> Coordinator::with('user')->first()
```

## 🎉 Listo para Desarrollo

Con estos seeders tienes:
- ✅ **Usuarios específicos** para cada rol
- ✅ **Datos de prueba** realistas
- ✅ **Mantenimientos listos** para asignar
- ✅ **Sistema completo** para testing

¡El sistema está listo para desarrollo y testing con datos realistas!
