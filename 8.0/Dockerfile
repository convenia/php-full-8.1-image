# 8.0 is end of life: 8.0.30 on alpine3.16 is its last image, it only gets the security backport below
FROM php:8.0.30-fpm-alpine3.16
# Information
LABEL maintainer="leonardo.lemos@convenia.com.br"
LABEL company="Convenia"

# Host user id and group id,.
# override this line to run whith a non 1000 user
ARG UID=1000
ARG GID=1000

# PHP 8.0 is EOL: rebuild PHP 8.0.30 with the official PHP security backport
# (CVE-2026-109618 / CVE-2026-109619, commit php/php-src@82f8f31b16a0 from the
# PHP-8.0-CVE-2026-109618-109619 branch). Must run before the extensions so they
# are compiled against the patched headers.
COPY ./8.0/php-8.0-CVE-2026-109618-109619.patch /usr/src/php-8.0-CVE-2026-109618-109619.patch
RUN set -eux; \
    configureOptions="$(php-config --configure-options)"; \
    apk add --no-cache --virtual .php-rebuild-deps \
        $PHPIZE_DEPS argon2-dev coreutils curl-dev gnu-libiconv-dev libsodium-dev libxml2-dev \
        linux-headers oniguruma-dev openssl-dev patch readline-dev sqlite-dev; \
    export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS"; \
    docker-php-source extract; \
    cd /usr/src/php; \
    patch -p1 < /usr/src/php-8.0-CVE-2026-109618-109619.patch; \
    ./configure $configureOptions; \
    make -j "$(nproc)"; \
    find -type f -name '*.a' -delete; \
    make install; \
    find /usr/local -type f -perm '/0111' -exec sh -euxc ' \
        strip --strip-all "$@" || : \
    ' -- '{}' +; \
    cd /; \
    docker-php-source delete; \
    rm /usr/src/php-8.0-CVE-2026-109618-109619.patch; \
    apk del --no-network .php-rebuild-deps; \
    rm -rf /tmp/pear ~/.pearrc; \
    grep -q zend_new_array_singleton /usr/local/sbin/php-fpm; \
    php --version
LABEL php.security-backport="CVE-2026-109618,CVE-2026-109619"

# Install php ext installer (https://github.com/mlocati/docker-php-extension-installer)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
# Install packages
RUN IPE_GD_WITHOUTAVIF=1 install-php-extensions bcmath bz2 calendar exif gd gettext gmp opcache intl pcntl \
    pdo_mysql sockets xsl zip \
    igbinary-stable \
    redis-stable \
    mongodb \
    mysqli

RUN apk add --no-cache --update supervisor nginx nginx-mod-http-headers-more openssh-client git less curl

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# Create app user and group
RUN addgroup -S -g $GID app && adduser -u $UID -G app -D app

# Copy configuration files
COPY ./docker/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY ./docker/nginx/default.conf /etc/nginx/http.d/default.conf
# For Automated Tests on Docker Hub
COPY ./8.0/run_tests.sh /run_tests.sh

#Enable production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

ADD ./public /var/www/app/public
RUN chown -R app:app /var/www/app && chmod +x /run_tests.sh

WORKDIR /var/www/app

EXPOSE 80

CMD [ "/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf" ]