# ─────────────────────────────────────────────────────────────
# Dockerfile — FoodMarket PHP (CodeIgniter 4)
# Image officielle PHP 8.2 + Apache
# ─────────────────────────────────────────────────────────────

FROM php:8.2-apache

# Installation des extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install intl mysqli pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Activer mod_rewrite pour CodeIgniter
RUN a2enmod rewrite

# Dossier de travail
WORKDIR /var/www/html

# Copie du code source
COPY . .

# Installation des dépendances Composer (prod uniquement, sans scripts post-install)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs

# Créer les dossiers CI4 avec les bonnes permissions
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data writable \
    && chmod -R 755 writable

# Copier .env si absent
RUN cp -n .env.example .env || true

# Configuration Apache — pointer vers public/
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
