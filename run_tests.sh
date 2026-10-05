#!/bin/sh
supervisord &
sleep 5
cd /var/www/app
rm -rf public
rm supervisord.pid
# PHP 8.1 only gets the Laravel 10 skeleton, and every laravel/framework 10 release has security advisories,
# so composer refuses to install it unless advisory blocking is off
if [ "$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')" = "8.1" ]; then
  export COMPOSER_POLICY_ADVISORIES_BLOCK=0
fi
composer create-project --no-audit laravel/laravel .
chown -R app:app .
curl --fail 127.0.0.1