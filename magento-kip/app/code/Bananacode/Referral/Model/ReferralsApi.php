<?php

namespace Bananacode\Referral\Model;

use Bananacode\Referral\Api\ReferralsApiInterface;
use Bananacode\Referral\Api\ReferralInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ReferralsApi implements ReferralsApiInterface
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
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $_ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule
     */
    private $_ruleResource;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private $_orderCollection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    private $_customerId;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     * @param \Magento\Customer\Model\Session $customer
     * @param Referral $referralModel
     * @param \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\ResourceModel\Rule $ruleResource
     * @param array $data
     */
    public function __construct(
        \Magento\Authorization\Model\CompositeUserContext $userContext,
        \Magento\Customer\Model\Session $customer,
        Referral $referralModel,
        \Bananacode\Referral\Model\ResourceModel\Referral $referralResourceModel,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule $ruleResource,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
         \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_referralResourceModel = $referralResourceModel;
        $this->_referralModel = $referralModel;
        $this->_userContext = $userContext;
        $this->_customer = $customer;
        $this->_ruleFactory = $ruleFactory;
        $this->_ruleResource = $ruleResource;
        $this->_orderCollection = $orderCollection;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerId = null;
    }

    /**
     * GET customer referral data
     *
     * @return mixed
     */
    public function getCustomerReferralData() {

        if(!$this->_scopeConfig->getValue('referrals/settings/enabled')) {
            return false;
        }

        $this->_referralResourceModel->load(
            $this->_referralModel,
            $this->getCustomerId(),
            'customer_id'
        );
        if ($this->_referralModel->getId()) {
            $model = $this->_referralModel;
        } else {
            $model = $this->saveReferralModel();
        }

        $data = $model->getData();
        $ruleType = $this->_scopeConfig->getValue('referrals/settings/coupon_type');
        $amount = floatval($this->_scopeConfig->getValue('referrals/settings/coupon_amount'));
        $amountMax = floatval($this->_scopeConfig->getValue('referrals/settings/coupon_max_amount'));
        $earned = floatval($this->_scopeConfig->getValue('referrals/settings/dollars_earned'));
        $daysExpire = intval($this->_scopeConfig->getValue('referrals/settings/expiration_days'));

        if($ruleType === 'by_percent') {
            $data['coupon_amount'] = 'un ' . $amount . '%';
        } else {
            $data['coupon_amount'] = '$' . $amount;
        }
        $data['coupon_max_amount'] = $amountMax;
        $data['you_win'] = $earned;
        $data['days_expire'] = $daysExpire;

        if(isset($data['referral_code'])) {
            // SPENT
            $select = $this->_orderCollection->getConnection()->select();
            $select->from(
                ['orders' => $this->_orderCollection->getTable('sales_order')],
                [
                    'orders.coupon_code',
                    'orders.created_at',
                    'orders.bn_referral_spent',
                    'orders.bn_referral_earned'
                ]
            )
                ->where('orders.customer_id = ?', $this->getCustomerId());
            $ordersSpent = $this->_orderCollection->getConnection()->fetchAll($select);
            $totalSpent = 0;
            $lastOrder = null;
            foreach ($ordersSpent as $orderSpent) {
                $totalSpent += $orderSpent['bn_referral_spent'];
                $lastOrder = $orderSpent['created_at'];
            }
            $data['total_spent'] = $totalSpent;
            $data['days_left'] = 0;
            if(!isset($data['last_order']) && $lastOrder) {
                $this->saveReferralModel([
                    'last_order' => $lastOrder
                ]);
            } else {
                if($data['last_order']) {
                    $lastOrder = $data['last_order'];
                }
            }
            if($lastOrder) {
                $from = new \DateTime();
                $to = new \DateTime($lastOrder);
                $to->add(\DateInterval::createFromDateString('+' . $daysExpire . ' day'));
                $data['days_left'] = $from->diff($to)->format("%r%a");
                if($data['days_left'] <= 0) {
                    $this->saveReferralModel([
                        'cash' => 0
                    ]);
                }
            }

            // EARNED
            $select = $this->_orderCollection->getConnection()->select();
            $select->from(
                    ['orders' => $this->_orderCollection->getTable('sales_order')],
                    [
                        'orders.coupon_code',
                        'orders.bn_referral_spent',
                        'orders.created_at',
                        'orders.bn_referral_earned'
                    ]
                )
                ->where('orders.coupon_code = ?', $data['referral_code']);
            $ordersReferrers = $this->_orderCollection->getConnection()->fetchAll($select);
            $timesUsed = 0;
            $totalEarned = 0;
            foreach ($ordersReferrers as $ordersReferrer) {
                $timesUsed++;
                $totalEarned += $ordersReferrer['bn_referral_earned'];
            }

            $data['times_used'] = $timesUsed;
            $data['total_earned'] = $totalEarned;
            $data['total_left'] = floatval($model->getCash());
        }

        $data['session'] = 0;
        if($this->_checkoutSession->getReferralPoints()) {
            $data['session'] = $this->_checkoutSession->getReferralPoints();
        }

        $data['share_copies'] = [
            'copy' => str_replace('%1', $data['referral_code'], ($this->_scopeConfig->getValue('referrals/settings/copy_share'))),
            'fb' => str_replace('%1', $data['referral_code'], ($this->_scopeConfig->getValue('referrals/settings/facebook_share'))),
            'wp' => str_replace('%1', $data['referral_code'], ($this->_scopeConfig->getValue('referrals/settings/whatsapp_share'))),
            'mail_subject' => str_replace('%1', $data['referral_code'], ($this->_scopeConfig->getValue('referrals/settings/email_share_subject'))),
            'mail_body' => str_replace('%1', $data['referral_code'], ($this->_scopeConfig->getValue('referrals/settings/email_share_body')))
        ];

        $this->jsonResponse(["status" => 200, "output" => $data]);
    }

    /**
     * GET customer referral data
     *
     * @param $id
     * @return mixed
     */
    public function getCustomerReferralDataAdmin($id) {
        $this->setCustomerId($id);
        return $this->getCustomerReferralData();
    }

    /**
     * SET customer referral cash
     *
     * @param string $id
     * @param float $cash
     * @return mixed
     */
    public function setCustomerReferralCash($id, $cash) {
        $this->setCustomerId($id);
        $this->_referralResourceModel->load(
            $this->_referralModel,
            $this->getCustomerId(),
            'customer_id'
        );
        if ($this->_referralModel->getId()) {
            $this->_referralModel->setCash($cash);
            $this->_referralModel->setLastOrder(strtotime('now'));
            $this->_referralResourceModel->save($this->_referralModel);
        }
        return true;
    }

    /**
     * @param array $data
     * @return Referral|false
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function saveReferralModel($data = [])
    {
        if(!$this->getCustomerId()) {
            return false;
        }

        $this->_referralResourceModel->load(
            $this->_referralModel,
            $this->getCustomerId(),
            'customer_id'
        );

        if ($this->_referralModel->getId()) {
            $this->_referralModel->addData($data);
            $this->_referralModel->isObjectNew(false);
            $this->_referralModel->setDataChanges(true);
            $this->_referralResourceModel->save($this->_referralModel);
        } else {
            $data['customer_id'] = $this->getCustomerId();
            $data['referral_code'] = $this->createCouponCode();
            $this->_referralModel->addData($data);
            $this->_referralModel->isObjectNew(true);
            $this->_referralModel->setDataChanges(true);
            $this->_referralResourceModel->save($this->_referralModel);
        }
        return $this->_referralModel;
    }

    /**
     * @return false
     */
    private function getCustomerId() {
        if(!$this->_customerId) {
            return $this->_customerId = $this->_customer->getId() ?? ($this->_userContext->getUserId() ?? false);
        }
        return $this->_customerId;
    }

    /**
     * @param $id
     */
    private function setCustomerId($id) {
        $this->_customerId = $id;
    }

    /**
     * @return false|\Magento\Customer\Model\Session
     */
    private function getCustomerSession() {
        return $this->_customer->getId() ? $this->_customer : false;
    }

    /**
     * @param $data
     * @return array|false
     */
    private function createCouponCode()
    {
        try {
            $ruleType = $this->_scopeConfig->getValue('referrals/settings/coupon_type');
            $amount = floatval($this->_scopeConfig->getValue('referrals/settings/coupon_amount'));
            $amountMax = floatval($this->_scopeConfig->getValue('referrals/settings/coupon_max_amount'));

            $today = new \DateTime();
            $today->sub(new \DateInterval('P1D')); // Yesterday
            $coupon = strtoupper($this->generateRandomString(6));

            /** @var \Magento\SalesRule\Model\Rule $shoppingCartPriceRule */
            $shoppingCartPriceRule = $this->_ruleFactory->create();

            $shoppingCartPriceRule
                ->setName('REFERRALS - ' . $coupon)
                ->setDescription('REFERRALS - ' . $coupon)
                ->setFromDate($today->format('Y-m-d'))
                ->setToDate(null)
                ->setCustomerGroupIds([$this->getCustomerSession() ? $this->getCustomerSession()->getCustomerGroupId() : '1'])
                ->setIsActive('1')
                ->setStopRulesProcessing('0')
                ->setIsAdvanced('1')
                ->setProductIds(null)
                ->setSortOrder('1')
                ->setSimpleAction($ruleType)
                ->setDiscountAmount($amount)
                ->setMaxDiscount($amountMax)
                ->setDiscountQty(null)
                ->setDiscountStep('0')
                ->setApplyToShipping('0')
                ->setTimesUsed('0')
                ->setIsRss('1')
                ->setWebsiteIds(['1'])
                ->setCouponType(\Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
                ->setCouponCode($coupon)
                ->setUsesPerCoupon('0')
                ->setUsesPerCustomer('1')
                ->setSimpleFreeShipping('0');

            $this->_ruleResource->save($shoppingCartPriceRule);

            return $coupon;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param int $length
     * @return string
     */
    private function generateRandomString($length = 25)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param $response
     */
    private function jsonResponse($response)
    {
        header('Content-Type: application/json');
        echo $response ? json_encode($response) : $response;
        exit;
    }
}
