# Usa una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Copia todo tu código al directorio raíz de Apache
COPY . /var/www/html/

# Habilita mod_rewrite (opcional, útil si usas .htaccess)
RUN a2enmod rewrite

# Cambia los permisos (opcional)
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto por defecto de Apache
EXPOSE 80
