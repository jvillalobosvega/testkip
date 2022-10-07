<?php

namespace Bananacode\Kip\Block\Category;

/**
 * Class View
 * @package Bananacode\Kip\Block\Product
 */
class View extends \Magento\Catalog\Block\Category\View
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    protected $_kip;

    /**
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bananacode\Kip\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Bananacode\Kip\Block\Main $kip
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bananacode\Kip\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Bananacode\Kip\Block\Main $kip
    ) {
        $this->_kip = $kip;
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        parent::__construct($context, $layerResolver, $registry, $categoryHelper);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $l = $this->_kip->currentWishList();
        $lists = $this->_kip->wishLists(false, true);
        if (isset($lists[$l])) {
            $title = 'Mis Productos Favoritos: ' . $lists[$l]['value'];
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }
            $description = 'Mi lista de favoritos: ' . $lists[$l]['value'];
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $lists[$l]['value'];
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($title);
            }
        } else {
            $title = 'Mis Recetas Favoritas';
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }
                $description = 'Mis recetas favoritas';
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = 'Mis Recetas Favoritas';
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($title);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [];
    }
}
