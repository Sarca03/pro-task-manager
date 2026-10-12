#!/bin/sh
# Render zadaje port kroz $PORT; Apache po defaultu sluša na 80
set -e
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

php /var/www/html/bin/migrate.php
exec apache2-foreground
