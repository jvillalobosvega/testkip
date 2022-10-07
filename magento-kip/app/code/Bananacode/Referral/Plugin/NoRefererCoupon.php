<?php

namespace Bananacode\Referral\Plugin;

use Bananacode\Referral\Model\Referral;
use Magento\Checkout\Controller\Cart\CouponPost;

class NoRefererCoupon
{
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
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $_orderCollection;

    /**
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Magento\Customer\Model\Session $customer
     * @param Referral $referralModel
     * @param \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel
     */
    public function __construct(
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Customer\Model\Session $customer,
        Referral $referralModel,
        \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
    ) {
        $this->_referralResourceModel = $referralResourceModel;
        $this->_referralModel = $referralModel;
        $this->_userContext = $userContext;
        $this->_customer = $customer;
        $this->_orderCollection = $orderCollection;
    }

    /**
     * @return false
     */
    private function getCustomerId() {
        return $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false);
    }

    /**
     * @param CouponPost $subject
     * @return false|void
     */
    public function beforeExecute(CouponPost $subject)
    {
        $couponCode = $subject->getRequest()->getParam('remove') == 1
            ? ''
            : trim($subject->getRequest()->getParam('coupon_code'));

        if(!empty($couponCode)) {
            $this->_referralResourceModel->load(
                $this->_referralModel,
                $couponCode,
                'referral_code'
            );

            if ($this->_referralModel->getId()) {
                if($this->_referralModel->getCustomerId() == $this->getCustomerId()) {
                    throw new \Exception('You are not allowed to use your own code.');
                }

                $select = $this->_orderCollection->getConnection()->select();
                $select->from(
                    ['orders' => $this->_orderCollection->getTable('sales_order')],
                    ['orders.entity_id']
                )
                    ->where('orders.customer_id = ?', $this->getCustomerId());
                $orders = $this->_orderCollection->getConnection()->fetchAll($select);
                if(count($orders) > 0) {
                    throw new \Exception('You are not allowed to use this code.');
                }
            }
        }
    }
}
