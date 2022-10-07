<?php

namespace Bananacode\TaxDocument\Block\Adminhtml\Review\Approve;

/**
 * Class Form
 * @package Bananacode\TaxDocument\Block\Adminhtml\Review\Approve
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Bananacode\TaxDocument\Model\TaxDocumentType
     */
    protected $_taxDocumentType;
    /**
     * @var \Bananacode\TaxDocument\Model\ResourceModel\TaxDocumentType
     */
    protected $_taxDocumentTypeResource;

    /**
     * @var \Bananacode\TaxDocument\Model\TaxCategory
     */
    protected $_taxDocumentCategory;
    /**
     * @var \Bananacode\TaxDocument\Model\ResourceModel\TaxCategory
     */
    protected $_taxDocumentCategoryResource;

    /**
     * @var \Bananacode\TaxDocument\Model\TaxDocument
     */
    protected $_taxDocument;
    /**
     * @var \Bananacode\TaxDocument\Model\ResourceModel\TaxDocument
     */
    protected $_taxDocumentResource;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Bananacode\TaxDocument\Model\TaxDocumentType $taxDocumentType
     * @param \Bananacode\TaxDocument\Model\ResourceModel\TaxDocumentType $taxDocumentTypeResource
     * @param \Bananacode\TaxDocument\Model\TaxCategory $taxDocumentCategory
     * @param \Bananacode\TaxDocument\Model\ResourceModel\TaxCategory $taxDocumentCategoryResource
     * @param \Bananacode\TaxDocument\Model\TaxDocument $taxDocument
     * @param \Bananacode\TaxDocument\Model\ResourceModel\TaxDocument $taxDocumentResource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,

        \Bananacode\TaxDocument\Model\TaxDocumentType $taxDocumentType,
        \Bananacode\TaxDocument\Model\ResourceModel\TaxDocumentType $taxDocumentTypeResource,

        \Bananacode\TaxDocument\Model\TaxCategory $taxDocumentCategory,
        \Bananacode\TaxDocument\Model\ResourceModel\TaxCategory $taxDocumentCategoryResource,

        \Bananacode\TaxDocument\Model\TaxDocument $taxDocument,
        \Bananacode\TaxDocument\Model\ResourceModel\TaxDocument $taxDocumentResource,

        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;

        $this->_taxDocumentType = $taxDocumentType;
        $this->_taxDocumentTypeResource = $taxDocumentTypeResource;

        $this->_taxDocumentCategory = $taxDocumentCategory;
        $this->_taxDocumentCategoryResource = $taxDocumentCategoryResource;

        $this->_taxDocument = $taxDocument;
        $this->_taxDocumentResource = $taxDocumentResource;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var $model \Bananacode\TaxDocument\Model\TaxDocument */
        $model = $this->_coreRegistry->registry('taxdocument_review');

        $isElementDisabled = true;

        $data = $model->getData();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'approve_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $form->setUseContainer(true);

        $form->setHtmlIdPrefix('document_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Información')]
        );

        if ($model->getId()) {
            $fieldset->addField('document_id', 'hidden', ['name' => 'document_id']);
        }

        $fieldset->addField(
            'customer_id',
            'text',
            [
                'name'     => 'customer_id',
                'label'    => __('ID Cliente Kip'),
                'title'    => __('ID Cliente Kip'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $this->_taxDocumentTypeResource->load($this->_taxDocumentType, $model->getData('document_type'));
        if($this->_taxDocumentType->getName() === 'EIVA') {
            $fieldset->addField(
                'customer_name',
                'text',
                [
                    'name'     => 'customer_name',
                    'label'    => __('Nombre y apellidos'),
                    'title'    => __('Nombre y apellidos'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'id_number',
                'text',
                [
                    'name'     => 'id_number',
                    'label'    => __('Número de Carnet'),
                    'title'    => __('Número de Carnet'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'entity',
                'text',
                [
                    'name'     => 'entity',
                    'label'    => __('Entidad'),
                    'title'    => __('Entidad'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'expiration_date',
                'text',
                [
                    'name'     => 'expiration_date',
                    'label'    => __('Fecha de expiración'),
                    'title'    => __('Fecha de expiración'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
        } else {
            $this->_taxDocumentCategoryResource->load($this->_taxDocumentCategory, $model->getData('category_id'));
            $data['category'] = $this->_taxDocumentCategory->getName();
            $fieldset->addField(
                'customer_name',
                'text',
                [
                    'name'     => 'customer_name',
                    'label'    => __('Nombre del contribuyente'),
                    'title'    => __('Nombre del contribuyente'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'tax_identification_number',
                'text',
                [
                    'name'     => 'tax_identification_number',
                    'label'    => __('NIT'),
                    'title'    => __('NIT'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'registry_number',
                'text',
                [
                    'name'     => 'registry_number',
                    'label'    => __('NCR'),
                    'title'    => __('NCR'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'economic_activity',
                'text',
                [
                    'name'     => 'economic_activity',
                    'label'    => __('GIRO/Actividad Económica'),
                    'title'    => __('GIRO/Actividad Económica'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'head_office_address',
                'text',
                [
                    'name'     => 'head_office_address',
                    'label'    => __('Dirección de casa matriz'),
                    'title'    => __('Dirección de casa matriz'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
            $fieldset->addField(
                'category',
                'text',
                [
                    'name'     => 'category',
                    'label'    => __('Categoría'),
                    'title'    => __('Categoría'),
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );
        }

        $previewHtml = '';
        if(is_array(json_decode($data['document_image']))) {
            foreach (json_decode($data['document_image']) as $image) {
                if (str_contains(strtoupper($image), '.PDF')) {
                    $previewHtml .= '<iframe src="https://docs.google.com/viewer?url='. $image .'&embedded=true" style="width:600px; height:500px;" frameborder="0"></iframe><a href="'.$image.'">Descargar</a>';
                    
                }
                else {
                      $previewHtml .= '<img style="max-height: 400px; margin-top: 30px; display: block" src="' . $image . '" alt="document-img">';
                }
            }
        }

        $fieldset->addField(
            'document_image',
            'text',
            [
                'name'     => 'document_image',
                'label'    => __('Imagen'),
                'title'    => __('Imagen'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => $previewHtml
            ]
        );

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
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
}