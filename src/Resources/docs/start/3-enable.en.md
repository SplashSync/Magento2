---
lang: en
permalink: start/enable
title: Enable the Module
---

### Activate Splash Module from CLI

Now that module is installed, you have to activate it Magento CLI.

```bash
php bin/magento module:enable SplashSync_Magento2
```

### Clear Cache & Restart your application

To ensure changes are done, you need to recompile and flush all cache.

```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:enable
php bin/magento cache:clean
```

### Setup Splash Minimal Parameters from CLI

At this step, you should be able to see Splash Connector on your store config!

If, for any reasons, you have to programmatically setup Splash, 
see below minimal configuration values. 

```bash
bin/magento config:set splashsync/core/id                               "ThisIsMagento2Key"
bin/magento config:set splashsync/core/key                              "ThisTokenIsNotSoSecretChangeIt"
bin/magento config:set splashsync/security/username                     "admin"
```
