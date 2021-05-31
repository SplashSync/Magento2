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

namespace Splash\Local\Objects\ThirdParty;

/**
 * Magento 2 Customers Addresses Fields Access
 */
trait AddressesTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildAddressesFields(): void
    {
        //====================================================================//
        // Default Billing Address
        $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
            ->identifier("default_billing")
            ->name("Default Billing Address")
            ->isReadOnly()
        ;
        //====================================================================//
        // Default Shipping Address
        $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
            ->identifier("default_shipping")
            ->name("Default Shipping Address")
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
    protected function getAddressesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Customer Full Name
            case 'default_billing':
                $address = $this->object->getDefaultBilling();
                $this->out[$fieldName] = $address
                    ? self::objects()->encode("Address", $address)
                    : null
                ;

                break;
            case 'default_shipping':
                $address = $this->object->getDefaultShipping();
                $this->out[$fieldName] = $address
                    ? self::objects()->encode("Address", $address)
                    : null
                ;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
