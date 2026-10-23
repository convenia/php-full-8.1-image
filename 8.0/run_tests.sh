#!/bin/sh
# PHP 8.0 is rebuilt with the CVE-2026-109618 / CVE-2026-109619 backport: make sure the installed binary has it
grep -q zend_new_array_singleton /usr/local/sbin/php-fpm || { echo "PHP security backport missing"; exit 1; }
supervisord &
sleep 5
cd /var/www/app
rm -rf public
rm supervisord.pid
# PHP 8.0 only gets the Laravel 9 skeleton, and every laravel/framework 9 release has security advisories,
# so composer refuses to install it unless advisory blocking is off
export COMPOSER_POLICY_ADVISORIES_BLOCK=0
composer create-project --no-audit laravel/laravel .
chown -R app:app .
curl --fail 127.0.0.1
