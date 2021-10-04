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

namespace Splash\Local\Objects\Invoice;

use Magento\Directory\Model\Currency;
use Splash\Local\Helpers\InvoiceItemsGetter;
use Splash\Local\Objects\Core\CoreItemsTrait;

/**
 * Magento 2 Invoice Items Fields Access
 */
trait ItemsTrait
{
    use CoreItemsTrait;

    /**
     * Build Address Fields using FieldFactory
     */
    protected function buildItemsFields(): void
    {
        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("qty")
            ->inList("lines")
            ->name("Quantity")
            ->microData("http://schema.org/QuantitativeValue", "value")
            ->association("name@lines", "qty_ordered@lines", "unit_price@lines")
            ->isRequired()
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field identifier / Name
     *
     * @return void
     */
    protected function getItemsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "lines", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify List is Not Empty
        $products = $this->object->getAllItems();
        if (!is_array($products)) {
            return;
        }
        /** @var Currency $currency */
        $currency = $this->object->getOrder()->getOrderCurrency();
        //====================================================================//
        // Fill List with Data
        foreach ($products as $index => $product) {
            //====================================================================//
            // Do Fill List with Data
            self::lists()->insert(
                $this->out,
                "lines",
                $fieldName,
                $index,
                InvoiceItemsGetter::getItemsValues($product, $currency, $fieldId)
            );
        }
        unset($this->in[$key]);
    }

    /**
     * Get Shipping Description
     *
     * @return string
     */
    protected function getShippingDescription(): string
    {
        return (string) $this->object->getOrder()->getShippingDescription();
    }
}
