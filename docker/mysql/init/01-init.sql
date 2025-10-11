-- Script de inicialización para la base de datos Carini Service
-- Este script se ejecuta automáticamente cuando se crea el contenedor MySQL

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS carini_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE carini_service;

-- Crear usuario si no existe (opcional, ya se crea en docker-compose)
-- CREATE USER IF NOT EXISTS 'carini_user'@'%' IDENTIFIED BY 'carini_password';
-- GRANT ALL PRIVILEGES ON carini_service.* TO 'carini_user'@'%';
-- FLUSH PRIVILEGES;

-- Configuraciones adicionales para optimización
SET GLOBAL innodb_buffer_pool_size = 256M;
SET GLOBAL max_connections = 200;
SET GLOBAL query_cache_size = 32M;
SET GLOBAL query_cache_type = 1;
