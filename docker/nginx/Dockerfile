FROM nginx:alpine

# Copiar configuración personalizada de Nginx
COPY docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Crear directorio para logs
RUN mkdir -p /var/log/nginx

# Exponer puertos 80 y 443
EXPOSE 80 443

# Comando por defecto
CMD ["nginx", "-g", "daemon off;"]
