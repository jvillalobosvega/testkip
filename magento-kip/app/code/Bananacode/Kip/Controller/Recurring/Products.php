<?php

namespace Bananacode\Kip\Controller\Recurring;

use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Products
 * @package Bananacode\Kip\Controller\Wishlist
 */
class Products extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * @var \Bananacode\Kip\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * @var PageFactory
     */
    private $_resultPageFactory;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    protected $_kip;

    /**
     * Products constructor.
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param \Bananacode\Kip\Model\Layer\Resolver $layerResolver
     * @param PageFactory $resultPageFactory
     * @param \Bananacode\Kip\Block\Main $kip
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        \Bananacode\Kip\Model\Layer\Resolver $layerResolver,
        PageFactory $resultPageFactory,
        \Bananacode\Kip\Block\Main $kip
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_kip = $kip;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        $recurring = $this->_kip->recurringProducts(null, true);

        if (count($recurring) > 0) {
            $this->layerResolver->create(\Bananacode\Kip\Model\Layer\Resolver::CATALOG_LAYER_CATEGORY);

            $page = $this->_resultPageFactory->create();

            $title = 'Habituales';
            if ($title) {
                $page->getConfig()->getTitle()->set($title);
            }
            $description = 'Habituales';
            if ($description) {
                $page->getConfig()->setDescription($description);
            }
            $keywords = 'Habituales';
            if ($keywords) {
                $page->getConfig()->setKeywords($keywords);
            }
            $pageMainTitle = $page->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($title);
            }

            if ($this->getRequest()->isAjax()) {
                $navigation = $this->_view->getLayout()->getBlock('catalog.leftnav');
                $products = $this->_view->getLayout()->getBlock('kip.category.products');
                $result = [
                    'products' => $products->toHtml(),
                    'navigation' => $navigation->toHtml()
                ];
                $this->getResponse()->representJson(json_encode($result));
            } else {
                return $page;
            }
        } else {
            $empty = $this->_url->getUrl('kip/recurring/unfilled');
            $this->getResponse()->setRedirect($empty);
        }
    }
}
