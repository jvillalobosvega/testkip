<?php

namespace Bananacode\TaxDocument\Block\Adminhtml\Review;

/**
 * Class Approve
 * @package Bananacode\TaxDocument\Block\Adminhtml\Review
 */
class Approve extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_mode = 'approve';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize review approve block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'review_id';
        $this->_blockGroup = 'Bananacode_TaxDocument';
        $this->_controller = 'adminhtml_review';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        if ($this->_isAllowedAction('Bananacode_TaxDocument::config')) {
            $this->buttonList->add(
                'pending',
                [
                    'label' => 'Pendiente',
                    'onclick' => 'setLocation(\'' . $this->getApproveUrl(1) . '\')',
                    'class' => 'pendiente'
                ]
            );
        }

        if ($this->_isAllowedAction('Bananacode_TaxDocument::config')) {
            $this->buttonList->add(
                'approve',
                [
                    'label' => 'Aprobar',
                    'onclick' => 'setLocation(\'' . $this->getApproveUrl(2) . '\')',
                    'class' => 'primary action'
                ]
            );
        }

        if ($this->_isAllowedAction('Bananacode_TaxDocument::config')) {
            $this->buttonList->add(
                'reject',
                [
                    'label' => 'Rechazar',
                    'onclick' => 'setLocation(\'' . $this->getApproveUrl(3) . '\')',
                    'class' => 'reject'
                ]
            );
        }

        $this->_headerText = $this->getHeaderText()->getText();
    }

    /**
     * @param $status
     * @return string
     */
    public function getApproveUrl($status)
    {
        return $this->getUrl('*/*/status/approve/' . $status . '/document_id/' . $this->_coreRegistry->registry('taxdocument_review')->getDocumentId());
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('taxdocument_review')->getDocumentId()) {
            return __("Aprobar Documento Fiscal '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('taxdocument_review')->getDocumentId()));
        }
        return __("Aprobar Documento Fiscal");
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
