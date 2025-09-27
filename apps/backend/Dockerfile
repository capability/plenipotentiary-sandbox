# -----------------------------------------------------------------------------
# Base PHP-FPM image: extensions, composer, app-user, fpm pool
# -----------------------------------------------------------------------------
FROM php:8.3-fpm-bookworm AS base

# System deps
RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    curl git unzip zip \
    libicu-dev libzip-dev \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    gosu ca-certificates; \
  rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN set -eux; \
  docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
  docker-php-ext-install -j"$(nproc)" pdo_mysql intl zip gd bcmath; \
  pecl install redis; \
  docker-php-ext-enable redis

# Composer + just
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN curl -sSfL https://just.systems/install.sh | bash -s -- --to /usr/local/bin

# Coverage & Debug
RUN pecl install pcov \
 && docker-php-ext-enable pcov \
 && { \
    echo "pcov.enabled=1"; \
    echo "pcov.directory=/workspaces/stack-root/apps/backend/app"; \
    echo 'pcov.exclude="~(vendor|storage|bootstrap/cache)~"'; \
  } > /usr/local/etc/php/conf.d/zz-pcov.ini

# Xdebug: installed but disabled by default
RUN pecl install xdebug \
 && docker-php-ext-enable xdebug
COPY docker/php/zz-xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

# Create unprivileged user/group mapped to host UID/GID
ARG UID=1000
ARG GID=1000
RUN set -eux; \
  if getent group "${GID}" >/dev/null; then \
    GN="$(getent group "${GID}" | cut -d: -f1)"; \
  else \
    groupadd -g "${GID}" app-user; GN="app-user"; \
  fi; \
  id -u app-user >/dev/null 2>&1 || useradd -m -u "${UID}" -g "${GN}" -s /bin/bash app-user

RUN mkdir -p /home/app-user/workspaces \
 && chown -R app-user:app-user /home/app-user

# Composer defaults
ENV COMPOSER_HOME=/home/app-user/.composer \
    COMPOSER_CACHE_DIR=/home/app-user/.composer/cache \
    PATH="/home/app-user/.composer/vendor/bin:${PATH}"

# PHP config
WORKDIR /workspaces/stack-root/apps/backend
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/zz-pool-user.conf /usr/local/etc/php-fpm.d/zz-pool-user.conf

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod 0755 /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm","-F"]

# -----------------------------------------------------------------------------
# Development layer: extra shells/tools (target=development)
# -----------------------------------------------------------------------------
FROM base AS development

RUN set -eux; \
  apt-get update; \
  apt-get install -y --no-install-recommends \
    curl jq rsync zsh zsh-autosuggestions zsh-syntax-highlighting \
    bash-completion vim less htop; \
  rm -rf /var/lib/apt/lists/*
