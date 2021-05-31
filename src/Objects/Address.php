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

namespace   Splash\Local\Objects;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address as MageAddress;
use Magento\Customer\Model\Data\Address as MageAddressData;
use Splash\Local\Helpers\MageHelper;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\GenericFieldsTrait;
use Splash\Models\Objects\IntelParserTrait;

/**
 * Splash PHP Module For Magento 2 - ThirdParty Address Object Integration
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Address extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use GenericFieldsTrait;

    // Core / Common Traits
    use Core\CRUDTrait;
    use Core\ObjectListTrait;

    // Address Fields
    use Address\CRUDTrait;
    use Address\ObjectListTrait;
    use Address\CoreTrait;
    use Address\MainTrait;

    // Core EAV Parser
    use Core\EavParserTrait;

    //====================================================================//
    // Magento Definition
    //====================================================================//

    /**
     * Magento Model Name
     *
     * @var class-string
     */
    protected static $modelClass = MageAddress::class;

    /**
     * @var AddressRepositoryInterface
     */
    protected $repository;

    /**
     * Magento Model
     *
     * @var MageAddressData
     */
    protected $object;

    /**
     * Magento Model Name
     *
     * @var string
     */
    protected static $modelName = 'customer/address';

    /**
     * Magento Model List Attributes
     *
     * @var array
     */
    protected static $listAttributes = array('entity_id', 'company', 'firstname', 'lastname', 'city');

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME = "Address";

    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION = "Magento 1 Customers Address Object";

    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO = "fa fa-envelope-o";

    //====================================================================//
    // Object Synchronization Recommended Configuration
    //====================================================================//

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * @var bool
     */
    protected static $ENABLE_PUSH_CREATED = false;

    /**
     * Enable Update Of Existing Local Objects when Modified Remotely
     *
     * @var bool
     */
    protected static $ENABLE_PUSH_UPDATED = false;

    /**
     * Enable Delete Of Existing Local Objects when Deleted Remotely
     *
     * @var bool
     */
    protected static $ENABLE_PUSH_DELETED = false;

    /**
     * Splash Product constructor.
     */
    public function __construct()
    {
        /** @var AddressRepositoryInterface $repository */
        $repository = MageHelper::getModel(AddressRepositoryInterface::class);
        $this->repository = $repository;
    }
}
