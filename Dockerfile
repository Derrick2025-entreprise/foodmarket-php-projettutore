# ─────────────────────────────────────────────────────────────
# Dockerfile — FoodMarket PHP (CodeIgniter 4)
# Utilise webdevops/php-apache:8.1 qui inclut déjà :
#   - PHP 8.1 + Apache
#   - Extensions : intl, mbstring, mysqli, pdo_mysql, zip, etc.
#   - Composer préinstallé
# Avantage : aucun apt-get nécessaire → build sans accès réseau Debian
# ─────────────────────────────────────────────────────────────

FROM webdevops/php-apache:8.1

# Dossier de travail
WORKDIR /app

# Copie du code source
COPY . .

# Installation des dépendances Composer
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Créer les dossiers CI4 avec les bonnes permissions
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data writable \
    && chmod -R 755 writable

# Copier .env si absent
RUN cp -n .env.example .env || true

# Variable d'environnement pour Apache — pointe vers public/
ENV WEB_DOCUMENT_ROOT=/app/public

EXPOSE 80
