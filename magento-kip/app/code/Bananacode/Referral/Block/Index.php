<?php

namespace Bananacode\Referral\Block;

use Bananacode\Referral\Model\Referral;

class Index extends \Magento\Framework\View\Element\Template
{
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
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $title = 'Mis Referidos';
        if ($title) {
            $this->pageConfig->getTitle()->set($title);
        }
        $description = 'Mis Referidos';
        if ($description) {
            $this->pageConfig->setDescription($description);
        }
        $keywords = 'Mis Referidos';
        if ($keywords) {
            $this->pageConfig->setKeywords($keywords);
        }
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }

        return $this;
    }
}
