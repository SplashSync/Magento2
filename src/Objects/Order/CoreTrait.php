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

namespace Splash\Local\Objects\Order;

use Exception;
use Splash\Local\Configurators\OrderConfigurator;
use Splash\Local\Helpers\DateHelper;

/**
 * Magento 2 Order Core Fields Access
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // Register Order Configurator
        $this->fieldsFactory()->registerConfigurator(
            "Order",
            new OrderConfigurator()
        );
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("increment_id")
            ->name('Reference')
            ->microData("http://schema.org/Order", "orderNumber")
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Customer Object
        $this->fieldsFactory()->create((string) self::objects()->encode("ThirdParty", SPL_T_ID))
            ->identifier("customer_id")
            ->name('Customer')
            ->microData("http://schema.org/Organization", "ID")
            ->isRequired()
            ->isReadOnly()
        ;
        //====================================================================//
        // Customer Email
        $this->fieldsFactory()->create(SPL_T_EMAIL)
            ->identifier("customer_email")
            ->name("Email du contact")
            ->microData("http://schema.org/ContactPoint", "email")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipping Method
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_method")
            ->name("Shipping Method")
            ->microData("http://schema.org/ParcelDelivery", "identifier")
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Order Shipping Method Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_description")
            ->name("Shipping Description")
            ->microData("http://schema.org/ParcelDelivery", "description")
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Order Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->identifier("created_at")
            ->name("Date")
            ->microData("http://schema.org/DataFeedItem", "dateCreated")
            ->isReadOnly()
            ->isListed()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field identifier / Name
     *
     * @throws Exception
     *
     * @return void
     */
    protected function getCoreFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Direct Readings
            case 'increment_id':
            case 'customer_email':
            case 'shipping_method':
            case 'shipping_description':
                $this->getGeneric($fieldName);

                break;
            //====================================================================//
            // Customer Object Id Readings
            case 'customer_id':
                $this->out[$fieldName] = self::objects()->encode("ThirdParty", $this->object->getData($fieldName));

                break;
            //====================================================================//
            // Order Official Date
            case 'created_at':
                $this->out[$fieldName] = DateHelper::toSplash((string) $this->object->getData($fieldName));

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
