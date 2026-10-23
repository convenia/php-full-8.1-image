#!/bin/sh
# Ensure the installed binary contains the CVE-2026-109618 / CVE-2026-109619 backport
grep -q zend_new_array_singleton /usr/local/sbin/php-fpm || { echo "PHP security backport missing"; exit 1; }
supervisord &
sleep 5
cd /var/www/app
rm -rf public
rm supervisord.pid
composer create-project laravel/laravel .
chown -R app:app .
curl --fail 127.0.0.1
