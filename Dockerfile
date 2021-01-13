FROM php:7.4-alpine AS composer

RUN apk add \
      git \
      curl \
      unzip;

COPY --from=composer:1 /usr/bin/composer /usr/local/bin/composer

RUN chmod +x /usr/local/bin/composer; \
    /usr/local/bin/composer --version; \
    /usr/local/bin/composer global require hirak/prestissimo -n

FROM composer AS vendor

WORKDIR /var/www
COPY ./composer.json /var/www/composer.json
COPY ./composer.lock /var/www/composer.lock
RUN /usr/local/bin/composer install --prefer-dist -o -v;
RUN /usr/local/bin/composer dump-autoload --optimize;

FROM php

WORKDIR /var/www/public
COPY --from=vendor /var/www/vendor /var/www/vendor
COPY ./public /var/www/public

ENTRYPOINT ["php" , "-S", "0.0.0.0:8000"]
