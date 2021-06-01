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

use Exception;
use Magento\Customer\Model\Data\Address;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Objects\Address as SplashAddress;

/**
 * Magento 2 Customers Address Fields Access
 */
trait AddressTrait
{
    /**
     * @var null|Address
     */
    private $address;

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildAddressFields(): void
    {
        $groupName = "Address";
        //====================================================================//
        // Postal Address 1
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("street_1")
            ->name("Street 1")
            ->Group($groupName)
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isReadOnly()
        ;
        //====================================================================//
        // Postal Address 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("street_2")
            ->name("Street 2")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isReadOnly()
        ;
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("postcode")
            ->name("Zip/Postal Code")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->isReadOnly()
        ;
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("city")
            ->name("City")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "addressLocality")
            ->isReadOnly()
        ;
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->Identifier("country_id")
            ->name("Country ISO")
            ->group($groupName)
            ->isLogged()
            ->microData("http://schema.org/PostalAddress", "addressCountry")
            ->isReadOnly()
        ;
        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->identifier("telephone")
            ->name("Telephone")
            ->microData("http://schema.org/PostalAddress", "telephone")
            ->group($groupName)
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getAddressFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Street Address
            case 'street_1':
                $this->out[$fieldName] = $this->address
                    ? SplashAddress::getStreet($this->address, 0)
                    : null;

                break;
            case 'street_2':
                $this->out[$fieldName] = $this->address
                    ? SplashAddress::getStreet($this->address, 1)
                    : null;

                break;
            //====================================================================//
            // Generic Fields
            case 'postcode':
            case 'city':
            case 'country_id':
            case 'telephone':
                if ($this->address) {
                    $this->getGeneric($fieldName, "address");
                }

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Load Customer Billing Address
     *
     * @param null|string $addressId
     *
     * @return void
     */
    protected function loadAddress(?string $addressId): void
    {
        $this->address = null;

        if ($addressId) {
            try {
                /** @var SplashAddress $address */
                $address = Splash::object("Address");
                $this->address = $address->load($addressId) ?: null;
            } catch (Exception $exception) {
                Splash::log()->err($exception->getMessage());
            }
        }
    }
}
