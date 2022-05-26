#!/bin/sh
rm -rf public
rm supervisord.pid
rm run.sh
composer create-project laravel/laravel .
curl --fail 127.0.0.1