docker-compose exec magento php bin/magento setup:upgrade
docker-compose exec magento php bin/magento setup:di:compile
docker-compose exec magento php bin/magento setup:static-content:deploy -f
docker-compose exec magento php bin/magento cache:clean
docker-compose exec magento chown -R www-data:www-data /var/www/html/