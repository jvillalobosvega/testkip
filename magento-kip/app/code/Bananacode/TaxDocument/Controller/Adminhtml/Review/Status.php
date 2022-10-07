<?php

namespace Bananacode\TaxDocument\Controller\Adminhtml\Review;

use Magento\Store\Model\Store;

/**
 * Class Save
 * @package Bananacode\TaxDocument\Controller\Adminhtml\Review
 */
class Status extends \Magento\Backend\App\Action
{
    const XML_PATH_TRANS_EMAIL_GENERAL_EMAIL = 'trans_email/ident_general/email';

    const XML_PATH_TRANS_EMAIL_GENERAL_NAME = 'trans_email/ident_general/name';

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->_customerRepository = $customerRepository;
        $this->_scopeConfig = $scopeConfig;
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
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {

            /** @var $model \Bananacode\TaxDocument\Model\TaxDocument */
            $model = $this->_objectManager->create('Bananacode\TaxDocument\Model\TaxDocument');

            /** @var $resource \Bananacode\TaxDocument\Model\ResourceModel\TaxDocument */
            $resource = $this->_objectManager->create('Bananacode\TaxDocument\Model\ResourceModel\TaxDocument');

            $id = $this->getRequest()->getParam('document_id');
            if ($id) {
                $resource->load($model, $id);
            } else {
                $this->messageManager->addException(new \Exception(), __('Error, contactar a Pablo.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData('status', intval($data['approve']));

            try {
                $model->save();
                $this->sendEmail($model);
                $this->messageManager->addSuccessMessage(__('Estatus de documento fiscal actualizado.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/approve', ['document_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the tax document.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/approve', ['document_id' => $this->getRequest()->getParam('document_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $taxDocument  \Bananacode\TaxDocument\Model\TaxDocument
     */
    private function sendEmail($taxDocument)
    {
        try {
            $customer = $this->_customerRepository->getById($taxDocument->getCustomerId());
            $email = $customer->getEmail();
            $name = $customer->getFirstname();
            $doc = $taxDocument->getCustomerName();
            $status = intval($taxDocument->getStatus());
            $status_color = '#DDDDDD';
            switch ($status) {
                case 2:
                    $status = 'Aprobado';
                    $status_color = '#48D597';
                    break;
                case 3:
                    $status = 'Rechazado';
                    $status_color = '#EF3340';
                    break;
                default:
                    $status = 'Pendiente';
                    break;
            }

            $transport =
                $this->_transportBuilder
                    ->setTemplateIdentifier('taxdocument_status')
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => Store::DISTRO_STORE_ID
                        ]
                    )
                    ->setTemplateVars(
                        [
                            'subject' => 'Kip: Actualización Documento Fiscal',
                            'name' => $name,
                            'doc' => $doc,
                            'status' => $status,
                            'status_color' => $status_color,
                        ]
                    )
                    ->setFromByScope(
                        [
                            'email' => $this->_scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                Store::DISTRO_STORE_ID
                            ),
                            'name' => $this->_scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                Store::DISTRO_STORE_ID
                            ),
                        ],
                        1
                    )
                    ->addTo(
                        $email,
                        $name
                    )
                    ->getTransport();

            $transport->sendMessage();
        } catch (\Exception $ex) {

        }
    }
}
