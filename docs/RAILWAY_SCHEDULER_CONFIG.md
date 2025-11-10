# 🚂 Configuración del Scheduler en Railway

## 📋 Resumen

Este documento explica cómo configurar el scheduler de Laravel en Railway para que el comando `maintenances:check-unconfirmed` se ejecute cada 5 minutos.

## ✅ Cambios Realizados

1. **Frecuencia actualizada**: El comando ahora se ejecuta cada 5 minutos en lugar de cada minuto
   - Archivo: `routes/console.php`
   - Cambio: `->everyMinute()` → `->everyFiveMinutes()`

2. **Script del scheduler**: Se creó un script para ejecutar el scheduler
   - Archivo: `scripts/scheduler.sh`

## 🔧 Configuración en Railway

### Opción 1: Usar un Servicio Separado (Recomendado)

Railway permite crear múltiples servicios en un mismo proyecto. Sigue estos pasos:

#### Paso 1: Crear un Nuevo Servicio

1. Ve a tu proyecto en Railway
2. Haz clic en **"New"** → **"Service"**
3. Selecciona **"Empty Service"** o **"GitHub Repo"** (si ya tienes el repo conectado)

#### Paso 2: Configurar el Servicio del Scheduler

1. En el nuevo servicio, ve a la pestaña **"Settings"**
2. Configura las siguientes opciones:

   **Build Command:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

   **Start Command:**
   ```bash
   bash scripts/scheduler.sh
   ```

   O alternativamente:
   ```bash
   sh -c "while true; do php artisan schedule:run --verbose --no-interaction && sleep 60; done"
   ```

#### Paso 3: Configurar Variables de Entorno

Asegúrate de que el servicio del scheduler tenga acceso a las mismas variables de entorno que tu aplicación principal:

- `APP_ENV`
- `APP_KEY`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `REDIS_HOST` (si usas Redis)
- `REDIS_PORT` (si usas Redis)
- Y cualquier otra variable que tu aplicación necesite

**Nota**: Railway permite compartir variables de entorno entre servicios del mismo proyecto.

### Opción 2: Usar Railway Cron Jobs (Si está disponible)

Si tu plan de Railway incluye cron jobs:

1. Ve a tu proyecto en Railway
2. Ve a **"Settings"** → **"Cron Jobs"**
3. Agrega un nuevo cron job:
   - **Schedule**: `*/5 * * * *` (cada 5 minutos)
   - **Command**: `php artisan maintenances:check-unconfirmed`

**Nota**: Esta opción puede no estar disponible en todos los planes de Railway.

### Opción 3: Usar un Worker en el Mismo Servicio

Si prefieres mantener todo en un solo servicio, puedes modificar el comando de inicio para ejecutar tanto el servidor web como el scheduler:

**Start Command:**
```bash
php artisan serve --host=0.0.0.0 --port=$PORT & bash scripts/scheduler.sh
```

**⚠️ Advertencia**: Esta opción no es recomendada para producción, ya que si el scheduler falla, puede afectar el servidor web.

## 🧪 Verificación

### Verificar que el Scheduler está Funcionando

1. Ve a los logs del servicio del scheduler en Railway
2. Deberías ver mensajes como:
   ```
   Running scheduled command: 'maintenances:check-unconfirmed'
   ```

### Probar el Comando Manualmente

Puedes ejecutar el comando manualmente desde Railway CLI o desde un servicio temporal:

```bash
php artisan maintenances:check-unconfirmed
```

## 📝 Notas Importantes

1. **Frecuencia del Scheduler**: El script `scheduler.sh` ejecuta `schedule:run` cada 60 segundos. Laravel internamente verifica qué tareas deben ejecutarse según su programación (cada 5 minutos en este caso).

2. **Sin Solapamiento**: El comando está configurado con `->withoutOverlapping()`, lo que significa que si una ejecución aún está corriendo cuando debería iniciarse la siguiente, se omitirá la nueva ejecución.

3. **Un Solo Servidor**: El comando está configurado con `->onOneServer()`, lo que requiere que uses Redis o Memcached como driver de cache. Esto asegura que el comando solo se ejecute en un servidor si tienes múltiples instancias.

4. **Logs**: Los logs del scheduler aparecerán en:
   - Logs del servicio en Railway
   - `storage/logs/laravel.log` (si tienes acceso al sistema de archivos)

## 🔍 Troubleshooting

### El scheduler no se ejecuta

1. Verifica que el servicio del scheduler esté corriendo
2. Revisa los logs del servicio
3. Verifica que todas las variables de entorno estén configuradas correctamente
4. Asegúrate de que la base de datos y Redis (si lo usas) sean accesibles desde el servicio

### El comando se ejecuta pero no hace nada

1. Verifica que haya mantenimientos sin confirmar que requieran notificación
2. Revisa los logs de Laravel: `storage/logs/laravel.log`
3. Verifica que los coordinadores existan en la base de datos

### Error de conexión a la base de datos

1. Verifica que `DB_HOST` apunte al servicio de base de datos correcto
2. En Railway, el host de la base de datos suele ser algo como `containers-us-west-xxx.railway.app`
3. Asegúrate de que el servicio del scheduler tenga acceso a la misma base de datos que tu aplicación principal

## 📚 Referencias

- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)
- [Railway Documentation](https://docs.railway.app/)
- [Railway Multiple Services](https://docs.railway.app/deploy/multi-service)

