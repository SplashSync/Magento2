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

namespace Splash\Local\Objects\Core;

use Magento\Framework\Exception\LocalizedException;
use Splash\Local\Helpers\MageHelper;

/**
 *  Magento 2 Website Specific Fields
 */
trait WebsitesTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildWebsitesFields(): void
    {
        //====================================================================//
        // Website Name
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("website")
            ->name("Website")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Website Id
        $this->fieldsFactory()->Create(SPL_T_INT)
            ->identifier("website_id")
            ->name("Website Id")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Store Name
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("store")
            ->name("Store")
            ->group("Meta")
            ->isReadOnly()
        ;
        //====================================================================//
        // Store Id
        $this->fieldsFactory()->Create(SPL_T_INT)
            ->identifier("store_id")
            ->name("Store Id")
            ->group("Meta")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getStoresFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'store':
                try {
                    $store = MageHelper::getStoreManager()->getStore($this->object->getStoreId());
                } catch (LocalizedException $e) {
                    $store = null;
                }
                $this->out[$fieldName] = $store ? $store->getName() : null;

                break;
            case 'store_id':
                $this->out[$fieldName] = $this->object->getStoreId();

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getWebsitesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'website':
                try {
                    $store = MageHelper::getStoreManager()->getStore($this->object->getStoreId());
                    $website = MageHelper::getStoreManager()->getWebsite($store->getWebsiteId());
                } catch (LocalizedException $e) {
                    $website = null;
                }
                $this->out[$fieldName] = $website ? $website->getName() : null;

                break;
            case 'website_id':
                try {
                    $store = MageHelper::getStoreManager()->getStore($this->object->getStoreId());
                    $this->out[$fieldName] = $store->getWebsiteId();
                } catch (LocalizedException $e) {
                    $this->out[$fieldName] = null;
                }

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
