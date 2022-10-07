<?php

namespace Bananacode\Ls\Observer;

use Exception;
use \Ls\Core\Model\LSR;
use \Ls\Omni\Helper\BasketHelper;
use \Ls\Omni\Helper\OrderHelper;
use Magento\Checkout\Model\Session\Proxy as CheckoutProxy;
use Magento\Customer\Model\Session\Proxy as CustomerProxy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface;

/** Class for order process*/
class OrderObserver implements ObserverInterface
{
    /**
     * @var BasketHelper
     */
    private $basketHelper;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Order
     */
    private $orderResourceModel;

    /**
     * @var LSR
     */
    private $lsr;

    /**
     * @var CustomerProxy
     */
    private $customerSession;

    /**
     * @var CheckoutProxy
     */
    private $checkoutSession;

    /***
     * OrderObserver constructor.
     * @param BasketHelper $basketHelper
     * @param OrderHelper $orderHelper
     * @param LoggerInterface $logger
     * @param Order $orderResourceModel
     * @param LSR $LSR
     * @param CustomerProxy $customerSession
     * @param CheckoutProxy $checkoutSession
     */
    public function __construct(
        BasketHelper $basketHelper,
        OrderHelper $orderHelper,
        LoggerInterface $logger,
        CustomerProxy $customerSession,
        CheckoutProxy $checkoutSession,
        Order $orderResourceModel,
        LSR $LSR
    ) {
        $this->basketHelper       = $basketHelper;
        $this->orderHelper        = $orderHelper;
        $this->logger             = $logger;
        $this->customerSession    = $customerSession;
        $this->checkoutSession    = $checkoutSession;
        $this->orderResourceModel = $orderResourceModel;
        $this->lsr                = $LSR;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        //Check: Bananacode\Kipping\Model\Kipping::comment
        return;
    }
}
