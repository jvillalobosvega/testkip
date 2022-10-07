<?php

namespace Bananacode\Referral\Model\Total\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;

class Referral extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @param \Magento\Quote\Model\QuoteValidator $quoteValidator
     */
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->quoteValidator = $quoteValidator;
        $this->_checkoutSession = $checkoutSession;
        $this->setCode('referral_discount');
    }
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if($this->_checkoutSession->getReferralPoints()) {
            $balance = $this->_checkoutSession->getReferralPoints();
        } else {
            $balance = 0;
        }

        //$total->setTotalAmount('referral_discount', $balance);
        //$total->setBaseTotalAmount('referral_discount', $balance);

        $total->addTotalAmount($this->getCode(), $balance);
        $total->addBaseTotalAmount($this->getCode(), $balance);

        $total->setReferralDiscount($balance);
        $total->setBaseReferralDiscount($balance);

        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    public function fetch(Quote $quote, Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => 'Referral Discount',
            'value' => $this->_checkoutSession->getReferralPoints()
        ];
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('Referral Discount');
    }
}
