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
# Setup Crontab
################################################################################

echo "Setup Crontab"
if [ ! -f /etc/cron.d/magento2-cron ]; then
    echo "File is Missing"
    exit 1
fi
chmod 0644 /etc/cron.d/magento2-cron && crontab -u www-data /etc/cron.d/magento2-cron


