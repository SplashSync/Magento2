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

namespace Splash\Local\Objects\Product;

use Magento\Catalog\Model\Product;
use Splash\Local\Configurators\ProductConfigurator;

/**
 *  Core Products Fields (required)
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
            "Product",
            new ProductConfigurator()
        );
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->name('Reference - SKU')
            ->isListed()
            ->microData("http://schema.org/Product", "model")
            ->isRequired();
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
            // MAIN INFORMATIONS
            //====================================================================//
            case 'sku':
                $this->getGeneric($fieldName);

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
     * @param mixed  $data      Field Data
     *
     * @return void
     */
    protected function setCoreFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // MAIN INFORMATIONS
            //====================================================================//
            case 'sku':
                $this->setGeneric($fieldName, $data);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
