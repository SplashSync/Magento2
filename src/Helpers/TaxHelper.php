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

use Magento\Tax\Model\Calculation;

class TaxHelper
{
    /**
     * Get Magento Tax Rate from Class Id
     *
     * @param int $taxClassId
     *
     * @return float
     */
    public static function getTaxRateById(int $taxClassId): float
    {
        /** @var Calculation $taxCalculation */
        $taxCalculation = MageHelper::getModel(Calculation::class);
        $taxRequest = $taxCalculation->getRateRequest(null, null, null, MageHelper::getStore());

        return (float)  $taxCalculation->getRate(
            /** @phpstan-ignore-next-line */
            $taxRequest->setProductClassId($taxClassId)
        );
    }

    /**
     * Identify Tax Class Id from Tax Percentile
     *
     * @param float $taxRate
     *
     * @return int
     */
    public static function getBestPriceTaxClass(float $taxRate): int
    {
        //====================================================================//
        // No Tax Rate Applied
        if (0 == $taxRate) {
            return 0;
        }
        //====================================================================//
        // Load Products Tax Rates
        /** @var Calculation $taxCalculation */
        $taxCalculation = MageHelper::getModel(Calculation::class);
        /** @var array<int, float> $availableTaxes */
        /** @phpstan-ignore-next-line */
        $availableTaxes = $taxCalculation->getTaxRates(null, null, null);
        //====================================================================//
        // For Each Tax Class
        $bestId = 0;
        $bestRate = 0;
        foreach ($availableTaxes as $txClassId => $txRate) {
            if (abs($taxRate - $txRate) < abs($taxRate - $bestRate)) {
                $bestId = $txClassId;
                $bestRate = $txRate;
            }
        }

        return $bestId;
    }
}
