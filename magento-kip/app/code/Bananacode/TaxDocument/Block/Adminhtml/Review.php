<?php

namespace Bananacode\TaxDocument\Block\Adminhtml;

/**
 * Class Review
 * @package Bananacode\TaxDocument\Block\Adminhtml
 */
class Review extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_review';
        $this->_blockGroup = 'Bananacode_TaxDocument';

        parent::_construct();

        $this->_headerText = __('Documentos Fiscales');
        $this->removeButton('add');
        $this->buttonList->remove('add');
    }
}

