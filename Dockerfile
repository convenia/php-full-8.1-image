FROM php:8.3-fpm-alpine3.17
# Informações
LABEL maintainer="leonardo.lemos@convenia.com.br"
LABEL company="Convenia"

# Host user id and group id,.
# override this line to run whith a non 1000 user
ARG UID=1000
ARG GID=1000

# Instalação do php ext installer (https://github.com/mlocati/docker-php-extension-installer)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
# Instalação de pacotes
RUN IPE_GD_WITHOUTAVIF=1 install-php-extensions bcmath bz2 calendar exif gd gettext gmp opcache intl pcntl \
    pdo_mysql sockets xsl zip \
    igbinary-stable \
    redis-stable \
    mongodb-stable

RUN apk add --no-cache --update supervisor=~4.2 nginx=~1.22 nginx-mod-http-headers-more openssh-client git less curl

# Instalação do composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
# Cria usuário e grupo app
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