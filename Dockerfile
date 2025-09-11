# ----------------------
# 1️⃣ Stage de build
# ----------------------
FROM composer:2.6 as builder

WORKDIR /app

# Copiar composer.json y composer.lock primero (para cachear dependencias)
COPY laravel/composer.json laravel/composer.lock ./

# Instalar dependencias de PHP/Laravel
RUN composer install --no-dev --optimize-autoloader

# Copiar todo el código Laravel
COPY laravel .

# Generar cache de config, rutas y views
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

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

# Ajustar permisos de storage y bootstrap/cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar DocumentRoot a /public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
