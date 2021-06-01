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
use Splash\Client\Splash;
use Splash\Local\Helpers\MageHelper;
use Throwable;

/**
 * Magento 2 Product CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return false|Product
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Safety Checks
        if (empty($objectId)) {
            return Splash::log()->errTrace("Missing Product Id.");
        }
        //====================================================================//
        // Load Product
        try {
            /** @var Product $product */
            $product = $this->repository->getById((int) $objectId, true, 0);
        } catch (\Throwable $exception) {
            return Splash::log()->err($exception->getMessage());
        }
        //====================================================================//
        // Load Parent Product
        $this->loadParent($product);

        return $product;
    }

    /**
     * Create Request Object
     *
     * @return false|object New Object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Required Fields
        if (!$this->verifyRequiredFields()) {
            return false;
        }
        //====================================================================//
        // Init Simple Product Class
        $product = $this->createSimpleProduct();
        //====================================================================//
        // Save Object
        try {
            /** @var Product $product */
            $product = $this->repository->save($product);
        } catch (Throwable $ex) {
            return Splash::log()->report($ex);
        }
        //====================================================================//
        // Init Configurable Product Class
        if (!empty($this->in["attributes"])) {
            $this->createConfigurableProduct($product);
        }

        return $this->load($product->getEntityId());
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id
     */
    public function update($needed)
    {
        return $this->coreUpdate($needed);
    }

    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Search for Parent Product
        $parent = $this->getParentProduct((int) $objectId);
        //====================================================================//
        // Execute Generic Magento Delete Function ...
        $deleteResult = $this->coreDelete((int) $objectId, false);
        //====================================================================//
        // If We are Deleting the Last Simple Product of a Configurable
        if ($parent && empty($this->getChildrenIds($parent->getEntityId()))) {
            $this->coreDelete($parent->getEntityId(), false);
        }

        return $deleteResult;
    }

    /**
     * Create a Simple Product
     *
     * @return Product
     */
    protected function createSimpleProduct(): Product
    {
        //====================================================================//
        // Init Product Class
        /** @var Product $product */
        $product = MageHelper::createModel(Product::class);
        $product
            // Setup Product Status
            ->setStatus(0)
            ->setPrice(0)
            ->setData("tax_class_id", 0)
            ->setWeight(1)
            ->setVisibility(Product\Visibility::VISIBILITY_BOTH)
            // Setup Product Attribute Set
            ->setAttributeSetId(MageHelper::getStoreConfig('splashsync/products/attribute_set'))
            // Setup Product Type => Always Simple when Created formOutside Magento
            ->setTypeId("simple")
            // Setup Product SKU & Name
            ->setData("sku", $this->in["sku"])
            ->setData("name", $this->in["name"])
        ;

        return $product;
    }
}
