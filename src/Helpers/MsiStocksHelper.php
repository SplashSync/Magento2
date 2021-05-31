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

use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Inventory\Model\SourceRepository;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySourceDeductionApi\Model\GetSourceItemBySourceCodeAndSku;
use Splash\Client\Splash;
use Throwable;

/**
 * Manage Products Stocks via Magento 2 Msi Inventory System
 */
class MsiStocksHelper
{
    //====================================================================//
    // SIMPLE STOCKS MANAGEMENT
    //====================================================================//

    /**
     * Read Product Stock Item
     *
     * @param int $productId
     *
     * @return int
     */
    public static function getStockLevel(int $productId): int
    {
        /** @var null|StockItemRepository $registry */
        static $registry;

        if (!$registry) {
            /** @var StockItemRepository $registry */
            $registry = MageHelper::getModel(StockItemRepository::class);
        }

        try {
            return (int) $registry->get($productId)->getQty();
        } catch (Throwable $throwable) {
            return 0;
        }
    }

    //====================================================================//
    // SOURCES STOCK ITEMS MANAGEMENT
    //====================================================================//

    /**
     * get List of User Stock Sources
     *
     * @return array
     */
    public static function getAvailableSourcesList(): array
    {
        /**
         * @var null|array $sources
         */
        static $sources;
        /**
         * @var null|SourceRepository $sourceRepository
         */
        static $sourceRepository;

        if (!$sourceRepository) {
            /** @var SourceRepository $sourceRepository */
            $sourceRepository = MageHelper::getModel(SourceRepository::class);
        }

        if (!$sources) {
            $sources = array();
            //====================================================================//
            // Walk on Sources List
            foreach ($sourceRepository->getList()->getItems() as $sourceItem) {
                //====================================================================//
                // Skip Default Source
                if ("default" == $sourceItem->getSourceCode()) {
                    continue;
                }
                $sources[$sourceItem->getSourceCode()] = $sourceItem->getName();
            }
        }

        return $sources;
    }

    /**
     * Read Product Source Stock Level
     *
     * @param string $sourceCode
     * @param string $sku
     *
     * @return int
     */
    public static function getSourceLevel(string $sourceCode, string $sku): int
    {
        $sourceItem = self::getSourceItem($sourceCode, $sku);

        return $sourceItem ? (int) $sourceItem->getQuantity() : 0;
    }

    /**
     * Write Product Source Stock Level
     *
     * @param string $sourceCode
     * @param string $sku
     * @param int    $qty
     *
     * @return bool
     */
    public static function setSourceLevel(string $sourceCode, string $sku, int $qty): bool
    {
        /** @var null|SourceItemsSaveInterface $sourceItemsSave */
        static $sourceItemsSave;

        if (!$sourceItemsSave) {
            /** @var SourceItemsSaveInterface $sourceItemsSave */
            $sourceItemsSave = MageHelper::getModel(SourceItemsSaveInterface::class);
        }

        $sourceItem = self::getSourceItem($sourceCode, $sku);
        if (!$sourceItem) {
            $sourceItem = self::addSourceItem($sourceCode, $sku);
        }
        $sourceItem->setQuantity($qty);

        try {
            $sourceItemsSave->execute(array($sourceItem));
        } catch (Throwable $throwable) {
            return Splash::log()->report($throwable);
        }

        return true;
    }

    /**
     * Get Product Source Stock Item
     *
     * @param string $sourceCode
     * @param string $productId
     *
     * @return null|SourceItemInterface
     */
    private static function getSourceItem(string $sourceCode, string $productId): ?SourceItemInterface
    {
        /** @var null|GetSourceItemBySourceCodeAndSku $registry */
        static $registry;

        if (!$registry) {
            /** @var GetSourceItemBySourceCodeAndSku $registry */
            $registry = MageHelper::getModel(GetSourceItemBySourceCodeAndSku::class);
        }

        try {
            return $registry->execute($sourceCode, $productId);
        } catch (Throwable $throwable) {
            return null;
        }
    }

    /**
     * Create Product Source Stock Item
     *
     * @param string $sourceCode
     * @param string $sku
     *
     * @return SourceItemInterface
     */
    private static function addSourceItem(string $sourceCode, string $sku): SourceItemInterface
    {
        /** @var SourceItemInterface $sourceItem */
        $sourceItem = MageHelper::createModel(SourceItemInterface::class);
        $sourceItem->setSourceCode($sourceCode);
        $sourceItem->setSku($sku);
        $sourceItem->setStatus(1);

        return $sourceItem;
    }
}
