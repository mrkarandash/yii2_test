#!/bin/bash
set -e

# Абсолютный путь к директории скрипта
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"

echo "🚀 Инициализация проекта Yii2 + Docker"

# 1️⃣ Создаем структуру директорий
mkdir -p "$PROJECT_DIR"/{backend,common,console,frontend,migrations,runtime,web}
mkdir -p "$PROJECT_DIR/docker"/{nginx,php}

# 2️⃣ Dockerfile
cat > "$PROJECT_DIR/docker/php/Dockerfile" <<'EOF'
FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    libzip-dev \
    unzip \
    zip \
    icu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl \
    && docker-php-ext-enable intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /app

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

EXPOSE 9000
EOF

# 3️⃣ php.ini
cat > "$PROJECT_DIR/docker/php/php.ini" <<'EOF'
date.timezone = Europe/Moscow
memory_limit = 512M
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 60
EOF

# 4️⃣ entrypoint.sh (копирование без docker-compose.yml)
cat > "$PROJECT_DIR/docker/php/entrypoint.sh" <<'EOF'
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
EOF

chmod +x "$PROJECT_DIR/docker/php/entrypoint.sh"
echo "🔧 entrypoint.sh создан и сделан исполняемым"

# 5️⃣ Nginx default.conf
cat > "$PROJECT_DIR/docker/nginx/default.conf" <<'EOF'
server {
    listen 80;
    server_name localhost;
    root /app/frontend/web;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires max;
        log_not_found off;
    }
}
EOF

# 6️⃣ .env
cat > "$PROJECT_DIR/docker/.env" <<'EOF'
MYSQL_ROOT_PASSWORD=root
MYSQL_DATABASE=yii2db
MYSQL_USER=yii2user
MYSQL_PASSWORD=yii2pass
EOF

# 7️⃣ docker-compose.yml — создаём только если его нет
COMPOSE_FILE="$PROJECT_DIR/docker-compose.yml"
if [ ! -f "$COMPOSE_FILE" ]; then
cat > "$COMPOSE_FILE" <<'EOF'
services:
  php:
    build:
      context: ./docker/php
    container_name: yii2-php
    volumes:
      - ./:/app
    depends_on:
      - db
    environment:
      - TZ=Europe/Moscow
    ports:
      - "9000:9000"

  nginx:
    image: nginx:latest
    container_name: yii2-nginx
    ports:
      - "8080:80"
    volumes:
      - ./:/app
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php

  db:
    image: mysql:8.0
    container_name: yii2-db
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: yii2db
      MYSQL_USER: yii2user
      MYSQL_PASSWORD: yii2pass
    volumes:
      - db_data:/var/lib/mysql
    ports:
      - "3307:3306"

  adminer:
    image: adminer
    container_name: yii2-adminer
    restart: always
    ports:
      - "8081:8080"

volumes:
  db_data:
EOF
    echo "📦 docker-compose.yml создан"
else
    echo "⚠️ docker-compose.yml уже существует — не перезаписывается"
fi

echo "✅ Инициализация завершена. Теперь выполните: docker-compose up -d --build"
