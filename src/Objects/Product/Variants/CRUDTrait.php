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

use Magento\Catalog\Model\Product;
use Splash\Client\Splash;
use Splash\Local\Helpers\MageHelper;
use Throwable;

trait CRUDTrait
{
    /**
     * Magento Product
     *
     * @var null|Product
     */
    protected $parent;

    /**
     * Magento Product Attributes
     *
     * @var null|array
     */
    protected $configurableAttributes = array();

    /**
     * Load Parent product if Exists
     *
     * @param Product $product
     *
     * @return null|Product
     */
    protected function loadParent(Product $product): ?Product
    {
        //====================================================================//
        // Load Parent Product
        $this->parent = $this->getParentProduct($product->getEntityId());
        //====================================================================//
        // Load Parent Product Attributes Array
        $this->configurableAttributes = $this->parent
            ? $this->getConfigurableManager()->getConfigurableAttributesAsArray($this->parent)
            : array();

        return $this->parent;
    }

    /**
     * Create Configurable Product
     *
     * @param Product $product
     *
     * @return null|Product
     */
    protected function createConfigurableProduct(Product $product): ?Product
    {
        //====================================================================//
        // Search for Existing Parent Product by Child Ids
        $this->parent = $this->identifyParentFromVariants();
        //====================================================================//
        // Parent Product Not Found
        if (!$this->parent) {
            //====================================================================//
            // Create a New Configurable Product Class
            $this->parent = $this->createSimpleProduct();
            /** @phpstan-ignore-next-line */
            $this->parent
                ->setTypeId('configurable')
                ->setVisibility(Product\Visibility::VISIBILITY_NOT_VISIBLE)
                ->setCanSaveConfigurableAttributes(true)
                ->setAffectConfigurableProductAttributes(MageHelper::getStoreConfig('splashsync/products/attribute_set'))
                ->setNewVariationsAttributeSetId(MageHelper::getStoreConfig('splashsync/products/attribute_set'))
            ;
        }
        //====================================================================//
        // Add Product to Child Products
        $this->parent->setAssociatedProductIds(array_merge(
            $this->getChildrenIds($this->parent->getEntityId()),
            array($product->getEntityId())
        ));
        //====================================================================//
        // Save Object
        try {
            return $this->parent->save();
        } catch (Throwable $ex) {
            Splash::log()->report($ex);

            return null;
        }
    }

    /**
     * Load Parent product if Exists
     *
     * @param Product $product
     *
     * @return null|Product
     */
    protected function identifyParent(Product $product): ?Product
    {
        //====================================================================//
        // Load Parent Product
        $this->parent = $this->getParentProduct($product->getEntityId());
        //====================================================================//
        // Load Parent Product Attributes Array
        $this->configurableAttributes = $this->parent
            ? $this->getConfigurableManager()->getConfigurableAttributesAsArray($this->parent)
            : array();

        return $this->parent;
    }

    /**
     * Identify Parent Product from Received Variants
     *
     * @return null|Product
     */
    private function identifyParentFromVariants(): ?Product
    {
        //====================================================================//
        // Search for Existing Parent Product by Child Ids
        if (empty($this->in["variants"]) || !is_iterable($this->in["variants"])) {
            return null;
        }
        //====================================================================//
        // Walk on Product Variants
        foreach ($this->in["variants"] as $variant) {
            //====================================================================//
            // Variant Product Id Exists
            if (!isset($variant["id"]) || empty($variant["id"])) {
                continue;
            }
            //====================================================================//
            // Variant Product Id is Valid
            $productId = self::objects()->id($variant["id"]);
            if (empty($productId)) {
                continue;
            }
            //====================================================================//
            // Load List of Parents Products
            $parents = $this->getConfigurableManager()->getParentIdsByChild((int) $productId);
            if (empty($parents)) {
                continue;
            }
            //====================================================================//
            // Load Parent Product
            try {
                /** @phpstan-ignore-next-line */
                return $this->repository->getById((int)array_shift($parents));
            } catch (\Throwable $exception) {
                Splash::log()->err($exception->getMessage());
            }
        }

        return null;
    }
}
