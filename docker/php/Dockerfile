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
