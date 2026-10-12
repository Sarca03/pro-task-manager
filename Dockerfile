# PHP 8.3 + Apache, sa drajverom za PostgreSQL (Neon)
FROM php:8.3-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev \
 && docker-php-ext-install pdo_pgsql \
 && apt-get purge -y --auto-remove \
 && rm -rf /var/lib/apt/lists/*

# Produkcijska PHP podešavanja: greške u log, ne na stranicu
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && printf "expose_php = Off\n" > "$PHP_INI_DIR/conf.d/security.ini" \
 && printf "ServerTokens Prod\nServerSignature Off\n" > /etc/apache2/conf-enabled/security-hardening.conf

COPY . /var/www/html/
# Pomoćni fajlovi ne smeju da budu dostupni preko weba
RUN rm -rf /var/www/html/bin/start.sh \
 && printf "<Directory /var/www/html>\n  Options -Indexes\n</Directory>\n<DirectoryMatch \"^/var/www/html/(bin|config|includes)\">\n  Require all denied\n</DirectoryMatch>\n<FilesMatch \"\\\\.(sql|md)$|^(Dockerfile|\\\\.)\">\n  Require all denied\n</FilesMatch>\n" > /etc/apache2/conf-enabled/app.conf

COPY bin/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh
CMD ["/usr/local/bin/start.sh"]
