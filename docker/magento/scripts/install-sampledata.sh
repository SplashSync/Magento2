#!/usr/bin/env bash

cd /var/www/html
su www-data

cp $COMPOSER_HOME/auth.json $INSTALL_DIR/var/composer_home/auth.json

bin/magento sampledata:deploy
compile-magento.sh
