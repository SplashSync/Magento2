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

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Splash\Client\Splash;

/**
 * Admin Button to Test Connexion with Splash Server
 */
class TestConnexion extends Field
{
    /**
     * TestConnexion constructor.
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(Context $context, array $data = array())
    {
        parent::__construct($context, $data);
        $this->_template = 'SplashSync_Magento2::testButton.phtml';
    }

    /**
     * @param AbstractElement $element
     *
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        /** @phpstan-ignore-next-line */
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $uri = '/splash/ws/test?node='.Splash::configuration()->WsIdentifier;

        /** @phpstan-ignore-next-line */
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            array(
                'id' => 'btnid',
                'label' => __('Test Splash Connexion'),
                'onclick' => "javascript: location.href = '".$uri."';"
            )
        );

        return $button->toHtml();
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
