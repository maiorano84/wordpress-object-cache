ARG PHP_VERSION=8.4

FROM wordpress:php${PHP_VERSION}

RUN apt-get update && \
    apt-get install -y zlib1g-dev libzip-dev libmemcached-dev unzip && \
    pecl install redis memcached apcu xdebug && \
    docker-php-ext-enable redis memcached apcu xdebug && \
    echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

copy --from=wordpress:cli /usr/local/bin/wp /usr/bin/wp
COPY --from=composer /usr/bin/composer /usr/bin/composer