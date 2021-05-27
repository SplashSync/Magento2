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

use Splash\Core\SplashCore      as Splash;
use Throwable;

/**
 * Magento 2 Core CRUD Functions
 */
trait CRUDTrait
{
    /**
     * @return false|string
     */
    public function getObjectIdentifier()
    {
        if (empty($this->object) || empty($this->object->getEntityId())) {
            return false;
        }

        return $this->object->getEntityId();
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id
     */
    protected function coreUpdate($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        if (!$needed) {
            return $this->object->getEntityId();
        }
        //====================================================================//
        // Update Object
        try {
            $this->object->save();
//            $this->object = $this->repository->save($this->object);
        } catch (Throwable $ex) {
            return Splash::log()->report($ex);
        }
        //====================================================================//
        // Ensure All changes have been saved
        if ($this->object->hasDataChanges()) {
            return Splash::log()->errTrace("Unable to update (".$this->object->getEntityId().").");
        }

        return $this->object->getEntityId();
    }

    /**
     * Generic Delete of requested Object
     *
     * @param null|int $objectId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    protected function coreDelete(int $objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Safety Checks
        if (empty($objectId)) {
            return Splash::log()->err("ErrSchNoObjectId", __CLASS__."::".__FUNCTION__);
        }

        try {
            //====================================================================//
            // Load Object From DataBase
            $object = $this->repository->getById($objectId);
        } catch (Throwable $exception) {
            return true;
        }

        try {
            //====================================================================//
            // Delete Object From DataBase
            $this->repository->delete($object);
        } catch (Throwable $exception) {
            return Splash::log()->err($exception->getMessage());
        }

        return true;
    }
}
