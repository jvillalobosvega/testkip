<?php

namespace Bananacode\Kip\Block\Adminhtml\CustomerEdit\Tab;

class Reports extends \Magento\Backend\Block\Template implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $_template = 'customer/tab/reports.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }
    public function getTabLabel()
    {
        return __('Reportes');
    }
    public function getTabTitle()
    {
        return __('Reportes');
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
}
