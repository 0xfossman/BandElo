#!/usr/bin/env bash
set -euo pipefail
APP_DIR=${APP_DIR:-/var/www/bandelo}
SERVER_NAME=${SERVER_NAME:-bandelo.local}
REPO_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
apt-get update
apt-get install -y apache2 mariadb-client php php-cli php-curl php-mysql php-mbstring php-xml php-json unzip curl
if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
  php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
fi
mkdir -p "$APP_DIR"
rsync -a --delete --exclude .git "$REPO_DIR/" "$APP_DIR/"
cd "$APP_DIR"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
cp -n .env.example .env
mkdir -p var/cache var/logs
chown -R www-data:www-data var public
chmod -R u+rwX,g+rwX var
cat >/etc/apache2/sites-available/bandelo.conf <<APACHE
<VirtualHost *:80>
    ServerName ${SERVER_NAME}
    DocumentRoot ${APP_DIR}/public
    <Directory ${APP_DIR}/public>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/bandelo_error.log
    CustomLog \${APACHE_LOG_DIR}/bandelo_access.log combined
</VirtualHost>
APACHE
a2enmod rewrite
a2ensite bandelo.conf
systemctl reload apache2
echo "Deployment complete. Edit ${APP_DIR}/.env, run: cd ${APP_DIR} && COMPOSER_ALLOW_SUPERUSER=1 composer install-app, then reload Apache."
