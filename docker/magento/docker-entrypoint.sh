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
# Ensure Splash Vendor DIR doesn't exists
if [ -d /var/www/module/vendor ]; then
    echo "Module Vendor MUST be Deleted before Docker Start"
    exit 1
fi

################################################################################
# Wait for Mysql Server Wakeup
sh /var/www/module/scripts/wait-for-mysql.sh

################################################################################
# First Time => INSTALL MAGENTO + SAMPLE DATA
if [ ! -f installed.txt ]; then
    echo "Install Magento"
    install-magento
#    echo "Install Sample data"
#    install-sampledata
fi
echo "Installed" > installed.txt

su www-data

################################################################################
# Install Phpunit
sh /var/www/module/scripts/install-phpunit.sh
################################################################################
# Install SplashSync Module
sh /var/www/module/scripts/install-dev-module.sh
################################################################################
# Configure Magento for Development & Tests
sh /var/www/module/scripts/setup-magento.sh
################################################################################
# Compile Magento
sh /var/www/module/scripts/compile-magento.sh

################################################################################
echo "Init Magento"
exec /sbin/my_init
