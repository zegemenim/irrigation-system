# Aşama 1: Frontend derleyici (Vite)
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
# Tüm bağımlılıkları (devDependencies dahil) yükle
RUN npm install --include=dev
COPY . .
RUN npm run build

# Aşama 2: PHP 8.3 Apache Üretim Ortamı
FROM php:8.3-apache

# Gerekli paketlerin kurulumu
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache DocumentRoot ayarını Laravel public klasörüne yönlendir
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Composer kurulumu
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Proje dosyalarını kopyala
COPY . .

# Frontend build dosyalarını Aşama 1'den kopyala (Vite hatasını kesin çözer)
COPY --from=frontend-builder /app/public/build ./public/build

# PHP bağımlılıklarını kur
RUN composer install --no-interaction --optimize-autoloader

# İzinleri ayarla
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Başlangıç betiği oluştur (Otomatik .env, key, migrate işlemleri)
RUN echo '#!/bin/bash\n\
if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi\n\
php artisan key:generate --no-interaction --force || true\n\
# Veritabanı dosyası yoksa oluştur\n\
mkdir -p database\n\
touch database/database.sqlite\n\
chown www-data:www-data database/database.sqlite\n\
php artisan migrate --force\n\
php artisan optimize:clear\n\
exec apache2-foreground\n\
' > /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
