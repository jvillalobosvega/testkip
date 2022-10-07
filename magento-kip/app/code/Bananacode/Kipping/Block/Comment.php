<?php

namespace Bananacode\Kipping\Block;

/**
 * Class Comment
 * @package Bananacode\Kip\Block
 */
class Comment extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customer;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    protected $_tokenFactory;

    /**
     * Comment constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customer,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory,
        array $data = []
    ) {
        $this->_customer = $customer;
        $this->_tokenFactory = $tokenFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $objectManager->create("Magento\Customer\Model\Session");
        return $this->_customer->getCustomer();
    }

    /**
     * @param null $customer_id
     * @return string
     */
    public function getCustomerToken($customer_id = null)
    {
        $customerToken = $this->_tokenFactory->create();
        return $customerToken->createCustomerToken($customer_id ?? $this->getCustomer()->getId())->getToken();
    }
}
