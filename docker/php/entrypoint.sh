#!/bin/bash
set -e

# ============================================
# Entrypoint: инициализация Laravel в контейнере
# ============================================

cd /var/www

# Устанавливаем PHP-зависимости, если vendor отсутствует
if [ ! -d "vendor" ]; then
    echo "📦 Установка PHP-зависимостей..."
    composer install --optimize-autoloader --no-dev
fi

# Собираем фронтенд, если public/build отсутствует
if [ ! -d "public/build" ]; then
    echo "🔨 Установка JS-зависимостей и сборка фронтенда..."
    npm install
    npm run build
fi

# Права на storage и bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Очищаем кэш конфигурации перед генерацией ключа
php artisan config:clear 2>/dev/null || true

# Генерируем APP_KEY, если он пустой в .env
if grep -q "^APP_KEY=$" .env 2>/dev/null; then
    echo "🔑 Генерация APP_KEY..."
    php artisan key:generate --force
fi

# Кэшируем конфиг и маршруты ПОСЛЕ генерации ключа
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Запускаем миграции
echo "🗄️  Запуск миграций..."
php artisan migrate --force

echo "✅ Приложение готово!"

# Запуск php-fpm
exec php-fpm
