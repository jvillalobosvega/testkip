<?php

namespace Bananacode\TaxDocument\Controller\Adminhtml\Review;

use Magento\Backend\App\Action;

/**
 * Class Approve
 * @package Bananacode\TaxDocument\Controller\Adminhtml\Review
 */
class Approve extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bananacode_TaxDocument::config');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Bananacode_TaxDocument::taxdocument')
            ->addBreadcrumb(__('Documento Fiscal'), __('Documento Fiscal'))
            ->addBreadcrumb(__('Aprobar Documentos Fiscales'), __('Aprobar Documentos Fiscales'));
        return $resultPage;
    }

    /**
     * Approve Doc Page
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('document_id');
        $model = $this->_objectManager->create('Bananacode\TaxDocument\Model\TaxDocument');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This tax document no longer exits. '));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('taxdocument_review', $model);

        // 5. Build approve form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();

        $resultPage->addBreadcrumb(__('Aprobar Documento Fiscal'), __('Aprobar Documento Fiscal'));

        $resultPage->getConfig()->getTitle()->prepend(__('Documentos Fiscales'));

        $resultPage->getConfig()->getTitle()->prepend($model->getCustomerName());

        return $resultPage;
    }
}
