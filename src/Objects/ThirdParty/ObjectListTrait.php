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

namespace Splash\Local\Objects\ThirdParty;

use Splash\Local\Helpers\AccessHelper;

/**
 * Magento 2 Customers Objects Lists Access
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
            array('attribute' => 'store_id',     'in' => AccessHelper::getAllowedStores())
        );
        if (!empty($filter) && is_string($filter)) {
            $filters = array(
                array('attribute' => 'email',       'like' => "%".$filter."%"),
                array('attribute' => 'firstname',   'like' => "%".$filter."%"),
                array('attribute' => 'lastname',    'like' => "%".$filter."%"),
            );
        }
        //====================================================================//
        // Execute Core Object List Function
        return $this->coreObjectsList($filters, (array) $params);
    }
}
