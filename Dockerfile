FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_pgsql mysqli curl \
    && rm -rf /var/lib/apt/lists/*

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' \
        'display_errors=Off' \
        'log_errors=On' \
        'expose_php=Off' \
        'session.cookie_httponly=1' \
        'session.cookie_secure=1' \
        'session.cookie_samesite=Lax' \
        'session.use_strict_mode=1' \
        'upload_max_filesize=3M' \
        'post_max_size=4M' \
        > "$PHP_INI_DIR/conf.d/medishop.ini"

RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/
EXPOSE 10000
