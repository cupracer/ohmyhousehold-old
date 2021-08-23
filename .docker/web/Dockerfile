FROM node:latest
RUN npm install -g zip

FROM php:8-apache

RUN apt-get update && apt-get install -y \
	apt-transport-https \
	apt-utils \
        g++ \
        git \
        gnupg \
        libicu-dev \
        locales-all \
        lsb-release \
        unzip \
        vim \
        zlib1g-dev

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
	&& echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
	&& apt-get update

RUN apt-get update && apt-get install -y \
	yarn

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
        && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
        && php -r "unlink('composer-setup.php');" \
        && chmod 755 /usr/local/bin/composer

RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable opcache

RUN curl https://pecl.php.net/get/APCu -o /tmp/apcu.tar.gz \
        && mkdir -p /usr/src/php/ext/apcu \
        && tar xf /tmp/apcu.tar.gz -C /usr/src/php/ext/apcu --strip-components=1 \
        && docker-php-ext-configure apcu \
        && docker-php-ext-install apcu \
        && rm /tmp/apcu.tar.gz

RUN docker-php-ext-install intl \
        && docker-php-ext-enable intl

RUN docker-php-ext-install bcmath \
        && docker-php-ext-enable bcmath

#RUN curl https://pecl.php.net/get/xdebug -o /tmp/xdebug.tar.gz \
#        && mkdir -p /usr/src/php/ext/xdebug \
#        && tar xf /tmp/xdebug.tar.gz -C /usr/src/php/ext/xdebug --strip-components=1 \
#        && docker-php-ext-configure xdebug \
#        && docker-php-ext-install xdebug \
#        && rm /tmp/xdebug.tar.gz

RUN mkdir /certs /var/www/.composer /var/www/.config /var/www/.cache /var/www/.local && chown www-data: /var/www/.composer /var/www/.config /var/www/.cache /var/www/.local

COPY --from=0 /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
COPY --from=0 /usr/local/lib/node_modules/zip /usr/local/lib/node_modules/zip
COPY --from=0 /usr/local/bin/node /usr/local/bin/node

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

COPY .docker/web/target/etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/web/target/etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf

RUN /usr/sbin/a2enmod rewrite \
	&& /usr/sbin/a2enmod ssl

COPY . /var/www/html/.
RUN chown -R www-data: /var/www/html

# TODO: fix --no-scripts

#RUN su www-data --shell=/bin/bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --no-scripts" \
RUN su www-data --shell=/bin/bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install --no-interaction --no-dev --optimize-autoloader --no-scripts" \
        && su www-data --shell=/bin/bash -c "composer config extra.symfony.allow-contrib true" \
        && su www-data --shell=/bin/bash -c "composer require symfony/apache-pack"

RUN mkdir /var/www/.yarn \
	&& chown www-data: /var/www/.yarn \
	&& touch /var/www/.yarnrc \
	&& chown www-data: /var/www/.yarnrc \
	&& su www-data --shell=/bin/bash -c "yarn install" \
	&& su www-data --shell=/bin/bash -c "yarn encore prod"

RUN chmod 755 /var/www/html/.docker/web/start.sh \
    && ln -s /var/www/html/.docker/web/start.sh /start.sh

RUN apt-get remove -y \
        g++ \
        git \
        && apt autoremove -y

CMD [ "/start.sh" ]
