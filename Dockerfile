# ==============================================================================
# ÉTAPE 1 : Installation des dépendances PHP (Composer)
# ==============================================================================
FROM php:8.2-cli AS composer-builder

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql intl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier uniquement les fichiers composer
COPY composer.json composer.lock ./

# Installer les dépendances PHP de production (crée le dossier vendor/)
RUN composer install --no-dev --no-scripts --optimize-autoloader

# ==============================================================================
# ÉTAPE 2 : Compilation des assets (Node.js)
# ==============================================================================
FROM node:18-alpine AS node-builder

WORKDIR /app

# Copier uniquement les configurations de packages Node
COPY package*.json ./

# Installer les dépendances Node
RUN npm ci

# Copier l'intégralité du code source
COPY . .

# Copier le dossier vendor/ depuis l'étape composer-builder car Webpack Encore en a besoin pour résoudre Stimulus / Symfony UX
COPY --from=composer-builder /app/vendor ./vendor

# Compiler les assets de production (Webpack Encore)
RUN npm run build

# ==============================================================================
# ÉTAPE 3 : Serveur de production (PHP / Apache)
# ==============================================================================
FROM php:8.2-apache

# Configurer Apache pour pointer vers le répertoire /public de Symfony
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Activer le module de réécriture d'URL Apache (mod_rewrite)
RUN a2enmod rewrite

# Installer les dépendances système requises et les librairies de développement
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql pdo_pgsql intl zip gd opcache \
    && rm -rf /var/lib/apt/lists/*

# Configurer le php.ini pour la production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configurer OPcache pour de meilleures performances Symfony en production
RUN echo "opcache.memory_consumption=256" >> "$PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini" && \
    echo "opcache.max_accelerated_files=20000" >> "$PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini" && \
    echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini" && \
    echo "opcache.interned_strings_buffer=16" >> "$PHP_INI_DIR/conf.d/docker-php-ext-opcache.ini"

WORKDIR /var/www/html

# Récupérer Composer depuis l'image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier l'intégralité du projet
COPY . .

# Copier le dossier vendor/ pré-installé et optimisé
COPY --from=composer-builder /app/vendor ./vendor

# Copier les assets compilés depuis l'étape Node.js
COPY --from=node-builder /app/public/build ./public/build

# Configurer les variables d'environnement pour la production
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Créer les dossiers de cache, logs et uploads et donner les droits d'écriture à Apache
RUN mkdir -p var/cache var/log public/uploads && \
    chown -R www-data:www-data var public/uploads

# Chauffer le cache de production de Symfony (sans base de données active en build-time)
RUN APP_ENV=prod DATABASE_URL=sqlite:///:memory: php bin/console cache:clear

# Exposer le port par défaut d'Apache
EXPOSE 80

CMD ["apache2-foreground"]