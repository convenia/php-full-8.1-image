#!/bin/sh
supervisord &
sleep 5
cd /var/www/app
rm -rf public
rm supervisord.pid
composer create-project laravel/laravel .
chown -R app:app .
curl --fail 127.0.0.1