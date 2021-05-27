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

namespace Splash\Local\Objects\Product\Variants;

use Splash\Client\Splash;

trait VariantTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildVariantsMetaFields()
    {
        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("parent_id")
            ->name("Parent")
            ->group("Meta")
            ->microData("http://schema.org/Product", "isVariationOf")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("parent_sku")
            ->name("Parent SKU")
            ->group("Meta")
            ->microData("http://schema.org/Product", "isVariationOfName")
            ->isReadOnly()
        ;

        //====================================================================//
        // CHILD PRODUCTS INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Product Variation List - Product Link
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->identifier("id")
            ->name("Variants")
            ->inList("variants")
            ->microData("http://schema.org/Product", "Variants")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation List - Product SKU
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("sku")
            ->Name("Variant SKU")
            ->InList("variants")
            ->MicroData("http://schema.org/Product", "VariationName")
            ->isReadOnly()
        ;
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getVariantsParentFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'parent_id':
                $this->out[$fieldName] = $this->parent ? (string) $this->parent->getEntityId() : null;

                break;
            case 'parent_sku':
                $this->out[$fieldName] = $this->parent ? (string) $this->parent->getSku() : null;

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
    protected function getVariantChildFields($key, $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "variants", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Check if Product has Parent
        if (empty($this->parent)) {
            unset($this->in[$key]);

            return;
        }
        //====================================================================//
        // Load Product Variants
        foreach ($this->getChildrenIds($this->parent->getEntityId()) as $index => $productId) {
            //====================================================================//
            // PhpUnit/Travis Mode => Skipp Current Product
            if (!empty(Splash::input('SPLASH_TRAVIS')) && ($productId == $this->getObjectIdentifier())) {
                continue;
            }

            switch ($fieldId) {
                case 'id':
                    $value = self::objects()->encode("Product", $productId);

                    break;
                case 'sku':
                    //====================================================================//
                    // Load Parent Product
                    try {
                        $value = $this->repository->getById((int) $productId)->getSku();
                    } catch (\Throwable $exception) {
                        $value = null;
                    }

                    break;
                default:
                    return;
            }
            //====================================================================//
            // Add Variant Infos
            self::lists()->insert($this->out, "variants", $fieldId, $index, $value);
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
    protected function setVariantsChildFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // Variants Infos are Only Used on Create
        if ("variants" == $fieldName) {
            unset($this->in[$fieldName]);
        }
    }
}
