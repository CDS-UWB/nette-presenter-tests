FROM php:8.4

RUN apt update && apt install -y \
        zip \
        unzip \
        libzip-dev \
    && docker-php-ext-install -j$(nproc) zip \
    && pecl install xdebug-3.5.0 && docker-php-ext-enable xdebug \
    && apt autoremove

COPY --from=composer:2.9.5 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/app

CMD ["tail", "-f", "/dev/null"]
