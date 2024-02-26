# Utilizamos una imagen oficial de PHP 8.3 con Apache como imagen base
FROM php:8.3-apache

# Instalamos los paquetes necesarios
RUN apt-get update && apt-get install -y \
    curl \
    vim \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configuramos los módulos de PHP
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
# Establecemos la raíz web de Apache en el directorio público del proyecto
RUN sed -ri -e 's!/var/www/html!$ {APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!$ {APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalamos y habilitamos Xdebug
RUN pecl install xdebug \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable xdebug

# Cambiamos el ID del usuario www-data a 1000
RUN usermod -u 1000 www-data

# Copiamos nuestra aplicación a la carpeta de trabajo del contenedor
COPY . /var/www/html/

# Establecemos la carpeta de trabajo
WORKDIR /var/www/html/

# Copiamos la configuración de Xdebug
COPY ./docker/xdebug/xdebug.ini $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini

# Exponemos el puerto 80 para el tráfico HTTP
EXPOSE 80

# Iniciamos el servidor Apache en primer plano
CMD ["apache2-foreground"]
