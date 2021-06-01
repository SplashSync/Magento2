#!/usr/bin/env bash

su www-data

cd /var/www/html
su www-data

################################################################################
# First Time => INSTALL MAGENTO + SAMPLE DATA
if [ ! -f installed.txt ]; then

    echo "Run Magento Installer"
    bin/magento setup:install \
        --base-url=$MAGENTO_URL \
        --backend-frontname=$MAGENTO_BACKEND_FRONTNAME \
        --language=$MAGENTO_LANGUAGE \
        --timezone=$MAGENTO_TIMEZONE \
        --currency=$MAGENTO_DEFAULT_CURRENCY \
        --db-host=$MYSQL_HOST \
        --db-name=$MYSQL_DATABASE \
        --db-user=$MYSQL_USER \
        --db-password=$MYSQL_PASSWORD \
        --use-secure=$MAGENTO_USE_SECURE \
        --base-url-secure=$MAGENTO_BASE_URL_SECURE \
        --use-secure-admin=$MAGENTO_USE_SECURE_ADMIN \
        --admin-firstname=$MAGENTO_ADMIN_FIRSTNAME \
        --admin-lastname=$MAGENTO_ADMIN_LASTNAME \
        --admin-email=$MAGENTO_ADMIN_EMAIL \
        --admin-user=$MAGENTO_ADMIN_USERNAME \
        --admin-password=$MAGENTO_ADMIN_PASSWORD \
        --elasticsearch-host=$ELASTICSEARCH-HOST \
        --elasticsearch-port=$ELASTICSEARCH-PORT \
        --enable-modules=MAGENTO_ENABLE \
        --disable-modules=$MAGENTO_DISABLE

fi
echo "Installed" > installed.txt
