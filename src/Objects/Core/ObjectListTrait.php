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

use Magento\Catalog\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Splash\Core\SplashCore as Splash;
use Splash\Local\Helpers\MageHelper;

/**
 * Generic Magento Object List Parser
 */
trait ObjectListTrait
{
    /**
     * Return Magento Generic Objects List with required filters
     *
     * @param array      $filters
     * @param array      $params      Search parameters for result List.
     * @param null|array $attrFilters
     *
     * @return array $data                 List of all customers main data
     */
    public function coreObjectsList(array $filters = array(), array $params = array(), array $attrFilters = null): array
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Increase Memory Limit
        ini_set("memory_limit", "-1");
        //====================================================================//
        // Load Magento Model
        /** @var null|AbstractModel $mageModel */
        $mageModel = MageHelper::getModel(static::$modelClass);
        if (empty($mageModel)) {
            Splash::log()->errTrace("Unable to find model ".static::$modelClass);

            return array("meta" => array("count" => 0, "total" => 0));
        }
        //====================================================================//
        // Build Magento Collection
        $collection = $mageModel->getCollection();
        //====================================================================//
        // Setup filters
        if (!empty($filters)) {
            $collection->addFieldToFilter($filters);
        }
        if (!empty($attrFilters) && method_exists($collection, "addFieldToFilter")) {
            foreach ($attrFilters as $attributeName => $condition) {
                $collection->addFieldToFilter($attributeName, $condition);
            }
        } elseif (!empty($attrFilters) && method_exists($collection, "addFieldToSearchFilter")) {
            foreach ($attrFilters as $attributeName => $condition) {
                $collection->addFieldToSearchFilter($attributeName, $condition);
            }
        }
        //====================================================================//
        // Setup Order & Pagination
        $total = $this->paginateCollection($collection, $params);

        return $this->getCollectionData($collection, $total);
    }

    /**
     * Setup Magento Collection Pagination
     *
     * @param AbstractCollection $collection
     * @param array              $params
     *
     * @return int
     */
    private function paginateCollection(object &$collection, array $params): int
    {
        //====================================================================//
        // Setup sort order
        $collection->setOrder(
            empty($params["sortfield"]) ? "entity_id" : $params["sortfield"],
            empty($params["sortorder"]) ? "ASC" : $params["sortorder"]
        );
        //====================================================================//
        // Compute Total Number of Results
        $total = $collection->getSize();
        //====================================================================//
        // Setup Pagination
        $limit = (isset($params["max"]) && ($params["max"] > 0)) ? $params["max"] : 25;
        $offset = (isset($params["offset"]) && ($params["offset"] > 0)) ? $params["offset"] : 0;
        $collection->setPageSize($limit);
        if ($offset) {
            $collection->setCurPage(1 + (int) ($offset / $limit));
        }

        return $total;
    }

    /**
     * Return Magento Collection Data
     *
     * @param AbstractCollection $collection
     * @param int                $total
     *
     * @return array
     */
    private function getCollectionData(object $collection, int $total): array
    {
        //====================================================================//
        // Init Result Array
        $data = array();
        //====================================================================//
        // For each result, read information and add to $Data
        foreach ($collection->getData() as $key => $listObject) {
            //====================================================================//
            // If Objects Define Parser, Parse to Array
            if (method_exists($this, "objectListToArray")) {
                $data[$key] = $this->objectListToArray($listObject);

                continue;
            }
            $data[$key] = $listObject;
            $data[$key]["id"] = $data[$key]["entity_id"];
        }
        //====================================================================//
        // Prepare List result meta infos
        $data["meta"]["current"] = count($data);    // Store Current Number of results
        $data["meta"]["total"] = $total;            // Store Total Number of results
        Splash::log()->deb((count($data) - 1)." Objects Found.");

        return $data;
    }
}
