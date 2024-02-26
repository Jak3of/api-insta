# Utilizamos una imagen oficial de PHP 8.3 con Apache como imagen base
FROM php:8.3-apache

RUN apt-get update && apt-get install -y

 RUN apt-get update && apt-get install -y --no-install-recommends 

RUN docker-php-ext-install \    
        pdo_mysql
        
RUN pecl install xdebug \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable xdebug

 RUN usermod -u 1000 www-data

COPY ./docker/xdebug/xdebug.ini $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/html

# Exponemos el puerto 80 para el tráfico HTTP
EXPOSE 85

# Iniciamos el servidor Apache en primer plano
CMD ["apache2-foreground"]

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
