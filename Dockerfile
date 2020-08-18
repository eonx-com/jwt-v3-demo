FROM php AS composer

RUN apt-get update; \
    # Install APT repository packages
    apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip; \
    rm -rf /var/lib/apt/lists/*;

COPY ./docker/composer/install.sh /tmp/composer/install.sh

RUN chmod +x /tmp/composer/install.sh; \
    /tmp/composer/install.sh; \
    ls -l /tmp/composer; \
    mv composer.phar /usr/local/bin/composer; \
    rm /tmp/composer/install.sh; \
    chmod +x /usr/local/bin/composer; \
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
