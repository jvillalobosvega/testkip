<?php

namespace Bananacode\ShoppingList\Block;

/**
 * Class Home
 * @package Bananacode\ShoppingList\Block
 */
class Home extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    protected $_kip;

    /**
     * Home constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bananacode\Kip\Block\Main $kip
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bananacode\Kip\Block\Main $kip,
        array $data = []
    ) {
        $this->_kip = $kip;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $title = 'Listas de búsqueda';
        if ($title) {
            $this->pageConfig->getTitle()->set($title);
        }
        $description = 'Listas de búsqueda';
        if ($description) {
            $this->pageConfig->setDescription($description);
        }
        $keywords = 'Listas de búsqueda';
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
