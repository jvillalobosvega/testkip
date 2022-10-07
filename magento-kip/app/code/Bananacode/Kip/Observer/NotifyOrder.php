<?php

namespace Bananacode\Kip\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class NotifyOrder
 * @package Bananacode\Kip\Observer
 */
class NotifyOrder implements ObserverInterface
{
    /**
     * @var \Bananacode\Kip\Helper\Notify
     */
    protected $_bananaNotify;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * NotifyOrder constructor.
     * @param \Bananacode\Kip\Helper\Notify $bananaNotify
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Bananacode\Kip\Helper\Notify $bananaNotify,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_bananaNotify = $bananaNotify;
        $this->_orderRepository = $orderRepository;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @return bool|void
     */
    public function execute(Observer $observer)
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $order = $observer->getEvent()->getOrder();                        
            /**
             * @var \Magento\Quote\Model\Quote $quote
             */
            $quote = $observer->getEvent()->getQuote();
                                        
            if ($order && $quote) {
                $this->_bananaNotify->discord('sales',":red_circle: ╭✧--------------------------------------  *NUEVA ORDEN*  --------------------------------------", 'Nueva Orden');
                // $this->_bananaNotify->discord('sales', 'Nueva orden con ID en magento #' . $order->getIncrementId() . ' Geo:' .  $lat.','. $lng . ' Dirección: ' . $address, 'Nueva Orden');
                $this->_bananaNotify->discord('sales', 'Nueva orden con ID en magento **#' . $order->getIncrementId().'**', 'Nueva Orden');
            }
        } catch (\Exception $e) {
            
        }
    }

    /**
     * @param $config
     * @return mixed
     */
    private function getConfig($config)
    {
        return $this->_scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
