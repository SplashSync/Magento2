#!/usr/bin/env bash

cd /var/www/html
chown -R www-data:www-data /var/www

echo "Create Project"
su www-data -c "composer create-project --no-progress --repository-url=https://repo.magento.com/ magento/project-community-edition $INSTALL_DIR $MAGENTO_VERSION"

echo "Install SplashSync Module via Composer"
composer config repositories.splash '{ "type": "path", "url": "/builds/SplashSync/Magento2", "options": { "symlink": true, "versions": { "splash/magento2": "dev-local" }}}'
composer config minimum-stability dev
COMPOSER_MEMORY_LIMIT=-1 composer require splash/magento2:dev-master --no-scripts --no-progress --no-suggest
composer info | grep "splash"

chmod 770 -Rf $INSTALL_DIR && chmod u+x bin/magento && chown -R www-data:www-data /var/www
