#!/bin/bash

# Script de inicialización para el proyecto Carini Service
# Este script se ejecuta cuando se inicia el contenedor de la aplicación

echo "🚀 Iniciando configuración de Carini Service..."

# Esperar a que MySQL esté listo
echo "⏳ Esperando a que MySQL esté disponible..."
while ! mysqladmin ping -h"mysql" -u"carini_user" -p"carini_password" --silent; do
    echo "Esperando MySQL..."
    sleep 2
done

echo "✅ MySQL está disponible"

# Copiar archivo de entorno si no existe
if [ ! -f .env ]; then
    echo "📝 Copiando archivo de entorno..."
    cp env.example .env
fi

# Generar clave de aplicación si no existe
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    echo "🔑 Generando clave de aplicación..."
    php artisan key:generate --force
fi

# Generar clave JWT si no existe
if ! grep -q "JWT_SECRET=" .env || grep -q "JWT_SECRET=$" .env; then
    echo "🔐 Generando clave JWT..."
    php artisan jwt:secret --force
fi

# Instalar dependencias de Composer
echo "📦 Instalando dependencias de Composer..."
composer install --no-dev --optimize-autoloader

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

# Ejecutar seeders (solo en desarrollo)
if [ "$APP_ENV" = "local" ]; then
    echo "🌱 Ejecutando seeders..."
    php artisan db:seed --force
fi

# Limpiar y optimizar cache
echo "🧹 Limpiando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizando para producción..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Crear directorios necesarios
echo "📁 Creando directorios necesarios..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Establecer permisos
echo "🔒 Estableciendo permisos..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

echo "✅ Configuración completada exitosamente!"
echo "🌐 La aplicación está disponible en: http://localhost"
echo "📊 Telescope está disponible en: http://localhost/telescope"
