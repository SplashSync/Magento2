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

namespace Splash\Local\Objects\Order;

use Splash\Local\Helpers\MageHelper;

/**
 * Magento 2 Order Main Fields Access
 */
trait MainTrait
{
    /**
     * Build Address Fields using FieldFactory
     */
    protected function buildMainFields(): void
    {
        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Order Total Price HT
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("grand_total_excl_tax")
            ->name("Total (tax excl.)"." (".MageHelper::getCurrency()->getCode().")")
            ->microData("http://schema.org/Invoice", "totalPaymentDue")
            ->isReadOnly()
        ;

        //====================================================================//
        // Order Total Price TTC
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("grand_total")
            ->name("Total (tax incl.)"." (".MageHelper::getCurrency()->getCode().")")
            ->microData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->isReadOnly()
            ->isListed()
        ;

        //====================================================================//
        // ORDER Currency Data
        //====================================================================//

        //====================================================================//
        // Order Currency
        $this->fieldsFactory()->create(SPL_T_CURRENCY)
            ->identifier("order_currency_code")
            ->name("Currency")
            ->microData("https://schema.org/PriceSpecification", "priceCurrency")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Currency
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("base_to_order_rate")
            ->name("Currency Rate")
            ->microData("https://schema.org/PriceSpecification", "priceCurrencyRate")
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
            // PRICE INFORMATIONS
            //====================================================================//
            case 'grand_total_excl_tax':
                $this->out[$fieldName] = $this->object->getSubtotal() + $this->object->getShippingAmount();

                break;
            case 'grand_total':
            case 'order_currency_code':
            case 'base_to_order_rate':
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
    protected function setMainFields(string $fieldName, $data): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // ORDER Currency Data
            //====================================================================//
            case 'order_currency_code':
            case 'base_to_order_rate':
                $this->setGeneric($fieldName, $data);

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }
}
