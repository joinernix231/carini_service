# 🔭 Solución para Migraciones de Telescope

## Problema

Cuando ejecutas `php artisan migrate:fresh`, todas las tablas se eliminan, incluyendo las de Telescope. Como las migraciones de Telescope están en el paquete `vendor` y no en `database/migrations`, no se ejecutan automáticamente.

## Solución

### Opción 1: Usar el comando personalizado (Recomendado)

He creado un comando personalizado que ejecuta `migrate:fresh` y luego las migraciones de Telescope automáticamente:

```bash
# Con seeders
php artisan migrate:fresh-telescope --seed

# Sin seeders
php artisan migrate:fresh-telescope
```

### Opción 2: Ejecutar manualmente después de migrate:fresh

Si prefieres usar el comando estándar, ejecuta esto después de `migrate:fresh`:

```bash
php artisan migrate:fresh --seed
php artisan migrate --path=vendor/laravel/telescope/database/migrations
```

### Opción 3: Publicar las migraciones de Telescope

Si quieres tener las migraciones de Telescope en tu proyecto:

```bash
php artisan vendor:publish --tag=telescope-migrations
```

Esto copiará las migraciones a `database/migrations` y se ejecutarán automáticamente con `migrate:fresh`.

## Verificación

Después de ejecutar las migraciones, verifica que Telescope funcione:

1. Visita `/telescope` en tu aplicación
2. O ejecuta: `php artisan telescope:prune` para verificar que las tablas existen

## Nota

El comando `migrate:fresh-telescope` está disponible en `app/Console/Commands/MigrateFreshWithTelescope.php` y se registra automáticamente en Laravel.

