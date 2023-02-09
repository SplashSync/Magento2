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

################################################################################
# Install PhpUnit 8
################################################################################

if [ ! -f /usr/bin/phpunit ]; then
    echo "Install Phpunit"
    curl https://phar.phpunit.de/phpunit-9.5.28.phar -o phpunit
    chown -R www-data:www-data phpunit
    chmod -X phpunit
    mv phpunit /usr/bin/phpunit
else
    echo "Phpunit Already Installed"
fi

php /usr/bin/phpunit --version


