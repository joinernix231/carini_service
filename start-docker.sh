#!/bin/bash

# Script de inicio rápido para Carini Service con Docker
# Este script automatiza el proceso de inicio del proyecto

echo "🚀 Iniciando Carini Service con Docker..."
echo "=========================================="

# Verificar si Docker está ejecutándose
if ! docker info > /dev/null 2>&1; then
    echo "❌ Error: Docker no está ejecutándose. Por favor, inicia Docker Desktop."
    exit 1
fi

# Verificar si docker-compose está disponible
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: docker-compose no está instalado."
    exit 1
fi

# Copiar archivo de entorno si no existe
if [ ! -f .env ]; then
    echo "📝 Copiando archivo de entorno..."
    cp env.example .env
    echo "✅ Archivo .env creado. Por favor, revisa y ajusta las configuraciones según sea necesario."
fi

# Construir las imágenes
echo "🔨 Construyendo imágenes de Docker..."
docker-compose build

# Iniciar los servicios
echo "🚀 Iniciando servicios..."
docker-compose up -d

# Esperar a que los servicios estén listos
echo "⏳ Esperando a que los servicios estén listos..."
sleep 10

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
docker-compose exec -T app php artisan migrate --force

# Generar claves si es necesario
echo "🔑 Verificando claves de aplicación..."
docker-compose exec -T app php artisan key:generate --force
docker-compose exec -T app php artisan jwt:secret --force

# Limpiar cache
echo "🧹 Limpiando cache..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear

echo ""
echo "✅ ¡Carini Service está listo!"
echo "=========================================="
echo "🌐 Aplicación: http://localhost"
echo "📊 Telescope: http://localhost/telescope"
echo "🗄️ phpMyAdmin: http://localhost:8080"
echo "📧 Mailhog: http://localhost:8025"
echo "🔴 Redis Commander: http://localhost:8081"
echo ""
echo "📋 Comandos útiles:"
echo "  Ver logs: docker-compose logs -f"
echo "  Detener: docker-compose down"
echo "  Reiniciar: docker-compose restart"
echo ""
echo "🎉 ¡Disfruta desarrollando!"
