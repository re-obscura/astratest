# ============================================
# Dockerfile для PHP-FPM (Laravel)
# ============================================
#
# Базовый образ: php:8.3-fpm (без Apache, только FPM)
# Устанавливаем расширения, необходимые для Laravel:
#  - pdo_mysql: работа с MySQL
#  - mbstring: многобайтовые строки (UTF-8)
#  - bcmath: математика для точных вычислений
#  - zip: работа с zip-архивами (Composer нуждается)
# Устанавливаем Composer для управления PHP-зависимостями.
# Устанавливаем Node.js для сборки фронтенда (Vite + Vue).

FROM php:8.3-fpm

# Системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Установить Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установить Node.js 20 (LTS) для сборки фронтенда
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www

# Копируем файлы проекта
COPY . /var/www

# Устанавливаем PHP-зависимости
RUN composer install --optimize-autoloader --no-dev

# Устанавливаем JS-зависимости и собираем фронтенд
RUN npm install && npm run build

# Права на storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
