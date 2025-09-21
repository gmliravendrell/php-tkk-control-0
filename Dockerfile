# ----------------------
# 1️⃣ Stage de build
# ----------------------
FROM composer:2.6 as builder

WORKDIR /app

# Copiar composer.json y composer.lock primero (para cachear dependencias)
COPY laravel/ ./

# Instalar dependencias de PHP/Laravel
RUN composer install --no-dev --optimize-autoloader

# Copiar todo el código Laravel
COPY laravel .
# ----------------------
# 2️⃣ Stage de runtime
# ----------------------
FROM php:8.2-apache

# Instalar extensiones necesarias para runtime
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql mbstring exif bcmath gd

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Crear directorio de la app
WORKDIR /var/www/html

# Copiar la app desde el stage builder
COPY --from=builder /app /var/www/html
RUN mkdir -p /var/www/html/storage/framework/{sessions,views,cache} \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar DocumentRoot a /public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
