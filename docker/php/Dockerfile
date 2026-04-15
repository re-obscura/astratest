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
# Заменяем зеркало на Яндекс для стабильности сборки
RUN sed -i 's/deb.debian.org/mirror.yandex.ru/g' /etc/apt/sources.list.d/debian.sources || sed -i 's/deb.debian.org/mirror.yandex.ru/g' /etc/apt/sources.list
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

# Копируем файлы зависимостей первыми для лучшего кэширования Docker слоёв
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-scripts

COPY package.json package-lock.json ./
RUN npm install

# Копируем остальные файлы проекта
COPY . /var/www

# Перегенерируем автозагрузку после копирования исходников
RUN composer dump-autoload --optimize

# Собираем фронтенд
RUN npm run build

# Права на storage и bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Копируем и делаем исполняемым entrypoint
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
