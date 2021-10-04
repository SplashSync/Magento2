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

namespace Splash\Local\Objects\Core;

use Magento\Directory\Model\Currency;
use Splash\Core\SplashCore      as Splash;

/**
 * Magento 2 Core Items Fields Access
 */
trait CoreItemsTrait
{
    /**
     * @var string
     */
    protected static $SHIPPING_LABEL = "__Shipping__";

    /**
     * @var string
     */
    protected static $MONEY_POINTS_LABEL = "__Money_For_Points__";

    /**
     * Build Address Fields using FieldFactory
     */
    protected function buildCoreItemsFields(): void
    {
        //====================================================================//
        // Order Line Label
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->inList("lines")
            ->name("Label")
            ->microData("http://schema.org/partOfInvoice", "name")
            ->association("name@lines", "qty_ordered@lines", "unit_price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Description
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("name")
            ->inList("lines")
            ->name("Description")
            ->microData("http://schema.org/partOfInvoice", "description")
            ->association("name@lines", "qty_ordered@lines", "unit_price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Product identifier
        $this->fieldsFactory()->create((string) self::objects()->Encode("Product", SPL_T_ID))
            ->identifier("product_id")
            ->InList("lines")
            ->Name("Product ID")
            ->MicroData("http://schema.org/Product", "productID")
            ->Association("qty_ordered@lines", "unit_price@lines")
            ->isNotTested()
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Discount
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("discount_percent")
            ->inList("lines")
            ->name("Discount (%)")
            ->microData("http://schema.org/Order", "discount")
            ->association("name@lines", "qty_ordered@lines", "unit_price@lines")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Unit Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("unit_price")
            ->inList("lines")
            ->name("Price")
            ->microData("http://schema.org/PriceSpecification", "price")
            ->association("name@lines", "qty_ordered@lines", "unit_price@lines")
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getShippingLineFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "lines", $fieldName);
        if (!$fieldId || Splash::isDebugMode()) {
            return;
        }
        /** @var Currency $currency */
        $currency = $this->getCurrency();
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            //====================================================================//
            // Order Line Direct Reading Data
            case 'name':
                $data = $this->getShippingDescription();

                break;
            case 'qty':
            case 'qty_ordered':
                $data = 1;

                break;
            case 'discount_percent':
                $data = 0;

                break;
            //====================================================================//
            // Order Line Direct Reading Data
            case 'sku':
                $data = static::$SHIPPING_LABEL;

                break;
            //====================================================================//
            // Order Line Product Id
            case 'product_id':
                $data = null;

                break;
            //====================================================================//
            // Order Line Unit Price
            case 'unit_price':
                $shipTaxPercent = $this->object->getShippingAmount()
                    ? 100 * $this->object->getShippingTaxAmount() / $this->object->getShippingAmount()
                    : 0.0
                ;
                $data = self::prices()->encode(
                    (float)    $this->object->getShippingAmount(),
                    (float)    round($shipTaxPercent, 3),
                    null,
                    $currency->getCurrencyCode(),
                    $currency->getCurrencySymbol(),
                    $currency->getCurrencyCode()
                );

                break;
            default:
                return;
        }
        //====================================================================//
        // Do Fill List with Data
        self::lists()->insert(
            $this->out,
            "lines",
            $fieldName,
            count($this->object->getAllItems()),
            $data
        );

        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field identifier / Name
     *
     * @return void
     */
    protected function getMoneyPointsLineFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        // Check if Money Points where Used
        $fieldId = self::lists()->initOutput($this->out, "lines", $fieldName);
        /** @phpstan-ignore-next-line */
        if (!$fieldId || empty($this->object->getMoneyForPoints())) {
            return;
        }
        //====================================================================//
        // Get Money Points Data
        /** @phpstan-ignore-next-line */
        $pointsUsed = $this->object->getPointsBalanceChange();
        /** @var Currency $currency */
        $currency = $this->getCurrency();
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            //====================================================================//
            // Order Line Direct Reading Data
            case 'sku':
                $value = static::$MONEY_POINTS_LABEL;

                break;
            //====================================================================//
            // Order Line Product Id
            case 'product_id':
                $value = null;

                break;
            //====================================================================//
            // Order Line Direct Reading Data
            case 'name':
                $value = "Money Points" ;

                break;
            case 'qty_ordered':
                $value = $pointsUsed;

                break;
            case 'discount_percent':
                $value = 0;

                break;
            //====================================================================//
            // Order Line Unit Price
            case 'unit_price':
                //====================================================================//
                // Encode Discount Price
                $value = self::prices()->encode(
                    (float)    -1 * abs(0.1),
                    (float)    20,
                    null,
                    $currency->getCurrencyCode(),
                    $currency->getCurrencySymbol(),
                    $currency->getCurrencyCode()
                );

                break;
            default:
                return;
        }
        //====================================================================//
        // Do Fill List with Data
        self::lists()->insert($this->out, "lines", $fieldName, count($this->object->getAllItems()) + 1, $value);

        unset($this->in[$key]);
    }
}
