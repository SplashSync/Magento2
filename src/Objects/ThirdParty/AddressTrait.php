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

use Magento\Customer\Model\Address;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Splash\Local\Objects\Address as SplashAddress;

/**
 * Magento 2 Customers Address Fields Access
 */
trait AddressTrait
{
    /**
     * @var null|Address|OrderAddressInterface
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
        // Company Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_company")
            ->name("[A] Company")
            ->group($groupName)
            ->microData("http://schema.org/Organization", "legalName")
            ->isReadOnly()
        ;
        //====================================================================//
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("address_contact_name")
            ->Name("[A] Contact Name")
            ->MicroData("http://schema.org/PostalAddress", "alternateName")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Postal Address 1
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_street_1")
            ->name("[A] Street 1")
            ->Group($groupName)
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isReadOnly()
        ;
        //====================================================================//
        // Postal Address 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_street_2")
            ->name("[A] Street 2")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isReadOnly()
        ;
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_postcode")
            ->name("[A] Zip/Postal Code")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->isReadOnly()
        ;
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_city")
            ->name("[A] City")
            ->group($groupName)
            ->microData("http://schema.org/PostalAddress", "addressLocality")
            ->isReadOnly()
        ;
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->Identifier("address_country_id")
            ->name("[A] Country ISO")
            ->group($groupName)
            ->isLogged()
            ->microData("http://schema.org/PostalAddress", "addressCountry")
            ->isReadOnly()
        ;
        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->identifier("address_telephone")
            ->name("[A] Telephone")
            ->microData("http://schema.org/PostalAddress", "telephone")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Relay Point Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_relay_code")
            ->name("[A] Relay Code")
            ->description("Relay Point Code")
            ->microData("http://schema.org/PostalAddress", "description")
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
        // Detect Address Fields Names
        if (0 !== strpos($fieldName, "address_")) {
            return;
        }
        $fieldId = substr($fieldName, strlen("address_"));

        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            case 'contact_name':
                $this->out[$fieldName] = $this->address
                    ? $this->address->getFirstname()." ".$this->address->getLastname()
                    : null;

                break;
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
            case 'company':
            case 'postcode':
            case 'city':
            case 'country_id':
            case 'telephone':
                if ($this->address) {
                    $this->getGeneric($fieldId, "address");
                    $this->out[$fieldName] = $this->out[$fieldId];
                    unset($this->out[$fieldId]);
                } else {
                    $this->out[$fieldName] = null;
                }

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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getAddressRelayFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Detect Address Fields Names
        if ("address_relay_code" != $fieldName) {
            return;
        }
        $this->out[$fieldName] = null;
        unset($this->in[$key]);
        //====================================================================//
        // No Address Loaded
        if (empty($this->address) || !method_exists($this->address, "getData")) {
            return;
        }
        //====================================================================//
        // Detect Mondial Relay Point Code
        $mrPointCode = $this->address->getData('mondialrelay_pickup_id');
        if (!empty($mrPointCode) && is_scalar($mrPointCode)) {
            $this->out[$fieldName] = (string) $mrPointCode;
        }
    }

    /**
     * Load Customer Main Address
     *
     * @param null|Address $address
     *
     * @return void
     */
    protected function loadMainAddress(?Address $address): void
    {
        $this->address = $address;
    }

    /**
     * Load Order Main Address
     *
     * @param null|OrderAddressInterface $address
     *
     * @return void
     */
    protected function loadOrderAddress(?OrderAddressInterface $address): void
    {
        $this->address = $address;
    }
}
