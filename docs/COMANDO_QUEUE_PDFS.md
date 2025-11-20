# Comando para Procesar Queue de PDFs

## 🚀 Comando Principal

### ⚠️ IMPORTANTE: Diferencia entre Queues

- **Notificaciones**: Van a la queue `default` (no especifican queue)
- **PDFs**: Van a la queue `PDFQueue_local` (especificada explícitamente)

Si ejecutas `php artisan queue:work` sin especificar queue, **solo procesa `default`** (notificaciones), **NO procesa los PDFs**.

### Opción 1: Procesar Solo PDFs

```bash
php artisan queue:work --queue=PDFQueue_local
```

### Opción 2: Procesar Notificaciones Y PDFs (Recomendado)

```bash
php artisan queue:work --queue=default,PDFQueue_local
```

Esto procesa ambas queues: primero `default` (notificaciones) y luego `PDFQueue_local` (PDFs).

## 📋 Opciones Adicionales

### Comando Básico (Recomendado para Desarrollo)
```bash
php artisan queue:work --queue=PDFQueue_local
```

### Comando con Opciones Avanzadas (Recomendado para Producción)
```bash
php artisan queue:work --queue=PDFQueue_local --tries=3 --timeout=300 --sleep=3
```

**Explicación de opciones:**
- `--queue=PDFQueue_local`: Procesa solo la queue de PDFs
- `--tries=3`: Reintenta hasta 3 veces si falla
- `--timeout=300`: Timeout de 5 minutos por job
- `--sleep=3`: Espera 3 segundos entre jobs cuando no hay trabajo

### Comando con Verbose (Para Debug)
```bash
php artisan queue:work --queue=PDFQueue_local --verbose
```

### Procesar Múltiples Queues (Notificaciones + PDFs)
Para procesar tanto notificaciones como PDFs:
```bash
php artisan queue:work --queue=default,PDFQueue_local
```

**Orden importante**: Se procesan en el orden especificado. Primero `default` (notificaciones), luego `PDFQueue_local` (PDFs).

## 🔄 Para Desarrollo Local

### Opción 1: Terminal Separada
Abre una terminal nueva y ejecuta:
```bash
cd /ruta/a/tu/proyecto
php artisan queue:work --queue=PDFQueue_local
```

### Opción 2: En Background (Linux/Mac)
```bash
php artisan queue:work --queue=PDFQueue_local > storage/logs/queue.log 2>&1 &
```

### Opción 3: Con Screen (Linux/Mac)
```bash
screen -S queue-pdfs
php artisan queue:work --queue=PDFQueue_local
# Presiona Ctrl+A luego D para detach
```

## 🚂 Para Railway (Producción)

### Opción 1: Servicio Separado para PDFs (Recomendado si quieres separar)

1. Crea un nuevo servicio en Railway
2. Configura el **Start Command**:
```bash
php artisan queue:work --queue=PDFQueue_local --tries=3 --timeout=300 --sleep=3
```

### Opción 1B: Servicio Único para Ambas Queues (Más Eficiente)

1. Crea un nuevo servicio en Railway
2. Configura el **Start Command**:
```bash
php artisan queue:work --queue=default,PDFQueue_local --tries=3 --timeout=300 --sleep=3
```

Esto procesa tanto notificaciones como PDFs en un solo worker.

3. Asegúrate de que tenga las mismas variables de entorno que el servicio principal

### Opción 2: En el Mismo Servicio (No Recomendado)

Modifica el `railway.json`:
```json
{
    "start": [
        "php artisan optimize:clear",
        "php artisan serve --host=0.0.0.0 --port=8500 &",
        "php artisan queue:work --queue=PDFQueue_local --tries=3 --timeout=300"
    ]
}
```

⚠️ **Nota**: Esta opción no es recomendada porque si el worker falla, puede afectar el servidor web.

## 🧪 Verificar que Funciona

### 1. Ver Jobs Pendientes
```bash
php artisan queue:monitor
```

### 2. Ver Jobs en la Base de Datos
```sql
SELECT * FROM jobs WHERE queue = 'PDFQueue_local';
```

### 3. Ver Logs
```bash
tail -f storage/logs/laravel.log | grep "PDF"
```

### 4. Probar Generando un PDF
1. Completa un mantenimiento desde el frontend
2. Verifica que el job se encola:
```sql
SELECT * FROM jobs WHERE queue = 'PDFQueue_local' ORDER BY created_at DESC LIMIT 1;
```
3. El worker debería procesarlo automáticamente
4. Verifica que el PDF se generó consultando el mantenimiento

## 🔍 Comandos Útiles

### Reiniciar Worker
```bash
php artisan queue:restart
```

### Ver Jobs Fallidos
```bash
php artisan queue:failed
```

### Reintentar Jobs Fallidos
```bash
php artisan queue:retry all
```

### Limpiar Jobs Fallidos
```bash
php artisan queue:flush
```

### Procesar un Solo Job
```bash
php artisan queue:work --queue=PDFQueue_local --once
```

## ⚠️ Solución de Problemas

### El Worker No Procesa Jobs

1. **Verificar que el worker esté corriendo:**
```bash
ps aux | grep "queue:work"
```

2. **Verificar que hay jobs en la queue:**
```sql
SELECT COUNT(*) FROM jobs WHERE queue = 'PDFQueue_local';
```

3. **Reiniciar el worker:**
```bash
php artisan queue:restart
php artisan queue:work --queue=PDFQueue_local
```

### Jobs Falla

1. **Ver jobs fallidos:**
```bash
php artisan queue:failed
```

2. **Ver detalles del error:**
```bash
php artisan queue:failed [id]
```

3. **Reintentar:**
```bash
php artisan queue:retry [id]
```

### PDFs No Se Generan

1. **Verificar logs:**
```bash
tail -f storage/logs/laravel.log | grep -i "pdf\|GenerateMaintenancePDF"
```

2. **Verificar que el reporte existe:**
```sql
SELECT * FROM maintenance_service_reports;
```

3. **Verificar que el job se encoló:**
```sql
SELECT * FROM jobs WHERE queue = 'PDFQueue_local' ORDER BY created_at DESC LIMIT 5;
```

## 📝 Notas Importantes

1. **Queue Connection**: El sistema usa `database` como driver de queue (configurado en `.env`)

2. **Queue Name**: Los PDFs se encolan en `PDFQueue_local` (definido en `GenerateMaintenancePDFListener`)

3. **Procesamiento Asíncrono**: Los PDFs se generan de forma asíncrona, por lo que el worker debe estar corriendo para procesarlos

4. **Timeout**: Los PDFs pueden tardar varios segundos en generarse, especialmente si hay múltiples dispositivos. Asegúrate de tener un timeout adecuado (mínimo 300 segundos)

5. **Memoria**: Los PDFs pueden consumir bastante memoria. Considera usar `--memory=512` si tienes problemas

## ✅ Checklist

- [ ] Worker corriendo: `php artisan queue:work --queue=PDFQueue_local`
- [ ] Queue connection configurada: `QUEUE_CONNECTION=database` en `.env`
- [ ] Tabla `jobs` existe en la base de datos
- [ ] Logs funcionando: `storage/logs/laravel.log`
- [ ] Probar completando un mantenimiento y verificando que se genera el PDF

