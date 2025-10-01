# 🔧 Configuración de Colas para Notificaciones Push

## 📋 **ESTADO ACTUAL**

✅ **Colas configuradas:** Database driver  
✅ **Tabla jobs:** Existe  
✅ **Notificaciones:** Implementadas con `ShouldQueue`  
✅ **Sistema:** Listo para procesar colas

---

## 🚀 **CONFIGURACIÓN PASO A PASO**

### **1. 📊 Verificar Configuración Actual**

```bash
# Verificar configuración de colas
php artisan config:show queue

# Verificar que la tabla jobs existe
php artisan migrate:status
```

### **2. 🔧 Configurar Variables de Entorno**

Crea o edita tu archivo `.env`:

```env
# Configuración de Colas
QUEUE_CONNECTION=database

# Configuración de Base de Datos (ya configurada)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carini_service
DB_USERNAME=root
DB_PASSWORD=

# Configuración de Mail (para notificaciones por email)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME="Carini Service"
```

### **3. ⚡ Iniciar Worker de Colas**

#### **Opción A: Desarrollo Local**
```bash
# Iniciar worker de colas
php artisan queue:work

# O con más opciones
php artisan queue:work --verbose --tries=3 --timeout=90
```

#### **Opción B: Desarrollo con Supervisor (Recomendado)**
```bash
# Instalar supervisor
sudo apt-get install supervisor

# Crear configuración
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

**Contenido del archivo:**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /ruta/a/tu/proyecto/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/ruta/a/tu/proyecto/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Recargar supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### **4. 🧪 Probar Sistema de Colas**

#### **A. Probar con Comando de Testing**
```bash
# Probar notificaciones (se procesarán en cola)
php artisan notifications:test --type=assigned
```

#### **B. Verificar Colas en Base de Datos**
```sql
-- Ver jobs pendientes
SELECT * FROM jobs;

-- Ver jobs fallidos
SELECT * FROM failed_jobs;
```

#### **C. Monitorear Logs**
```bash
# Ver logs de worker
tail -f storage/logs/laravel.log

# Ver logs específicos de colas
tail -f storage/logs/worker.log
```

### **5. 🔍 Comandos Útiles de Colas**

```bash
# Ver jobs pendientes
php artisan queue:monitor

# Procesar jobs pendientes
php artisan queue:work --once

# Limpiar jobs fallidos
php artisan queue:flush

# Reintentar jobs fallidos
php artisan queue:retry all

# Ver estadísticas
php artisan queue:work --verbose
```

### **6. 📊 Monitoreo de Colas**

#### **A. Dashboard de Colas (Opcional)**
```bash
# Instalar Laravel Horizon (para Redis)
composer require laravel/horizon

# O usar Telescope (ya instalado)
php artisan telescope:install
```

#### **B. Comando Personalizado para Monitoreo**
```bash
# Crear comando de monitoreo
php artisan make:command MonitorQueues
```

### **7. 🚨 Solución de Problemas**

#### **Problema: Jobs no se procesan**
```bash
# Verificar que el worker esté corriendo
ps aux | grep "queue:work"

# Reiniciar worker
php artisan queue:restart
```

#### **Problema: Jobs fallan**
```bash
# Ver jobs fallidos
php artisan queue:failed

# Reintentar job específico
php artisan queue:retry [id]

# Ver detalles del error
php artisan queue:failed [id]
```

#### **Problema: Notificaciones no llegan**
```bash
# Verificar logs
tail -f storage/logs/laravel.log | grep "notification"

# Verificar configuración de mail
php artisan config:show mail
```

### **8. 🎯 Configuración para Producción**

#### **A. Variables de Entorno de Producción**
```env
# Producción
QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=produccion@tudominio.com
MAIL_PASSWORD=password_seguro
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=produccion@tudominio.com
MAIL_FROM_NAME="Carini Service"
```

#### **B. Supervisor para Producción**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/carini_service/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/carini_service/storage/logs/worker.log
stopwaitsecs=3600
```

### **9. 📈 Optimización de Rendimiento**

#### **A. Configuración Avanzada**
```bash
# Worker con múltiples procesos
php artisan queue:work --processes=4

# Worker con timeout específico
php artisan queue:work --timeout=300

# Worker con memoria limitada
php artisan queue:work --memory=512
```

#### **B. Monitoreo de Recursos**
```bash
# Ver uso de memoria
php artisan queue:monitor --memory=512

# Ver tiempo de procesamiento
php artisan queue:monitor --timeout=300
```

---

## ✅ **CHECKLIST DE CONFIGURACIÓN**

- [ ] ✅ Variables de entorno configuradas
- [ ] ✅ Tabla jobs creada
- [ ] ✅ Worker de colas iniciado
- [ ] ✅ Notificaciones implementadas con ShouldQueue
- [ ] ✅ Mail configurado para notificaciones
- [ ] ✅ Logs funcionando
- [ ] ✅ Testing completado
- [ ] ✅ Monitoreo configurado

---

## 🎯 **COMANDOS RÁPIDOS**

```bash
# Iniciar worker
php artisan queue:work

# Probar notificaciones
php artisan notifications:test

# Ver logs
tail -f storage/logs/laravel.log

# Reiniciar worker
php artisan queue:restart

# Limpiar jobs fallidos
php artisan queue:flush
```

---

**🎉 ¡Sistema de colas configurado y listo para producción!**
