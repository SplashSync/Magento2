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
 * Magento 2 Customers Main Fields Access
 */
trait MainTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMainFields(): void
    {
        //====================================================================//
        // Gender Type
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("gender")
            ->name("Social title")
            ->microData("http://schema.org/Person", "gender")
            ->description("Social title"." ; 0 => Male // 1 => Female // 2 => Neutral")
            ->addChoice("0", "Male")
            ->addChoice("1", "Female")
            ->addChoice("2", "Not Specified")
            ->isNotTested()
        ;
        //====================================================================//
        // Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("fullname")
            ->name("Full Name")
            ->description("Customer Full Name for Incoices")
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
    protected function getMainFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Customer Full Name
            case 'fullname':
                $this->out[$fieldName] = $this->object->getPrefix()
                    ." ".$this->object->getFirstname()
                    ." ".$this->object->getLastname();

                break;
            //====================================================================//
            // Gender Type
            case 'gender':
                switch ($this->object->getGender()) {
                    case 1:
                        $this->out[$fieldName] = 0;

                        break;
                    case 2:
                        $this->out[$fieldName] = 1;

                        break;
                    default:
                        $this->out[$fieldName] = 2;

                        break;
                }

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
    protected function setMainFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Fields
        switch ($fieldName) {
            //====================================================================//
            // Gender Type
            case 'gender':
                //====================================================================//
                // Convert Gender Type Value to Magento Values
                // Splash Social title ; 0 => Male // 1 => Female // 2 => Neutral
                // Magento Social title ; 1 => Male // 2 => Female
                $fieldData++;
                //====================================================================//
                // Update Gender Type
                $this->setGeneric($fieldName, (int) $fieldData);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
