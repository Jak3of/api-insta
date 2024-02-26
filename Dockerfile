# Utilizamos una imagen oficial de PHP 8.3 con Apache como imagen base
FROM php:8.3-apache

# Actualizamos e instalamos dependencias
RUN apt-get update && apt-get install -y \
    git \
    unzip

# Instalamos Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalamos las extensiones necesarias de PHP
RUN docker-php-ext-install pdo_mysql

# Copiamos los archivos de la aplicación al contenedor
COPY ./src /var/www/html/

# Instalamos las dependencias de la aplicación usando Composer
RUN composer install --no-interaction --optimize-autoloader

# Habilitamos el módulo rewrite de Apache
RUN a2enmod rewrite

# Cambiamos el usuario predeterminado a www-data
RUN usermod -u 1000 www-data

# Establecemos el directorio de trabajo
WORKDIR /var/www/html

# Configuramos el nombre del servidor
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Cambiamos los permisos del directorio html
RUN chmod -R 755 /var/www/html/

# Iniciamos el servidor Apache en primer plano
CMD ["apache2-foreground"]
