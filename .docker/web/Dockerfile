FROM node:latest AS stage-npm
RUN npm install -g zip


FROM php:8-apache AS stage-php-build

RUN apt-get update && apt-get install -y \
    git \
    gnupg \
    libicu-dev \
    python3 \
    unzip

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
	&& echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
	&& apt-get update \
    && apt-get install -y yarn

RUN docker-php-ext-install bcmath \
    && docker-php-ext-install calendar \
    && docker-php-ext-install intl \
    && docker-php-ext-install pdo_mysql

RUN curl https://pecl.php.net/get/APCu -o /tmp/apcu.tar.gz \
    && mkdir -p /usr/src/php/ext/apcu \
    && tar xf /tmp/apcu.tar.gz -C /usr/src/php/ext/apcu --strip-components=1 \
    && docker-php-ext-configure apcu \
    && docker-php-ext-install apcu

#RUN curl https://pecl.php.net/get/xdebug -o /tmp/xdebug.tar.gz \
#        && mkdir -p /usr/src/php/ext/xdebug \
#        && tar xf /tmp/xdebug.tar.gz -C /usr/src/php/ext/xdebug --strip-components=1 \
#        && docker-php-ext-configure xdebug \
#        && docker-php-ext-install xdebug \
#        && rm /tmp/xdebug.tar.gz

COPY . /var/www/html/.
RUN chown -R www-data: /var/www/html

COPY --from=stage-npm /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
COPY --from=stage-npm /usr/local/lib/node_modules/zip /usr/local/lib/node_modules/zip
COPY --from=stage-npm /usr/local/bin/node /usr/local/bin/node

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN mkdir /var/www/.yarn \
    && chown www-data: /var/www/.yarn \
    && mkdir -p /var/www/.cache/yarn \
    && chown www-data: /var/www/.cache/yarn \
	&& touch /var/www/.yarnrc \
	&& chown www-data: /var/www/.yarnrc \
	&& su www-data --shell=/bin/bash -c "yarn install --production" \
	&& su www-data --shell=/bin/bash -c "yarn encore prod"

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && chmod 755 /usr/local/bin/composer \
    && mkdir /var/www/.composer \
    && chown www-data: /var/www/.composer \
    && su www-data --shell=/bin/bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --no-progress --no-dev --no-scripts" \
    && su www-data --shell=/bin/bash -c "composer config extra.symfony.allow-contrib true"  \
    && su www-data --shell=/bin/bash -c "composer require --no-interaction --no-progress --no-scripts symfony/apache-pack"
# TODO: fix --no-scripts   ???


FROM php:8-apache AS stage-php

RUN mkdir /certs

COPY --from=stage-php-build /usr/local/lib/php/extensions/no-debug-non-zts-20210902/apcu.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/
COPY --from=stage-php-build /usr/local/lib/php/extensions/no-debug-non-zts-20210902/bcmath.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/
COPY --from=stage-php-build /usr/local/lib/php/extensions/no-debug-non-zts-20210902/calendar.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/
COPY --from=stage-php-build /usr/local/lib/php/extensions/no-debug-non-zts-20210902/intl.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/
COPY --from=stage-php-build /usr/local/lib/php/extensions/no-debug-non-zts-20210902/pdo_mysql.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/

COPY .docker/web/target/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/web/target/etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

RUN docker-php-ext-enable apcu \
    && docker-php-ext-enable bcmath \
    && docker-php-ext-enable calendar \
    && docker-php-ext-enable intl \
    && docker-php-ext-enable opcache \
    && docker-php-ext-enable pdo_mysql

RUN /usr/sbin/a2enmod rewrite \
	&& /usr/sbin/a2enmod ssl

COPY --from=stage-php-build /var/www/html /var/www/html

RUN chmod 755 /var/www/html/.docker/web/start.sh \
    && ln -s /var/www/html/.docker/web/start.sh /start.sh

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && chmod 755 /usr/local/bin/composer \
    && mkdir /var/www/.composer \
    && chown www-data: /var/www/.composer

CMD [ "/start.sh" ]

EXPOSE 80
EXPOSE 443

ENV SSL_SUBJECT="" \
    DATABASE_URL="" \
    SUPER_ADMIN_EMAIL=""
