<?php

namespace Bananacode\Referral\Observer;

use Bananacode\Referral\Model\Referral;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CheckReferralPlaceOrder
 * @package Bananacode\Referral\Observer
 */
class CheckReferralPlaceOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Bananacode\Referral\Model\ResourceModel\Referral
     */
    private $_referralResourceModel;

    /**
     * @var Referral
     */
    private $_referralModel;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    private $_userContext;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customer;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $_orderRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Magento\Customer\Model\Session $customer
     * @param Referral $referralModel
     * @param \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Customer\Model\Session $customer,
        Referral $referralModel,
        \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_referralResourceModel = $referralResourceModel;
        $this->_referralModel = $referralModel;
        $this->_userContext = $userContext;
        $this->_customer = $customer;
        $this->_orderRepository = $orderRepository;
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

            if ($order && $quote) {
                $newCash = null;

                $couponCode = $order->getCouponCode();
                if(!empty($couponCode)) {
                    $this->_referralResourceModel->load(
                        $this->_referralModel,
                        $couponCode,
                        'referral_code'
                    );
                    if ($this->_referralModel->getId()) {
                        $earned = floatval($this->getConfig('referrals/settings/dollars_earned'));
                        $order->setBnReferralEarned($earned);
                        $currentCash = floatval($this->_referralModel->getCash());
                        $newCash = $currentCash + $earned;
                    }
                }

                $referralPointsUsed = $this->_checkoutSession->getReferralPoints();
                if(is_numeric($referralPointsUsed)) {
                    if($referralPointsUsed !== 0) {
                        $this->_referralResourceModel->load(
                            $this->_referralModel,
                            $order->getCustomerId(),
                            'customer_id'
                        );
                        if ($this->_referralModel->getId()) {
                            $order->setBnReferralSpent($referralPointsUsed);
                            $currentCash = floatval($this->_referralModel->getCash());
                            $newCash = $currentCash + $referralPointsUsed;
                        }
                    }
                }

                if($newCash) {
                    $this->_referralModel->setCash($newCash);
                    $this->_referralResourceModel->save($this->_referralModel);
                }

                $this->_orderRepository->save($order);
                $this->_checkoutSession->setReferralPoints(0);
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
