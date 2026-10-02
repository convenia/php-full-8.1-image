FROM php:8.5-fpm-alpine3.21 AS base
# Information
LABEL maintainer="leonardo.lemos@convenia.com.br"
LABEL company="Convenia"

# Host user id and group id,.
# override this line to run whith a non 1000 user
ARG UID=1000
ARG GID=1000

# Install the php ext installer (https://github.com/mlocati/docker-php-extension-installer)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
# Install packages
RUN IPE_GD_WITHOUTAVIF=1 install-php-extensions bcmath bz2 calendar exif gd gettext gmp intl pcntl \
    pdo_mysql sockets xsl zip \
    igbinary-stable \
    redis-stable \
    mongodb-^2@stable \
    mysqli

RUN apk add --no-cache --update supervisor=~4.2 nginx=~1.26 nginx-mod-http-headers-more openssh-client git less curl

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# Create the app user and group
RUN addgroup -S -g $GID app && adduser -u $UID -G app -D app

# Copy configuration files
COPY ./docker/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY ./docker/nginx/default.conf /etc/nginx/http.d/default.conf
# For Automated Tests on Docker Hub
COPY ./run_tests.sh /run_tests.sh

#Enable production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ADD ./public /var/www/app/public
RUN chown -R app:app /var/www/app && chmod +x /run_tests.sh

WORKDIR /var/www/app

EXPOSE 80

CMD [ "/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf" ]

# Development version: docker build --target development .
FROM base AS development

ARG NVM_VERSION=v0.40.8
ARG NODE_VERSION=24

# nvm detects Alpine and downloads the musl binaries from the unofficial builds (no node compilation)
ENV NVM_DIR=/usr/local/nvm \
    NVM_NODEJS_ORG_MIRROR=https://unofficial-builds.nodejs.org/download/release \
    NVM_SYMLINK_CURRENT=true \
    COMPOSER_HOME=/usr/local/composer \
    BASH_ENV=/usr/local/etc/nvm.sh
# $NVM_DIR/current points to the version of the last "nvm use", so node is in the PATH in any shell
ENV PATH=$NVM_DIR/current/bin:$COMPOSER_HOME/vendor/bin:$PATH

# coreutils and libstdc++ are nvm and node dependencies on Alpine
RUN apk add --no-cache bash vim sudo coreutils libstdc++ \
    && echo 'app ALL=(ALL) NOPASSWD: ALL' > /etc/sudoers.d/app && chmod 0440 /etc/sudoers.d/app

RUN install-php-extensions pcov

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# nvm is a shell function: lazy loaded in any bash (interactive via bashrc, non-interactive via BASH_ENV)
RUN git clone --depth 1 --branch "$NVM_VERSION" https://github.com/nvm-sh/nvm.git "$NVM_DIR" \
    && echo 'nvm() { unset -f nvm; . "$NVM_DIR/nvm.sh" --no-use; nvm "$@"; }' > "$BASH_ENV" \
    && echo ". $BASH_ENV" >> /etc/bash/bashrc \
    && . "$BASH_ENV" && nvm install "$NODE_VERSION" && nvm alias default "$NODE_VERSION" && nvm cache clear \
    && npm install --global --prefix /usr/local @redocly/cli yarn && npm cache clean --force \
    && chown -R app:app "$NVM_DIR"

RUN composer global config --no-plugins allow-plugins.infection/extension-installer true \
    && composer global require infection/infection \
    && composer clear-cache \
    && chown -R app:app "$COMPOSER_HOME"

SHELL ["/bin/sh", "-c"]

# Production version (default): docker build .
FROM base AS production
