<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Bananacode\Kipping\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Adds reCaptcha configuration to checkout.
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Bananacode\Kip\Helper\Map
     */
    private $_mapHelper;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * CheckoutConfigProvider constructor.
     * @param \Bananacode\Kip\Helper\Map $mapHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Bananacode\Kip\Helper\Map $mapHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_mapHelper = $mapHelper;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'kipping' => [
                "key" => $this->_mapHelper->getKey(),
                "polygon" => $this->_mapHelper->getPolygon(),
                "polygon_restricted" => $this->_mapHelper->getPolygonRestricted(),
                "center" => $this->_mapHelper->getCenter(),
                "express" => [
                    "active" => $this->getCoreConfig('_express/active'),
                    "title" => $this->getCoreConfig('_express/title'),
                    "disclaimer" => $this->getCoreConfig('_express/disclaimer'),
                    "price" => $this->getCoreConfig('_express/price')
                ],
                "flash" => [
                    "active" => $this->getCoreConfig('_flash/active'),
                    "title" => $this->getCoreConfig('_flash/title'),
                    "disclaimer" => $this->getCoreConfig('_flash/disclaimer'),
                    "price" => $this->getCoreConfig('_flash/price')
                ],
                "scheduled" => [
                    "active" => $this->getCoreConfig('_scheduled/active'),
                    "title" => $this->getCoreConfig('_scheduled/title'),
                    "disclaimer" => $this->getCoreConfig('_scheduled/disclaimer'),
                    "price" => $this->getCoreConfig('_scheduled/price')
                ],
                "scheduled_today" => [
                    "active" => $this->getCoreConfig('_scheduled_today/active'),
                    "title" => $this->getCoreConfig('_scheduled_today/title'),
                    "disclaimer" => $this->getCoreConfig('_scheduled_today/disclaimer'),
                    "price" => $this->getCoreConfig('_scheduled_today/price')
                ],
                "scheduled_add" => [
                    "active" => $this->getCoreConfig('_scheduled_add/active'),
                    "title" => $this->getCoreConfig('_scheduled_add/title'),
                    "disclaimer" => $this->getCoreConfig('_scheduled_add/disclaimer'),
                    "price" => $this->getCoreConfig('_scheduled_add/price')
                ],
                "scheduled_festivity" => [
                    "active" => $this->getCoreConfig('_scheduled_festivity/active'),
                    "title" => $this->getCoreConfig('_scheduled_festivity/title'),
                    "disclaimer" => $this->getCoreConfig('_scheduled_festivity/disclaimer'),
                    "price" => $this->getCoreConfig('_scheduled_festivity/price')
                ]
            ]
        ];
    }

    /**
     * @param $config
     * @return mixed
     */
    public function getCoreConfig($config)
    {
        return $this->_scopeConfig->getValue(
            'carriers/kipping' . $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
