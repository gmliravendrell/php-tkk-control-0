FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cambiar DocumentRoot
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY public /var/www/html/public

EXPOSE 80