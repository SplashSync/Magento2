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

namespace Splash\Local\Helpers;

use Magento\Directory\Model\Currency;
use Magento\Sales\Model\Order\Item;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Models\Objects\PricesTrait;

class SalesItemsGetter
{
    use ObjectsTrait;
    use PricesTrait;

    /**
     * Read requested Item Values
     *
     * @param Item     $product
     * @param Currency $currency
     * @param string   $fieldId
     *
     * @return mixed
     */
    public static function getItemsValues(Item $product, Currency $currency, string $fieldId)
    {
        switch ($fieldId) {
            //====================================================================//
            // Order Line Direct Reading Data
            case 'sku':
            case 'name':
                return $product->getData($fieldId);
            //====================================================================//
            // Compute Item Price Discount
            case 'discount_percent':
                return self::getDiscount($product);
            //====================================================================//
            // Qty Always 0 for Bundles, Else Normal Reading
            case 'qty_ordered':
                return $product->getData("has_children") ? 0 : (int) $product->getQtyOrdered();
            //====================================================================//
            // Order Line Product Id
            case 'product_id':
                return self::objects()->encode("Product", $product->getData($fieldId));
            //====================================================================//
            // Order Line Unit Price
            case 'unit_price':
                return self::getPrice($product, $currency);
            default:
                return null;
        }
    }

    /**
     * Read requested Item Discount Pourcent
     *
     * @param Item $orderItem
     *
     * @return float|int
     */
    private static function getDiscount(Item $orderItem)
    {
        if (!empty($orderItem->getData('discount_percent'))) {
            return (float) $orderItem->getData('discount_percent');
        }

        if (($orderItem->getPriceInclTax() > 0) && ($orderItem->getQtyOrdered() > 0)) {
            return (float) 100 * $orderItem->getDiscountAmount()
                / ($orderItem->getPriceInclTax() * $orderItem->getQtyOrdered())
            ;
        }

        return 0;
    }

    /**
     * Read Order Product Price
     *
     * @param Item     $orderItem
     * @param Currency $currency
     *
     * @return array|string
     */
    private static function getPrice(Item $orderItem, Currency $currency)
    {
        //====================================================================//
        // Read Item Regular Price
        $htPrice = (float) $orderItem->getPrice();
        $ttcPrice = null;
        $itemTax = (float) $orderItem->getTaxPercent();
        //====================================================================//
        // Build Price Array
        return self::prices()->encode(
            $htPrice,
            $itemTax,
            $ttcPrice,
            $currency->getCurrencyCode(),
            $currency->getCurrencySymbol(),
            $currency->getCurrencyCode()
        );
    }
}
