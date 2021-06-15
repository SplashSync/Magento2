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

/**
 * Magento 2 Orders Address Objects Lists Access
 */
trait ObjectListTrait
{
    /**
     * Return List Of Customer's Address with required filters
     *
     * @param string $filter Filters for Object Listing.
     * @param array  $params Search parameters for result List.
     *
     * @return array $data                 List of all customers main data
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Setup filters
        $filters = array(
            'store_id' => array('in' => array("1"))
        );
        if (!empty($filter) && is_string($filter)) {
            $filters = array(
                "increment_id" => array('like' => '%'.$filter.'%'),
            );
        }
        //====================================================================//
        // Execute Core Object List Function
        return $this->coreObjectsList(array(), (array) $params, $filters);
    }
}
