FROM php:8.3-cli

# Instalar dependências
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pcntl

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar todo o projeto
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permissões adequadas
RUN chown -R www-data:www-data storage bootstrap/cache

# Comando do Worker do Laravel
CMD ["php", "artisan", "queue:work", "--tries=3", "--memory=512"]
