<?php

namespace Bananacode\Kip\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Button
 * @package Bananacode\Kip\Block\Adminhtml\System\Config
 */
class Button extends Field
{
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/tree.phtml');
        }
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel  = $originalData['button_label'];
        $buttonUrl    = $originalData['button_url'];
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'button_url'   => $this->getUrl($buttonUrl),
                'html_id'      => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }
}
