# Docker Setup para Carini Service

Este directorio contiene toda la configuración necesaria para ejecutar el proyecto Carini Service usando Docker.

## Estructura de archivos

```
docker/
├── app/
│   ├── Dockerfile          # Imagen de PHP 8.2 con Laravel
│   └── php.ini            # Configuración personalizada de PHP
├── nginx/
│   ├── Dockerfile         # Imagen de Nginx
│   └── nginx.conf         # Configuración de Nginx
├── mysql/
│   └── init/
│       └── 01-init.sql    # Script de inicialización de MySQL
└── scripts/
    └── init.sh            # Script de inicialización de la aplicación
```

## Servicios incluidos

- **app**: Aplicación Laravel con PHP 8.2-FPM
- **nginx**: Servidor web Nginx
- **mysql**: Base de datos MySQL 8.0
- **redis**: Cache y colas con Redis
- **queue**: Worker de colas de Laravel
- **scheduler**: Scheduler de tareas de Laravel

## Comandos útiles

### Iniciar todos los servicios
```bash
docker-compose up -d
```

### Ver logs de todos los servicios
```bash
docker-compose logs -f
```

### Ver logs de un servicio específico
```bash
docker-compose logs -f app
docker-compose logs -f mysql
docker-compose logs -f redis
```

### Ejecutar comandos en el contenedor de la aplicación
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app composer install
```

### Reiniciar un servicio específico
```bash
docker-compose restart app
docker-compose restart queue
```

### Detener todos los servicios
```bash
docker-compose down
```

### Detener y eliminar volúmenes (CUIDADO: elimina datos)
```bash
docker-compose down -v
```

## Configuración inicial

1. Copia el archivo de entorno:
```bash
cp env.example .env
```

2. Inicia los servicios:
```bash
docker-compose up -d
```

3. Ejecuta las migraciones:
```bash
docker-compose exec app php artisan migrate
```

4. Ejecuta los seeders (opcional):
```bash
docker-compose exec app php artisan db:seed
```

## Puertos expuestos

- **80**: Nginx (HTTP)
- **443**: Nginx (HTTPS)
- **3306**: MySQL
- **6379**: Redis

## URLs de acceso

- **Aplicación**: http://localhost
- **Telescope**: http://localhost/telescope
- **API**: http://localhost/api

## Troubleshooting

### Problemas de permisos
```bash
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache
```

### Limpiar cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

### Reconstruir imágenes
```bash
docker-compose build --no-cache
docker-compose up -d
```

### Ver estado de los contenedores
```bash
docker-compose ps
```

### Acceder al contenedor de la aplicación
```bash
docker-compose exec app bash
```

### Acceder a MySQL
```bash
docker-compose exec mysql mysql -u carini_user -p carini_service
```

### Acceder a Redis
```bash
docker-compose exec redis redis-cli
```
