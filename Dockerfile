# Laravel 10 API (PHP 8.2) for the CTU LMS backend.
#
# Runs `php artisan serve`, i.e. the same entry point start_lms.sh used before,
# but inside a container.  PHP dependencies are baked into the image; the source
# code is bind-mounted by docker-compose so edits are picked up immediately.
FROM php:8.2-cli

# Docker's embedded DNS can hand out an AAAA record for a host that the container
# has no IPv6 route to, which makes apt/composer hang on "Connection timed out".
# Preferring IPv4 makes both the build and runtime network calls work.
RUN echo 'precedence ::ffff:0:0/96  100' >> /etc/gai.conf

# libonig-dev is needed to build the mbstring extension; unzip lets Composer
# extract package archives; git covers the few dependencies shipped as sources.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# pdo_mysql talks to the db service, mbstring is a hard Laravel requirement.
RUN docker-php-ext-install -j"$(nproc)" mbstring pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependencies first, so this layer is only rebuilt when the lock file changes.
# --no-scripts skips `artisan package:discover`, which needs a booted app and a
# writable bootstrap/cache/ (bind-mounted at runtime, see docker-compose.yml).
COPY composer.json composer.lock ./
RUN composer install \
        --no-interaction \
        --no-scripts \
        --prefer-dist \
        --no-progress

COPY . .

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
