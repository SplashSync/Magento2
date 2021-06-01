#!/bin/bash
################################################################################
#
#  This file is part of SplashSync Project.
#
#  Copyright (C) Splash Sync <www.splashsync.com>
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

set -e
cd /var/www/html

if [ -d vendor/splash/magento2 ]; then
    echo "Composer Module Already Installed"
else
    echo "Install SplashSync Module via Composer"
    composer config repositories.splash '{ "type": "path", "url": "/builds/SplashSync/Magento2", "options": { "symlink": true } }'
    composer config minimum-stability dev
#    COMPOSER_MEMORY_LIMIT=-1 composer require splash/magento2:dev-master --no-scripts --update-with-dependencies --no-progress --no-suggest
    COMPOSER_MEMORY_LIMIT=-1 composer require splash/magento2:dev-master --no-scripts --no-progress --no-suggest
    composer info | grep "splash"
fi

#composer config repositories.splash '{ "type": "path", "url": "/var/www/phpcore", "options": { "symlink": true } }'
#COMPOSER_MEMORY_LIMIT=-1 composer require splash/phpcore:dev-master --no-scripts --update-with-dependencies --no-progress --no-suggest
#composer info | grep "splash"


chown -R www-data:www-data /var/www/html/
cp vendor/splash/magento2/phpunit.xml.dist phpunit.xml.dist

################################################################################
echo "Enable SplashSync Module"
php bin/magento module:disable SplashSync_Magento2
php bin/magento module:enable SplashSync_Magento2
bin/magento config:set dev/template/allow_symlink 1
