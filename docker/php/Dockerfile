
FROM php:8.3-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql bcmath gd mbstring zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Criar diretório da aplicação
WORKDIR /var/www/html

# Copiar arquivos do projeto
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Ajustar permissões requeridas pelo Laravel
RUN mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expor porta usada pela aplicação
EXPOSE 10000

# ===============================
# 2) CMD: Iniciar Laravel
# ===============================
CMD php artisan serve --host=0.0.0.0 --port=10000
