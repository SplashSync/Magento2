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

use Splash\Local\Helpers\MsiStocksHelper;

/**
 * Product Stock Fields
 */
trait StockTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildStockFields(): void
    {
        //====================================================================//
        // Stock Reel
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("quantity_and_stock_status")
            ->name("[Default] Stock")
            ->microData("http://schema.org/Offer", "inventoryLevel")
        ;
        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("is_in_stock")
            ->name("This product is out of stock")
            ->microData("http://schema.org/ItemAvailability", "OutOfStock")
            ->isReadOnly()
        ;
    }

    /**
     * Build Fields using FieldFactory
     */
    protected function buildStockSourcesFields(): void
    {
        foreach (MsiStocksHelper::getAvailableSourcesList() as $srcCode => $srcName) {
            //====================================================================//
            // Stock Reel
            $this->fieldsFactory()->create(SPL_T_INT)
                ->identifier("src_stock_".$srcCode)
                ->name("[".$srcName."] Stock")
                ->microData("http://schema.org/Offer", "inventoryLevel".ucfirst($srcCode))
            ;
        }
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getStockFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'quantity_and_stock_status':
                $this->out[$fieldName] = MsiStocksHelper::getStockLevel($this->object->getEntityId());

                break;
            case 'is_in_stock':
                $this->out[$fieldName] = !empty(MsiStocksHelper::getStockLevel($this->object->getEntityId()));

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
    protected function getStockSourcesFields(string $key, string $fieldName): void
    {
        foreach (array_keys(MsiStocksHelper::getAvailableSourcesList()) as $sourceCode) {
            if ($fieldName == ("src_stock_".$sourceCode)) {
                $this->out[$fieldName] = MsiStocksHelper::getSourceLevel($sourceCode, $this->object->getSku());
                unset($this->in[$key]);
            }
        }
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $data      Field Data
     *
     * @return void
     */
    protected function setStockFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'quantity_and_stock_status':
                $stockLevel = MsiStocksHelper::getStockLevel($this->object->getEntityId());
                if ($stockLevel == $data) {
                    break;
                }
                $this->object->setQuantityAndStockStatus(array(
                    'qty' => (int) $data,
                    'is_in_stock' => empty($data) ? 0 : 1,
                ));
                $this->needUpdate();

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $data      Field Data
     *
     * @return void
     */
    protected function setStockSourcesFields(string $fieldName, $data): void
    {
        foreach (array_keys(MsiStocksHelper::getAvailableSourcesList()) as $sourceCode) {
            if ($fieldName == ("src_stock_".$sourceCode)) {
                $sourceLevel = MsiStocksHelper::getSourceLevel($sourceCode, $this->object->getSku());
                if ($sourceLevel != $data) {
                    MsiStocksHelper::setSourceLevel($sourceCode, $this->object->getSku(), (int) $data);
                }

                unset($this->in[$fieldName]);
            }
        }
    }
}
