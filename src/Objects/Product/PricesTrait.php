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

use Exception;
use Splash\Local\Helpers\MageHelper;
use Splash\Local\Helpers\TaxHelper;

/**
 * Products Prices Fields Access
 */
trait PricesTrait
{
    /**
     * Build Fields using FieldFactory
     */
    protected function buildPricesFields(): void
    {
        $currencyCode = MageHelper::getStore()->getDefaultCurrencyCode();
        //====================================================================//
        // Product Selling Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price")
            ->name("Selling Price"." (".$currencyCode.")")
            ->group("Pricing")
            ->microData("http://schema.org/Product", "price")
        ;
        //====================================================================//
        // Product Cost Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("cost")
            ->name("Cost Price"." (".$currencyCode.")")
            ->group("Pricing")
            ->microData("http://schema.org/Product", "wholesalePrice")
        ;
        //====================================================================//
        // Product Manufacturer's Suggested Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("msrp")
            ->name("MSRP Price"." (".$currencyCode.")")
            ->description("Manufacturer's Suggested Retail Price")
            ->group("Pricing")
            ->microData("http://schema.org/Product", "suggestedPrice")
        ;
        //====================================================================//
        // Product Minimal Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("minimal_price")
            ->name("Minimal Price"." (".$currencyCode.")")
            ->description("Minimal Price")
            ->group("Pricing")
            ->microData("http://schema.org/Product", "minPrice")
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
    protected function getPricesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'price':
            case 'cost':
            case 'msrp':
            case 'minimal_price':
                $this->out[$fieldName] = self::toProductPrice(
                    (float) $this->object->getData($fieldName),
                    (int) $this->object->getData("tax_class_id")
                );

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
    protected function setProductPriceFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'price':
            case 'cost':
            case 'msrp':
            case 'minimal_price':
                //====================================================================//
                // Read Current Price
                $currentPrice = self::toProductPrice(
                    (float) $this->object->getData($fieldName),
                    (int) $this->object->getData("tax_class_id")
                );
                //====================================================================//
                // Compare Prices
                if (self::prices()->compare($currentPrice, $fieldData)) {
                    return;
                }
                //====================================================================//
                // Update Prices
                $oldPrice = (float)  self::prices()->taxExcluded($currentPrice);
                $newPrice = (float)  self::prices()->taxExcluded($fieldData);
                if (abs($oldPrice - $newPrice) > 1E-6) {
                    $this->object->setData($fieldName, $newPrice);
                    $this->needUpdate();
                }
                //====================================================================//
                // Update Product Tax Class
                if ("price" == $fieldName) {
                    $newTaxId = TaxHelper::getBestPriceTaxClass((float) self::prices()->taxPercent($fieldData));
                    if ($this->object->getData("tax_class_id") != $newTaxId) {
                        $this->object->setData("tax_class_id", $newTaxId);
                        $this->needUpdate();
                    }
                }

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Encode Magento Product Price
     *
     * @param float       $priceHT
     * @param int         $taxClassId
     * @param null|string $code
     * @param null|string $symbol
     *
     * @throws Exception
     *
     * @return array
     */
    protected static function toProductPrice(
        float $priceHT,
        int $taxClassId,
        ?string $code = null,
        ?string $symbol = null
    ): array {
        //====================================================================//
        // Build Price Array
        $price = self::prices()->encode(
            $priceHT,
            (float) TaxHelper::getTaxRateById($taxClassId),
            null,
            $code ?: MageHelper::getCurrency()->getCurrencyCode(),
            $symbol ?: MageHelper::getCurrency()->getCurrencySymbol(),
            $code ?: MageHelper::getCurrency()->getCurrencyCode()
        );
        //====================================================================//
        // Safety Check
        if (!is_array($price)) {
            throw new Exception("An Error Occurred on Price generation");
        }

        return $price;
    }
}
