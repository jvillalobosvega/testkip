<?php

namespace Bananacode\Referral\Block\Adminhtml\CustomerEdit\Tab;

class Referrals extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $_template = 'customer/tab/referrals.phtml';

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    private $_tokenFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $_authSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_tokenFactory = $tokenFactory;
        $this->_authSession = $authSession;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }
    public function getTabLabel()
    {
        return __('Referrals');
    }
    public function getTabTitle()
    {
        return __('Referrals');
    }
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }
    public function getTabClass()
    {
        return '';
    }
    public function getTabUrl()
    {
        return '';
    }
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @return \Magento\User\Model\User|null
     */
    private function getCurrentAdminUser()
    {
        return $this->_authSession->getUser();
    }

    /**
     * Get logged in customer token
     * @return mixed
     */
    public function adminToken() {
        $factory = $this->_tokenFactory->create();
        return $factory->createAdminToken($this->getCurrentAdminUser()->getId())->getToken();
    }
}
