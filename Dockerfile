# ─────────────────────────────────────────────────────────────
# Dockerfile — FoodMarket PHP (CodeIgniter 4)
# PHP 8.2 + Apache, port 80
# ─────────────────────────────────────────────────────────────

FROM php:8.2-apache

# Extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install intl mysqli pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# mod_rewrite requis par CodeIgniter 4
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs

# Permissions dossier writable
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data writable \
    && chmod -R 755 writable

# Copier .env si absent
RUN cp -n .env.example .env || true

# Apache pointe vers public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
