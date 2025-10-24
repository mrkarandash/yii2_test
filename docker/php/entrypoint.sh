#!/bin/sh
set -e

echo "🚀 ENTRYPOINT STARTED"

APP_PATH="/app/composer.json"
TEMP_DIR="/tmp/yii2-install"

if [ ! -f "$APP_PATH" ]; then
    echo "⚙️ Yii2 не найден, выполняется установка..."
    mkdir -p "$TEMP_DIR"
    composer create-project --prefer-dist yiisoft/yii2-app-advanced "$TEMP_DIR"

    echo "📦 Перенос файлов в /app (без docker-compose.yml)..."
    cd "$TEMP_DIR"
    find . -maxdepth 1 ! -name '.' ! -name 'docker-compose.yml' -exec cp -r {} /app/ \;

    cd /app
    php init --env=Development --overwrite=All --delete=All --interactive=0

    DB_CONFIG_FILE="/app/common/config/main-local.php"
    if [ -f "$DB_CONFIG_FILE" ]; then
        echo "🔧 Настраивается подключение к MySQL..."
        sed -i "s/'dsn' => 'mysql:host=.*/'dsn' => 'mysql:host=db;dbname=yii2db',/" $DB_CONFIG_FILE
        sed -i "s/'username' => 'root'/'username' => 'yii2user'/" $DB_CONFIG_FILE
        sed -i "s/'password' => ''/'password' => 'yii2pass'/" $DB_CONFIG_FILE
        sed -i "s/'charset' => 'utf8'/'charset' => 'utf8mb4'/" $DB_CONFIG_FILE
    fi

    echo "📥 Установка зависимостей..."
    composer install --no-interaction --prefer-dist

    echo "🗃️ Применение миграций..."
    php yii migrate --interactive=0 || true

    echo "✅ Yii2 успешно установлен и настроен."
else
    echo "✅ Yii2 уже установлен, проверка зависимостей..."
    cd /app
    composer install --no-interaction --prefer-dist
    php yii migrate --interactive=0 || true
fi

exec php-fpm
