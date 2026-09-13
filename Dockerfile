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

# libonig-dev is needed to build the mbstring extension; libpq-dev provides the
# headers pdo_pgsql compiles against; unzip lets Composer extract package
# archives; git covers the few dependencies shipped as sources; gosu lets
# docker-entrypoint.sh drop from root to the checkout's owner.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        gosu \
        libonig-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# pdo_pgsql talks to the database (a local Postgres or Supabase), mbstring is a
# hard Laravel requirement.
RUN docker-php-ext-install -j"$(nproc)" mbstring pdo_pgsql

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

# Fixes ownership of Laravel's writable directories on start-up, then runs the
# command as whoever owns the checkout — see the file for the reasoning.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-php-entrypoint", "/usr/local/bin/docker-entrypoint.sh"]
# Shell form on purpose: the exec form would pass a literal "${PORT}" to
# artisan and the container would never bind the port.  Shell form means the
# variable is expanded, so a host that assigns one (Render sets PORT, default
# 10000) gets honoured, while compose and local runs keep 8000.
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
