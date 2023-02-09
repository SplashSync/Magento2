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

use Magento\Customer\Model\Address;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 *  Main Address Fields (required)
 */
trait MainTrait
{
    /**
     * Get Indexed Street Address
     *
     * @param Address|OrderAddressInterface $address
     * @param int                           $index
     *
     * @return null|string
     */
    public static function getStreet($address, int $index): ?string
    {
        $street = $address->getStreet() ?: array();

        return $street[$index] ?? null;
    }

    /**
     * Build Core Fields using FieldFactory
     */
    protected function buildMainFields(): void
    {
        //====================================================================//
        // Street Address 1
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("street_1")
            ->name("Street 1")
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isRequired()
        ;
        //====================================================================//
        // Street Address 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("street_2")
            ->name("Street 2")
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
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
    protected function getMainFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'street_1':
                $this->out[$fieldName] = self::getStreet($this->object, 0);

                break;
            case 'street_2':
                $this->out[$fieldName] = self::getStreet($this->object, 1);

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
    protected function setMainFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'street_1':
                $this->updateStreet(0, $fieldData);

                break;
            case 'street_2':
                $this->updateStreet(1, $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Update Street Address
     *
     * @param int   $index
     * @param mixed $fieldData Field Data
     *
     * @return void
     */
    protected function updateStreet(int $index, $fieldData): void
    {
        if (!is_scalar($fieldData)) {
            return;
        }
        $street = $this->object->getStreet() ?: array();
        if (!isset($street[$index]) || ($street[$index] != $fieldData)) {
            $street[$index] = (string) $fieldData;
            $this->object->setStreet($street);
            $this->needUpdate();
        }
    }
}
