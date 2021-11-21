---
lang: fr
permalink: start/enable
title: Activer le Bundle
---

### Activer le module Splash depuis le CLI

Maintenant que ce module est installé, vous devez l'activer Magento CLI.

```bash
php bin/magento module:enable SplashSync_Magento2
```

### Videz le cache et redémarrez votre application

Pour vous assurer que les modifications sont effectuées, vous devez recompiler et vider tout le cache.

```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:enable
php bin/magento cache:clean
```

### Configurer les paramètres minimaux à partir de la CLI

À cette étape, vous devriez pouvoir voir Splash Connector sur la configuration de votre boutique !

Si, pour une quelconque raison, vous devez configurer Splash par programmation,
voir ci-dessous les valeurs de configuration minimales.

```bash
bin/magento config:set splashsync/core/id                               "ThisIsMagento2Key"
bin/magento config:set splashsync/core/key                              "ThisTokenIsNotSoSecretChangeIt"
bin/magento config:set splashsync/security/username                     "admin"
```
