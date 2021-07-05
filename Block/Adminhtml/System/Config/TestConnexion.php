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

// phpcs:disable

namespace SplashSync\Magento2\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Splash\Client\Splash;

/**
 * Admin Button to Test Connexion with Splash Server
 */
class TestConnexion extends Field
{
    /**
     * @param AbstractElement $element
     *
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        $uri = '/splash/ws/test?node='.Splash::configuration()->WsIdentifier;

        $element
            ->setData("value", __('Test Splash Connexion'))
            ->setData("on_click", "javascript: location.href = '".$uri."';")
            ->setData("onclick", "javascript: location.href = '".$uri."';")
        ;

        return parent::render($element);
    }
}
