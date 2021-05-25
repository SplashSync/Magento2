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

namespace SplashSync\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Splash\Client\Splash;
use Splash\Local\Local;

/**
 * OnSaveBefore Event is Triggered after Each Db Save Action
 *
 * Execute Splash Commits for Create/Update Actions
 */
class OnSaveAfter extends AbstractObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        //====================================================================//
        // Filter & Get Object From Event Class
        $object = $this->filterEvent($observer);
        if (is_null($object)) {
            return;
        }
        //====================================================================//
        // Translate Object Type & Comment
        $objectType = self::getObjectType($object);
        $comment = self::getObjectName($object);
        if(empty(self::$action) || empty($objectType)) {
            return;
        }
        //====================================================================//
        // Do Generic Change Commit
        $this->commitChanges($objectType, self::$action, $object->getEntityId(), (string) $comment);
    }
}
