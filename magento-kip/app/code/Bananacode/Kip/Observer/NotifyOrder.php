<?php

namespace Bananacode\Kip\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class NotifyOrderFailed
 * @package Bananacode\Kip\Observer
 */
class NotifyOrderFailed implements ObserverInterface
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
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * NotifyOrder constructor.
     * @param \Bananacode\Kip\Helper\Notify $bananaNotify
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Bananacode\Kip\Helper\Notify $bananaNotify,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_bananaNotify = $bananaNotify;
        $this->_orderRepository = $orderRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
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

            /**
             * @var \Exception $ex
             */
            $ex = $observer->getEvent()->getException();

            if ($order && $quote) {
                $customerName = $this->_checkoutSession->getCustomerNameValue();
                $this->_bananaNotify->discord('errors', 'Error al procesar pedido: '. $customerName , 'Error');
                $this->_bananaNotify->discord('errors',  $ex->getMessage(), 'Error');

            }
        } catch (\Exception $e) {}
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
