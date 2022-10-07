<?php

namespace Bananacode\TaxDocument\Controller\Tax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Lists
 * @package Bananacode\TaxDocument\Controller\Shopping
 */
class Document extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * @var \Bananacode\Kip\Block\Main
     */
    protected $_kip;

    /**
     * @var PageFactory
     */
    private $_resultPageFactory;

    /**
     * Lists constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param \Bananacode\Kip\Block\Main $kip
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        \Bananacode\Kip\Block\Main $kip,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_queryFactory = $queryFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_kip = $kip;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $page = $this->_resultPageFactory->create();

        $breadcrumbsBlock = $page->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'wishlist',
                ['label' => 'Configuración de cuenta', 'link' => '/customer/account/']
            );
            $breadcrumbsBlock->addCrumb(
                'tax-documents',
                ['label' => 'Crédito Fiscal y Exento de IVA',  'readonly' => true]
            );
        }

        return $page;
    }
}
