#!/bin/bash

if [[ ! -z "$SSL_SUBJECT" ]]; then
	if [[ ! -f /certs/ssl-cert.key ]] && [[ ! -f /certs/ssl-cert.pem ]]; then
	  openssl req -x509 -nodes -days 820 -newkey rsa:2048 -keyout /certs/ssl-cert.key -out /certs/ssl-cert.pem -subj "${SSL_SUBJECT}"
	fi

	sed -i 's/##REWRITE_HTTPS##//g' /etc/apache2/sites-available/000-default.conf
	a2ensite default-ssl
fi

if [[ ! -z "$TZ" ]]; then
  printf "[PHP]\ndate.timezone = \"${TZ}\"\n" > /usr/local/etc/php/conf.d/tzone.ini
fi

case "${APP_ENV}" in
	dev)
		cp -a /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
		;;
	*)
		cp -a /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
		;;
esac

sed -i 's/^max_execution_time.*/max_execution_time = 120/' /usr/local/etc/php/php.ini

if [ -f /var/www/html/public/.htaccess ]; then
  sed -i '/##SYMFONY-APACHE-PACK##/ r /var/www/html/public/.htaccess' /etc/apache2/sites-available/000-default.conf
  sed -i '/##SYMFONY-APACHE-PACK##/ r /var/www/html/public/.htaccess' /etc/apache2/sites-available/default-ssl.conf
fi

rm -f /var/www/html/.env.local
test -z $APP_ENV || echo "APP_ENV=${APP_ENV}" >> /var/www/html/.env.local
test -z $APP_SECRET || echo "APP_SECRET=${APP_SECRET}" >> /var/www/html/.env.local
test -z $SITE_NAME || echo "SITE_NAME=${SITE_NAME}" >> /var/www/html/.env.local
test -z $SITE_URI || echo "SITE_URI=${SITE_URI}" >> /var/www/html/.env.local
test -z $DATABASE_URL || echo "DATABASE_URL=${DATABASE_URL}" >> /var/www/html/.env.local
test -z $MAILER_DSN || echo "MAILER_DSN=${MAILER_DSN}" >> /var/www/html/.env.local
test -z $SUPER_ADMIN_EMAIL || echo "SUPER_ADMIN_EMAIL=${SUPER_ADMIN_EMAIL}" >> /var/www/html/.env.local
test -z $MAILER_SENDER_ADDRESS || echo "MAILER_SENDER_ADDRESS=${MAILER_SENDER_ADDRESS}" >> /var/www/html/.env.local
test -z "$MAILER_SENDER_NAME" || echo "MAILER_SENDER_NAME=\"${MAILER_SENDER_NAME}\"" >> /var/www/html/.env.local
# test -z $APP_MAILER_DEV_RECIPIENT || echo "APP_MAILER_DEV_RECIPIENT='${APP_MAILER_DEV_RECIPIENT}'" >> /var/www/html/.env.local
# test -z $APP_DATATABLES_USE_FIXED_COLUMNS || echo "APP_DATATABLES_USE_FIXED_COLUMNS='${APP_DATATABLES_USE_FIXED_COLUMNS}'" >> /var/www/html/.env.local

# su www-data --shell=/bin/bash -c "php bin/console --no-interaction --env=${APP_ENV} doctrine:schema:update -f" \

echo '***'
echo '*** Remember to run "php bin/console doctrine:migrations:migrate" after DB structure changes ***'
echo '***'

su www-data --shell=/bin/bash -c "COMPOSER_MEMORY_LIMIT=-1 composer run-script post-update-cmd" \
    && apache2-foreground
