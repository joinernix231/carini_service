@echo off
REM Script de inicio rápido para Carini Service con Docker (Windows)
REM Este script automatiza el proceso de inicio del proyecto

echo 🚀 Iniciando Carini Service con Docker...
echo ==========================================

REM Verificar si Docker está ejecutándose
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Error: Docker no está ejecutándose. Por favor, inicia Docker Desktop.
    pause
    exit /b 1
)

REM Verificar si docker-compose está disponible
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Error: docker-compose no está instalado.
    pause
    exit /b 1
)

REM Copiar archivo de entorno si no existe
if not exist .env (
    echo 📝 Copiando archivo de entorno...
    copy env.example .env
    echo ✅ Archivo .env creado. Por favor, revisa y ajusta las configuraciones según sea necesario.
)

REM Construir las imágenes
echo 🔨 Construyendo imágenes de Docker...
docker-compose build

REM Iniciar los servicios
echo 🚀 Iniciando servicios...
docker-compose up -d

REM Esperar a que los servicios estén listos
echo ⏳ Esperando a que los servicios estén listos...
timeout /t 10 /nobreak >nul

REM Ejecutar migraciones
echo 🗄️ Ejecutando migraciones...
docker-compose exec -T app php artisan migrate --force

REM Generar claves si es necesario
echo 🔑 Verificando claves de aplicación...
docker-compose exec -T app php artisan key:generate --force
docker-compose exec -T app php artisan jwt:secret --force

REM Limpiar cache
echo 🧹 Limpiando cache...
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear

echo.
echo ✅ ¡Carini Service está listo!
echo ==========================================
echo 🌐 Aplicación: http://localhost
echo 📊 Telescope: http://localhost/telescope
echo 🗄️ phpMyAdmin: http://localhost:8080
echo 📧 Mailhog: http://localhost:8025
echo 🔴 Redis Commander: http://localhost:8081
echo.
echo 📋 Comandos útiles:
echo   Ver logs: docker-compose logs -f
echo   Detener: docker-compose down
echo   Reiniciar: docker-compose restart
echo.
echo 🎉 ¡Disfruta desarrollando!
pause
