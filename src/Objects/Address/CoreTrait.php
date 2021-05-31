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

namespace Splash\Local\Objects\Address;

use Splash\Local\Configurators\AddressConfigurator;

/**
 *  Core Address Fields (required)
 */
trait CoreTrait
{
    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // Register Product Configurator
        $this->fieldsFactory()->registerConfigurator(
            "Address",
            new AddressConfigurator()
        );
        //====================================================================//
        // Customer
        $this->fieldsFactory()->Create((string) self::objects()->encode("ThirdParty", SPL_T_ID))
            ->identifier("customer_id")
            ->name("Customer")
            ->microData("http://schema.org/Organization", "ID")
            ->isRequired()
        ;
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->identifier("country_iso")
            ->name("Country ISO (Code)")
            ->microData("http://schema.org/PostalAddress", "addressCountry")
            ->isRequired()
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
    protected function getCoreFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Customer Object Id Readings
            case 'customer_id':
                $customerId = $this->object->getCustomerId();
                $this->out[$fieldName] = $customerId
                    ? self::objects()->encode("ThirdParty", $customerId)
                    : null
                ;

                break;
            //====================================================================//
            // ISO Country Code
            case 'country_iso':
                $this->out[$fieldName] = $this->object->getCountryId();

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setCoreFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // Customer Object Id Writings
            case 'customer_id':
                //====================================================================//
                // Decode Customer Id
                $customerId = self::objects()->id($fieldData);
                //====================================================================//
                // Check For Change
                if ($customerId && ($customerId != $this->object->getCustomerId())) {
                    $this->object->setCustomerId((int) $customerId);
                    $this->needUpdate();
                }

                break;
            //====================================================================//
            // ISO Country Code
            case 'country_iso':
                $this->setGeneric("countryId", $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
