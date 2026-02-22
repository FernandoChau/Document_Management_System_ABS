#!/bin/sh
# Inicia o PHP-FPM em background
php-fpm -D
# Inicia o Nginx em foreground
nginx -g "daemon off;"