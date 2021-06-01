<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local;

use ArrayObject;
use Exception;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Module\ModuleListInterface;
use Magento\User\Model\User;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Helpers\MageHelper;
use Splash\Models\LocalClassInterface;

/**
 * Splash PHP Module For Magento 1 - Local Core Class
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Local implements LocalClassInterface
{
    /**
     * Object Commit Mode
     *
     * @var string
     */
    public $action;

    //====================================================================//
    // *******************************************************************//
    //  MANDATORY CORE MODULE LOCAL FUNCTIONS
    // *******************************************************************//
    //====================================================================//

    /**
     * {@inheritDoc}
     */
    public function parameters(): array
    {
        $parameters = array();
        //====================================================================//
        // Server Identification Parameters
        $parameters["WsIdentifier"] = MageHelper::getStoreConfig('splashsync/core/id');
        $parameters["WsEncryptionKey"] = MageHelper::getStoreConfig('splashsync/core/key');
        //====================================================================//
        // Check If Expert Mode is Active
        $isExpert = MageHelper::getStoreConfig('splashsync/core/expert');
        //====================================================================//
        // Server Ws Method
        if ($isExpert) {
            $parameters["WsMethod"] = MageHelper::getStoreConfig('splashsync/core/use_nusoap') ? "NuSOAP" : "SOAP";
        }
        //====================================================================//
        // If Expert Mode => Allow Override of Server Host Address
        if ($isExpert) {
            $serverUrl = MageHelper::getStoreConfig('splashsync/core/host');
            if (!empty($serverUrl)) {
                $parameters["WsHost"] = $serverUrl;
            }
        }
        //====================================================================//
        // Smart Notifications
        $parameters["SmartNotify"] = (bool) MageHelper::getStoreConfig('splashsync/core/smart');
        //====================================================================//
        // Override Webservice Path
        $parameters["ServerPath"] = "/splash/ws/soap";

        return $parameters;
    }

    /**
     * Include Local Includes Files
     *
     *      Include here any local files required by local functions.
     *      This Function is called each time the module is loaded
     *
     *      There may be differents scenarios depending if module is
     *      loaded as a library or as a NuSOAP Server.
     *
     *      This is triggered by global constant SPLASH_SERVER_MODE.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function includes(): bool
    {
        static $isAreaSetupDone;

        //====================================================================//
        // When Library is called in server mode ONLY
        if (defined("SPLASH_SERVER_MODE") && !empty(SPLASH_SERVER_MODE)) {
            if (!isset($isAreaSetupDone)) {
                /** @var State $stateModel */
                $stateModel = MageHelper::getModel(State::class);
                $stateModel->setAreaCode(Area::AREA_WEBAPI_SOAP);
                $isAreaSetupDone = true;
            }
            $this->loadLocalUser();
            MageHelper::getStoreManager()->setCurrentStore(0);
        }

        //====================================================================//
        //  Load Local Translation File
        Splash::translator()->load("main@local");

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function selfTest(): bool
    {
        //====================================================================//
        //  Load Local Translation File
        Splash::translator()->load("main@local");

        //====================================================================//
        //  Verify - Core Parameters
        if (!self::selfTestCoreParameters()) {
            return false;
        }
//
//        //====================================================================//
//        //  Verify - Products Parameters
//        if (!self::selfTestProductsParameters()) {
//            return false;
//        }

        return Splash::log()->msg("Self Test Passed");
    }

    /**
     * {@inheritDoc}
     */
    public function informations($informations): ArrayObject
    {
        //====================================================================//
        // Init Response Object
        $response = $informations;

        //====================================================================//
        // Server General Description
        $response->shortdesc = "Splash Module for Magento 1";
        $response->longdesc = "Splash SOAP Connector Module for Magento 1.";

        //====================================================================//
        // Company Informations
        $response->company = MageHelper::getStoreConfig('general/store_information/name');
        $response->address = MageHelper::getStoreConfig('general/store_information/street_line1');
        $response->zip = MageHelper::getStoreConfig('general/store_information/postcode');
        $response->town = MageHelper::getStoreConfig('general/store_information/city');
        $response->country = MageHelper::getStoreConfig('general/store_information/country_id');
        $response->www = MageHelper::getStoreConfig('web/secure/base_url')
            ?: MageHelper::getStoreConfig('web/unsecure/base_url');
        $response->email = MageHelper::getStoreConfig('trans_email/ident_general/email');
        $response->phone = MageHelper::getStoreConfig('general/store_information/phone');

        //====================================================================//
        // Get Server Root Path
        /** @var DirectoryList $dirList */
        $dirList = MageHelper::getModel(DirectoryList::class);
        //====================================================================//
        // Server Logo & Images
        $response->icoraw = Splash::file()->readFileContents(
            $dirList->getRoot()."/vendor/magento/module-theme/view/adminhtml/web/favicon.ico"
        );
        $response->logoraw = Splash::file()->readFileContents(
            $dirList->getRoot()."/vendor/splash/magento2/src/Resources/img/magento2-logo.png"
        );

        //====================================================================//
        // Server Informations
        /** @var ProductMetadataInterface $productMetadata */
        $productMetadata = MageHelper::getModel(ProductMetadataInterface::class);
        $response->servertype = "Magento 2 V".$productMetadata->getVersion();
        $response->serverurl = MageHelper::getStoreConfig('web/secure/base_url')
            ?: MageHelper::getStoreConfig('web/unsecure/base_url');

        //====================================================================//
        // Module Informations
        $response->moduleversion = $this->getExtensionVersion().' (Splash Php Core '.SPLASH_VERSION.')';

        return $response;
    }

    //====================================================================//
    // *******************************************************************//
    //  OPTIONAl CORE MODULE LOCAL FUNCTIONS
    // *******************************************************************//
    //====================================================================//

    /**
     * {@inheritDoc}
     */
    public function testParameters(): array
    {
        //====================================================================//
        // Init Parameters Array
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function testSequences($name = null): array
    {
        switch ($name) {
            case "Main":
                return array();
            case "List":
                return array(
                    "Main"
                );
        }

        return array();
    }

    //====================================================================//
    // *******************************************************************//
    // Place Here Any SPECIFIC ro COMMON Local Functions
    // *******************************************************************//
    //====================================================================//

    /**
     * Initiate Local Request User if not already defined
     *
     * @return bool
     */
    public function loadLocalUser(): bool
    {
        static $session;
        if (!isset($session)) {
            /** @var Session $session */
            $session = MageHelper::getModel(Session::class);
        }
        //====================================================================//
        // Verify User Not Already Authenticated
        if ($session->isLoggedIn()) {
            return true;
        }
        //====================================================================//
        // LOAD SPLASH USER
        /** @var User $userModel */
        $userModel = MageHelper::getModel(User::class);
        /** @var User $userModel */
        $user = $userModel->loadByUsername(
            MageHelper::getStoreConfig('splashsync/security/username')
        );
        //====================================================================//
        // Safety Check
        if (empty($user->getId())) {
            return Splash::log()->err("ErrSelfTestNoUser");
        }
        //====================================================================//
        // Authenticate Admin User
        $session->setUser($user);
        $session->processLogin();
        if ($session->isLoggedIn()) {
            /** @var \Magento\Framework\Registry $registry */
            $registry = MageHelper::getModel('Magento\Framework\Registry');
            $registry->register('isSecureArea', true);

            return true;
        }

        return Splash::log()->err("ErrUnableToLoginUser");
    }

    //====================================================================//
    //  Magento Dedicated Parameter SelfTests
    //====================================================================//

//    /**
//     * Verify Langage Parameters are correctly set.
//     *
//     * @return bool
//     */
//    private static function validateLanguageParameters(): bool
//    {
//        //====================================================================//
//        //  Verify - SINGLE LANGUAGE MODE
//        if (empty(MageHelper::getStoreConfig('splashsync_splash_options/langs/multilang'))) {
//            if (empty(MageHelper::getStoreConfig('splashsync_splash_options/langs/default_lang'))) {
//                return Splash::log()->err(
//                    "In single Language mode, You must select a default Language for Multi-lang Fields"
//                );
//            }
//
//            return true;
//        }
//
//        //====================================================================//
//        //  Verify - MULTILANG MODE - ALL STORES HAVE AN ISO LANGUAGE
//        foreach (MageHelper::app()->getWebsites() as $website) {
//            foreach ($website->getStores() as $store) {
//                if (empty($store->getConfig('splashsync_splash_options/langs/store_lang'))) {
//                    return Splash::log()->err(
//                        "Multi-Language mode, You must select a Language for Store: ".$store->getName()
//                    );
//                }
//            }
//        }
//
//        return true;
//    }

//    /**
//     * Check if Bundle Components Price Mode is Enabled
//     *
//     * @return bool
//     */
//    public static function isBundleComponantsPricesMode(): bool
//    {
//        return (bool) MageHelper::getStoreConfig('splashsync_splash_options/advanced/bundle_mode');
//    }

    /**
     * Self Tests - Core Parameters
     *
     * @return bool
     */
    private static function selfTestCoreParameters(): bool
    {
        //====================================================================//
        //  Verify - Server Identifier Given
        if (empty(MageHelper::getStoreConfig('splashsync/core/id'))) {
            return Splash::log()->err("ErrSelfTestNoWsId");
        }

        //====================================================================//
        //  Verify - Server Encrypt Key Given
        if (empty(MageHelper::getStoreConfig('splashsync/core/key'))) {
            return Splash::log()->err("ErrSelfTestNoWsKey");
        }

        //====================================================================//
        //  Verify - Default Language is Given
//        if (empty(MageHelper::getStoreConfig('splashsync_splash_options/core/lang'))) {
//            return Splash::log()->err("ErrSelfTestDfLang");
//        }

        //====================================================================//
        //  Verify - User Selected
//        if (empty(MageHelper::getStoreConfig('splashsync_splash_options/user/login'))
//            || empty(MageHelper::getStoreConfig('splashsync_splash_options/user/pwd'))) {
//            return Splash::log()->err("ErrSelfTestNoUser");
//        }

        //====================================================================//
        //  Verify - FIELDS TRANSLATIONS CONFIG
//        if (!self::validateLanguageParameters()) {
//            return false;
//        }

        return true;
    }

//    /**
//     * Self Tests - Products Parameters
//     *
//     * @return bool
//     */
//    private static function selfTestProductsParameters()
//    {
//        //====================================================================//
//        //  Verify - PRODUCT DEFAULT ATTRIBUTE SET
//        $attributeSetId = Mage::getStoreConfig('splashsync_splash_options/products/attribute_set');
//        if (empty($attributeSetId)) {
//            return Splash::log()->err("No Default Product Attribute Set Selected");
//        }
//        /** @var \Mage_Eav_Model_Entity_Attribute_Set $attributeSetModel */
//        $attributeSetModel = Mage::getModel('eav/entity_attribute_set');
//        if (empty($attributeSetModel->load($attributeSetId))) {
//            return Splash::log()->err("Wrong Product Attribute Set Identifier");
//        }
//        //====================================================================//
//        //  Verify - PRODUCT DEFAULT STOCK
//        $stockId = Mage::getStoreConfig('splashsync_splash_options/products/default_stock');
//        if (empty($stockId)) {
//            return Splash::log()->err("No Default Product Warehouse Selected");
//        }
//        /** @var \Mage_CatalogInventory_Model_Stock $stockModel */
//        $stockModel = Mage::getModel('cataloginventory/stock');
//        if (empty($stockModel->load($stockId))) {
//            return Splash::log()->err("Wrong Product Warehouse Selected");
//        }
//
//        //====================================================================//
//        //  Verify - Product Prices Include Tax Warning
//        if (Mage::getStoreConfig('tax/calculation/price_includes_tax')) {
//            Splash::log()->war(
//                "You selected to store Products Prices Including Tax.
//                It is highly recommended to store Product Price without Tax to work with Splash."
//            );
//        }
//        //====================================================================//
//        //  Verify - Shipping Prices Include Tax Warning
//        if (Mage::getStoreConfig('tax/calculation/shipping_includes_tax')) {
//            Splash::log()->war(
//                "You selected to store Shipping Prices Including Tax.
//                It is highly recommended to store Shipping Price without Tax to work with Splash."
//            );
//        }
//
//        return true;
//    }

    //====================================================================//
    //  Magento Getters & Setters
    //====================================================================//

    /**
     * Get Splash Module Version
     *
     * @return string
     */
    private function getExtensionVersion(): string
    {
        /** @var ModuleListInterface $moduleList */
        $moduleList = MageHelper::getModel(ModuleListInterface::class);
        $module = $moduleList->getOne("SplashSync_Magento2");

        return $module ? $module['setup_version'] : 'Unknown';
    }
}
