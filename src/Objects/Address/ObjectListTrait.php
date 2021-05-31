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

namespace Splash\Local\Objects\Address;

/**
 * Magento 2 Customers Address Objects Lists Access
 */
trait ObjectListTrait
{
    /**
     * Return List Of Products with required filters
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
            //            array('attribute' => 'type_id',     'nin' => array("configurable"))
        );
        if (!empty($filter) && is_string($filter)) {
            $filters = array(
                array('attribute' => 'firstname',   'like' => "%".$filter."%"),
                array('attribute' => 'lastname',    'like' => "%".$filter."%"),
                array('attribute' => 'street',      'like' => "%".$filter."%"),
                array('attribute' => 'city',        'like' => "%".$filter."%"),
                array('attribute' => 'postcode',    'like' => "%".$filter."%"),
            );
        }
        //====================================================================//
        // Execute Core Object List Function
        return $this->coreObjectsList($filters, (array) $params);
    }
}
