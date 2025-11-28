FROM php:8.3-fpm as php

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache


# ---- NGINX + PHP ----
FROM nginx:1.25

# Copia Laravel
COPY --from=php /var/www/html /var/www/html

# Copia config do Nginx
COPY docker/php/nginx.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html

EXPOSE 8080

CMD ["nginx", "-g", "daemon off;"]
