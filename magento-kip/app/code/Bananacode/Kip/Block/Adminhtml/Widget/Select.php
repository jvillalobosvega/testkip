<?php

namespace Bananacode\Kip\Block\Adminhtml\Widget;

Class Select extends \Magento\Backend\Block\Template
{
    protected $_elementFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    )
    {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $config = $this->_getData('config');
        $element->setOptions(array_values($config['options']));

        $select = $this->_elementFactory->create("select", ['data' => $element->getData()]);
        $select->setId($element->getId());
        $select->setForm($element->getForm());

        $select->setClass("widget-option select admin__control-select");
        if ($element->getRequired()) {
            $select->addClass('required-entry');
        }

        $element->setData('after_element_html', $select->getElementHtml());
        return $element;
    }


}
