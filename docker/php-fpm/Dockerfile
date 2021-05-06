FROM php:8.0-fpm-alpine
MAINTAINER RevilOn <revil-on@mail.ru>

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER 1
ARG PROJECT_NAME
ARG APP_ENV
ARG TIMEZONE

RUN apk add composer

RUN docker-php-ext-install opcache pdo_mysql

RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "${TIMEZONE}"\n' > /usr/local/etc/php/conf.d/tzone.ini

RUN mkdir -p var/log && mkdir -p var/cache && mkdir -p /tmp/${PROJECT_NAME}
RUN chown -R www-data var
RUN chown -R www-data /tmp/${PROJECT_NAME}

RUN composer self-update --2

RUN echo "\
APP_ENV=${APP_ENV}\
" >> .env.local

COPY composer.json composer.json
COPY composer.lock composer.lock
COPY symfony.lock symfony.lock

RUN composer install \
    --verbose \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --no-progress

COPY phpunit.xml.dist phpunit.xml.dist
COPY tests tests/
COPY bin bin/
COPY config config/
COPY public public/
COPY templates templates/
COPY translations translations/
COPY .env .env
COPY migrations migrations/
COPY src src/

RUN php bin/console cache:clear
RUN php bin/console assets:install
